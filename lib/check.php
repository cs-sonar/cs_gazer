<?php

/*------------------------------------------------------
 * 初期設定
 -------------------------------------------------------*/
date_default_timezone_set('Asia/Tokyo');
error_reporting(E_ALL ^ E_NOTICE);
require_once(dirname(__FILE__) . '/../include/Csv.php');
require_once(dirname(__FILE__) . '/../include/config.inc.php');
$time_start = microtime(true);
if(!$line_max){$line_max = 5000;}
if(!$timeout){$timeout = 3;}
if(!$ping_cmd){$ping_cmd = "ping";}
$error_count = 0; // エラーサーバーの数の記録用(check_log_execの表示用)
$current_check_str = "";

/*------------------------------------------------------
 * チェック準備
 -------------------------------------------------------*/
//curl関数が存在するかチェック
if( !function_exists(curl_init) ){ $not_curl = 1; }

//check_logの読み込み
$file=CHECK_LOG_DAT_FILE;
$file_dat=fopen($file,"a+");
flock($file_dat, LOCK_EX); //チェック前にログのロック開始

//現在のログファイルから最大IDを取得する
//最大IDの取得はファイルロックをしていないと複数回チェックを実行した時に問題となる
if(file_exists($file)){
	$csvcheck = new Csv($file);
	$check_log = $csvcheck->getCsv();
	if(isset($check_log)){
		foreach($check_log as $v){
			$check_id[] = $v['id'];
		}
	$max_id = max($check_id);
	}
	if(isset($max_id)){
		$next_id = $max_id + 1;
	}else{
		$max_id = 1;
		$next_id = 2;
	}
	if($max_id > $line_max) {
		flock($file_dat, LOCK_UN); // ログローテートの実行前にファイルロックを解除
		rename( $file, $file . "_" . date("YmdHis") );
		$next_id = 1;
		$line_max_flg = 1;
	}
}else{
	$next_id = 1;
}

// 保存ログの件数が多い場合、一番古いものを削除する
$dat_path = HOME_PATH . "dat/";
$logs_command = 'ls -1 ' . $dat_path . ' | grep check_log.dat_';
exec( $logs_command , $old_logs );
if(count($old_logs) > $oldlog_max) {
	sort($old_logs);
	unlink($dat_path . $old_logs[0]);
}

// ログローテートが必要な場合はcheck_logを再度読み込み
if($line_max_flg){
	//check_logの再読み込み
	$file=CHECK_LOG_DAT_FILE;
	$file_dat=fopen($file,"a+");
	flock($file_dat, LOCK_EX); //チェック前にログのロック開始
}

/*------------------------------------------------------
 * チェック開始
 -------------------------------------------------------*/

//チェック対象の一覧を取得
$csv = new Csv(LIST_DAT_FILE);
$checklist = $csv->getCsv();

