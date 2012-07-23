<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING );

/**
 * printhtml
 * smartyを利用したview.
 * @param array $data smartyへ引き渡すデータ
 * @param string $templatefile tmplファイル名
 * @param string $tmpldir smartyのcompiledir等の元ファイル
 * @param null $encode エンコード
 */
function printhtml($data=NULL, $templatefile = NULL, $tmpldir="./", $encode=NULL) {
	require_once(SMARTY_DIR.'Smarty.class.php');
	$smarty = new Smarty();
	if(SMARTY_DEBUG === 'true'){
		$smarty->debugging = true;
	}

	$smarty->template_dir = $tmpldir."templates/";
	$smarty->compile_dir  = $tmpldir."templates_c/";
	$smarty->config_dir   = $tmpldir."configs/";
	$smarty->cache_dir    = $tmpldir."cache/";
	$smarty->caching      = FALSE;

	if (!$templatefile) {
		$tmplname =  basename($_SERVER['SCRIPT_NAME'],".php").".tmpl";
		$tdir = opendir($smarty->template_dir) or error_s("ディレクトリのオープンに失敗しました。");

		while ($fname = readdir($tdir)) {
			if (strcmp($fname, $tmplname) == 0) {
				$templatefile = $tmplname;
			}
		}
	}
	if (!$templatefile) {
		error_s("テンプレートファイルが存在しません。file not found " . $smarty->template_dir .  $tmplname);
	}

	if ($encode) {
		mb_convert_encoding($data, $encode, 'UTF-8');
	}

	if (is_array($data)) {
		foreach($data as $key => $val) {
			$smarty->assign($key, $val);
		}
	}
	if ($encode) {
		if (strcmp($encode, "SJIS") == 0) {
			header("Content-type: text/html; charset=Shift_JIS");
		}
		$output = $smarty->fetch($templatefile);
		$out = mb_convert_encoding($output, $encode, 'UTF-8');
		echo $out;
	} else {
		header("Content-type: text/html; charset=UTF-8");
		$smarty->display($templatefile);
	}

}

/**
 * clean_dat_file datファイルの再作成を行う
 * @param string $file dat_file_path
 * @param string $header dat_file_header_string
 */

function clean_dat_file($file,$header) {
	$file_dat=fopen($file,"w+");
	flock($file_dat, LOCK_EX);
	fputs($file_dat, $header);
	flock($file_dat, LOCK_UN);
	chmod($file,0666);
	$file_dat='';
}

/**
 * create_token.
 * @return string
 */
function create_token() {
	$token=md5(uniqid(mt_rand(), true));
	return $token;
}

/**
 * form.
 * @param null $after_enc
 * @param string $before_enc
 * @return array
 */
function form($after_enc = NULL, $before_enc = '') {
	$FORM = array();
	foreach ($_POST as $key => $value) {
		if ($after_enc) { $value = mb_convert_encoding($value, $after_enc, $before_enc); }
		$FORM[$key] = $value;
	}
	return $FORM;
}

/**
 * form_xss
 * @param null $after_enc
 * @param string $before_enc
 * @return array
 */
function form_xss($after_enc = NULL, $before_enc = '') {
	$FORM = array();
	foreach ($_POST as $key => $value) {
		if ($after_enc) { $value = mb_convert_encoding($value, $after_enc, $before_enc); }
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		$value = str_replace("\\", "", $value); // バックスラッシュを完全削除
		$FORM[$key] = $value;
	}
	foreach ($_GET as $key => $value) {
		if ($after_enc) { $value = mb_convert_encoding($value, $after_enc, $before_enc); }
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		$value = str_replace("\\", "", $value); // バックスラッシュを完全削除
		$FORM[$key] = $value;
	}
	return $FORM;
}

/**
 * download_file
 * 与えられた引数のファイルをダウンロードするダイアログを出す
 * @param $path_file ファイルパス
 */
function download_file($path_file)
{
    /* ファイルの存在確認 */
    if (!file_exists($path_file)) {
        error("ファイルが存在しません。");
    }

    /* オープンできるか確認 */
    if (!($fp = fopen($path_file, "r"))) {
        error("指定のファイルを開く事ができません");
    }
    fclose($fp);

    /* ファイルサイズの確認 */
    if (($content_length = filesize($path_file)) == 0) {
        error("指定のファイルサイズがゼロです。");
    }

    /* ダウンロード用のHTTPヘッダ送信 */
    header("Content-Disposition: inline; filename=\"".basename($path_file)."\"");
    header("Content-Length: ".$content_length);
    header("Content-Type: application/octet-stream");

    /* ファイルを読んで出力 */
    if (!readfile($path_file)) {
        error("指定のファイルを読み出す事ができません。");
    }
}

/**
 * randomstring
 * 与えられた引数桁のランダムな文字列を返す
 * @param int $digit 得たい桁数
 * @return string
 */
function randomstring($digit = 0){

	if($digit == 0 || !is_numeric($digit)){
		echo "randomstring.php 引数エラー";
		die();
	}

	//	変数
	$char_set	= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
	$result		= "";

	//	文字列生成
	mt_srand();
	for($i = 0; $i < $digit; $i++){
		$result .= $char_set[mt_rand(0, strlen($char_set) - 1)];
	}

	return $result;
}

/**
 * f_AddressChk
 * @param $pAddress E-Mailアドレス
 * @return bool
 */
//簡易なもの
function f_AddressChk($pAddress){
	//アドレスチェック
	if (!preg_match('/^[^@]+@[^.]+\..+/',$pAddress)){
		return false;
	}
	return true;
}

/**
 * error
 * smartyを利用したエラーの表示
 * @param $str
 */
function error($str) {
	$data["str"] = $str;
	$data["home_url"] = HOME_URL;
	printhtml($data , $templatefile = "error.tmpl", $tmpldir=NULL, $encode=NULL);
	exit;
}

/**
 * error_plane
 * smartyを利用しないエラー表示
 * @param $str
 */
function error_plane($str) {
	header("Content-type: text/html; charset=UTF-8");
	print "<html>
<head><title>エラーが発生しました</title></head>
<body>
■システムでエラーが発生しました。
	<br><br>
	<font color=red>";
	echo "<pre>";
	echo $str;
	echo "</pre>";
	print"</font>
	
</body>
</html>
	";
	exit;
}