if($checklist){
foreach( $checklist as $v ){
	//値の初期化
	$errors = array();
	$check_str = "";
	$str = "";
	$hit = "";
	$check = array();
	$portlist = array();
	$mail_to_arr = array();

	$check_str['date'] = date("Y-m-d H:i:s");
	$check_str['host'] = $v['host'];
	if ($v['ports']) {
	$portlist = explode(":", $v['ports']);
	}

	if($v['check'] === "on"){
		if(ipcheck($v['host'])){
			$ip = $v['host'];
		}else{
			$ip = getHostByName($v['host']);
			if(!ipcheck($ip)) {
				$errors[] = "[ドメイン逆引きエラー]" . $v['host'] ."\n";
				$check_str['check'] = "resolverror";
			}
		}

		if(!$errors) {
			//pingチェック
			if ( $v['pingchk'] === "enable" ) {
				if (!$ping_str = pingcheck($ip,$ping_cmd)) {
					$errors[] = "[PINGエラー]\n　".$v['host']."\n";
					$check_str['pingchk'] = "ng";
				}else{
					$rtt = explode("\n", $ping_str);
					foreach($rtt as $var){
						if ( strpos( $var, "rtt" ) !== false || strpos( $var, "round-trip" ) !== false ) $hit = $var;
						$tmp = explode('/',$hit);
					}
					$check_str['pingchk'] = $tmp[4]."ms";
				}
			}

			//webアクセスチェック
			if ( $v['httpchk'] === "enable" ) {
				if(!$not_curl){
					if (!$http_str = httpcheck('http://'.$v['host'],$timeout)) {
						$errors[] .= "[HTMLエラー]\n　".$v['host']."\n";
						$check_str['httpchk'] = "ng";
					}else{
						$check_str['httpchk'] = $http_str;
					}
				}else{ //Curlが使えない場合は単純なポートチェックを行う
					$socket = @fsockopen($ip, 80, $errno, $errstr, $timeout);
					if ($socket === FALSE) {
						$errors[] .= "[HTMLエラー]\n　".$v['host']."\n";
						$check_str['httpchk'] = "ng";
					}else{
						$check_str['httpchk'] = ok;
					}
				}
			}

			//任意のポートチェック
			if( $portlist ) {
				foreach( $portlist as $port ) {
					$socket = @fsockopen($ip, $port, $errno, $errstr, $timeout);
					if ($socket === FALSE) {
						$errors[] = "[ポート接続エラー:".$port."]\n　". $errstr . "\n";
						$ports_str[] = "PORT".$port." is ".$errstr;
					}else{
						$ports_str[] = "PORT".$port."ok";
					}
				}
				$check_str['ports'] = implode(':',$ports_str);
				$ports_str = '';
			}
		}


		//エラーが確認できた場合
		if($errors){
			if(!$check_str['check']) {$check_str['check'] = 'checkerror';}
			$error_count++;
			$mail_title = '[cs_gazer]'.$v['host'];
			$mail_from = $from_mailaddr ? $from_mailaddr : 'from@example.com';
			$mail_to = array();

			$mail_body = "";
			$mail_body .= "【".$v['host'] . "】\n" . $v['summary'] ."\n\n";
			$mail_body .= "サーバーエラーを検知しました\n\n";
			foreach($errors as $vvv) {
				$mail_body .= $vvv."\n\n";
			}
			$mail_body .= "host url http://" . $v['host'] . "\n";
			$mail_body .= "\n";
			$mail_body .= "checked by " . $cs_gazer_url . "\n";

			// 送信先メールアドレス取得処理
			$mail = new Csv(MAIL_DAT_FILE);
			$group = new Csv(MAILGROUP_DAT_FILE);
			$mail_id_list = $group->getCsvLine($v['mailgroup']);
			$mailGroupAsMailaddr = explode(':',$mail_id_list['mailid']); // メールアドレスidを配列にする
			foreach($mailGroupAsMailaddr as $vv) {
				$tmp = $mail->getCsvLine($vv); // メールアドレス一覧datからidの1行を取得
				$mail_to_arr[] = $tmp['mailaddr']; // 送信先のメールアドレスを配列に格納
			}

			if(isset($mail_to_arr)){
				foreach($mail_to_arr as $mail_to){
					mb_language("ja");
					mb_internal_encoding("UTF-8");
					//メール送信
					$mail_header = "From: $mail_from";
					$mail_body = wordwrap($mail_body, 70, "\n");
					mb_send_mail($mail_to, $mail_title, $mail_body, $mail_header);
				}
			}
		}else{
			$check_str['check'] = 'ok';
		}
	}else{
		$check_str['check'] = "notcheck";
	}
	// header :  date,host,check,ports,pingchk,httpchk
	$str .= "\n" . $next_id .",".
		$check_str['host'] .",".
		$check_str['date'] .",".
		$check_str['check'] .",".
		$check_str['ports'] .",".
		$check_str['pingchk'] .",".
		$check_str['httpchk'];

	$current_check_str .= $str;
	$next_id++;
}
}

/*------------------------------------------------------
 * チェックログに書き込みを行う
 -------------------------------------------------------*/

//チェックログの書き込み終了
//前半でロックしていたcheck_logの書き込み
if($line_max_flg){
fputs($file_dat, $header . $current_check_str);
flock($file_dat, LOCK_UN);
chmod(CHECK_LOG_DAT_FILE,0666);
}else{
fputs($file_dat, $current_check_str);
flock($file_dat, LOCK_UN);
}

//currentチェックログの書き込み開始
$current_check_str = $header . $current_check_str;
write_dat_file(CHECK_CURRENT_DAT_FILE,$current_check_str);

// チェックのステータスを記録
$time_end = microtime(true);
$time = $time_end - $time_start;
$time = substr( $time, 0, 4 );
$time_logs = date("Y-m-d H:i:s") . ",errorserver:" .$error_count . ",exectime:" .$time ."\n";
write_dat_file(trim(CHECK_LOG_EXEC_DAT_FILE),$time_logs);

/*------------------------------------------------------
 * チェックの終了
 -------------------------------------------------------*/

//webからのリンクでのチェックの場合はindex.phpへリダイレクトする
if($_GET['web']){
header('Location:../index.php');
}
//CLIでの実行ではdoneを表示
//var_dump($current_check_str);
echo "done\n";
exit;

/*------------------------------------------------------
 * 関数集
 -------------------------------------------------------*/

/**
 * pingcheck.
 * pingのチェックを行う
 * @param $ip チェック対象のIPアドレス
 * @return bool|string 成功時にはコマンドを返す。失敗時にはfalse
 */
function pingcheck($ip, $ping_cmd) {
	$ping_command_str = $ping_cmd . " -c 2 " . $ip;
	$output = `$ping_command_str`;
	if (strstr($output, '100% packet loss') || strstr($output, '100.0% packet loss') || strstr($output, '0 packets received') || strstr($output, '0 received') || !$output ) {
		return false;
	} else {
		return $output;
	}
}

/**
 * httpcheck.
 * httpのステータスチェックを行う
 * @param $url
 * @param $timeout
 * @return bool|string 成功時にはステータスコードを返す。失敗時にはfalse
 */
function httpcheck($url,$timeout) {
	$statuscode = get_http_statuscode($url,$timeout);
	if($statuscode){
		return $statuscode;
	}else{
		return false;
	}
}

/**
 * get_http_statuscode.
 * HTTPのステータスコードを取得する
 * @param $url チェックするURL
 * @param $timeout タイムアウトまでの時間
 * @return mixed httpコードを返す
 */
function get_http_statuscode($url , $timeout) {
	$agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)";

	$ch = curl_init();
	$options = array(
		CURLOPT_URL            => $url,
		CURLOPT_HEADER         => TRUE,
		CURLOPT_NOBODY         => TRUE,
		CURLOPT_RETURNTRANSFER => TRUE,
		CURLOPT_TIMEOUT        => $timeout,
		CURLOPT_USERAGENT      => $agent
	);
	curl_setopt_array($ch, $options);
	$header_data = curl_exec($ch);
	curl_close($ch);

	preg_match("/^HTTP\/[01\.]+ ([0-9]{3}[A-Za-z\(\)\- ]+)/",$header_data,$getcode);
	return $getcode[1];
}

/**
 * ipcheck.
 * IPアドレスであるかのチェックを行う.
 * @param $sStr
 * @return bool|int
 */
function ipcheck($sStr) {
	$bRes = preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/',$sStr, $aReg);
	if($bRes) {
	array_shift($aReg);
		foreach($aReg As $iWk) {
			if(($iWk > 255) || ($iWk < 0)){
				$bRes = false;
				break;
			}
		}
	}
	return $bRes;
}

/**
 * write_dat_dile
 * ファイルに任意のデータを書き込む.
 * @param $file 書き込み対象のファイル名
 * @param $header 書き込むデータ
 */
function write_dat_file($file,$str) {
	$file_dat=fopen($file,"w+");
	flock($file_dat, LOCK_EX);
	fputs($file_dat, $str);
	flock($file_dat, LOCK_UN);
	$file_dat='';
}


