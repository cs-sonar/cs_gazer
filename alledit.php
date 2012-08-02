<?php
/**
 * 2012/01/22 23:48:17
 * @author sonar
 * alledit.php
 */

/*------------------------------------------------------------
 環境設定
 ------------------------------------------------------------*/
require_once './include/init.inc';
if(!$session->isAuthenticated()){
	header('Location:./index.php');
	exit();
}
$data['user'] = $session->get('user',''); // ログインしていた場合にユーザー名をsmartyに渡す
$FORM = form_xss();
/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
// 使用するクラスを読み込む
try{
	$csv = new Csv(LIST_DAT_FILE);
	$group = new Csv(MAILGROUP_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}

// 監視できるポートリストの取得
$portList = explode(',', file_get_contents(PORTS_DAT_FILE));

// 最後のチェックのデータを取得
$checkexec = file(CHECK_LOG_EXEC_DAT_FILE);
$checkexec = explode(',',$checkexec[0]);
$data['date'] = $checkexec[0];
$data['errornum'] = $checkexec[1];
$data['exectime'] = $checkexec[2];

// フォーム送信()
if(isset($FORM['add'])){
	// 入力されたポートを配列にしてから:区切りの文字列を作る
	foreach($portList as $v) {
		if($FORM["ports$v"] === $v) {
			$ports[] = $v;
			$hold[$v] = $v;
		}
	}
	$FORM['ports'] = implode($ports,':');
	$FORM['ports'] = trim($FORM['ports']);

	// httpchkやpingchkの値がenable以外であればdisableをセットする
	if ($FORM['httpchk'] !== 'enable') {
		$FORM['httpchk'] = 'disable';
	}
	if ($FORM['pingchk'] !== 'enable') {
		$FORM['pingchk'] = 'disable';
	}
	if ($FORM['check'] !== 'on') {
		$FORM['check'] = 'off';
	}

	//checklist.datに挿入する配列を作成
	$csv = new Csv(LIST_DAT_FILE);
	$header = $csv->getCsvHeader();

	foreach($header as $v){
		$hold[$v] = $FORM[$v];
		$arr[] = $FORM[$v];
		if(preg_match('/,/', $FORM[$v])){
			$errors['str'] = "追加:値が不正です";
		}
		if( !$FORM[$v] && $v !== "ports" ) {
			$errors[$v] = "追加：" . $v . "の入力がありません";
		}
	}

	//エラーチェック
	if(!preg_match('/^([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $FORM['host'])){
		$errors['host'] = "追加：サーバー名が不正です";
	}
	if(!$csv->checkUnique($FORM['host'])) {
		$errors['host'] = '追加：すでに登録されているホストです。';
	}

	// エラーの場合はholdに値を入れて再表示
	if( $errors ) {
		$data['errors'] = $errors;
		$data['hold'] = $hold;
	}else{
		//挿入
		$csv->addCsv($arr);
	}
}

// 削除が押された時
if($FORM['del']){
	if(!$_POST['delcheck']){$errors[] = "削除：削除対象が選択されていません。";}
	foreach( $_POST['delcheck'] as $v ){
		$csv->delCsv($v);
	}
}

// 編集が押された時
if($FORM['edit']){
	$checklist = $csv->getCsv();
	$num = count($checklist);
	if($num){
		for($i=1;$i<=$num;$i++){
			$port = implode(':', $_POST["$i-port"]);
			if($FORM["$i-check"] != "on"){$FORM["$i-check"] = 'off';}
			if($FORM["$i-pingchk"] != "enable"){$FORM["$i-pingchk"] = 'disable';}
			if($FORM["$i-httpchk"] != "enable"){$FORM["$i-httpchk"] = 'disable';}
			if(!$FORM["$i-host"]){$errors[] = "編集：ホスト名が入力されていません。";}
			$arr = array(
				$FORM["$i-host"],
				$FORM["$i-check"],
				$FORM["$i-summary"],
				$FORM["$i-mailgroup"],
				$port,
				$FORM["$i-pingchk"],
				$FORM["$i-httpchk"]);
			$str[] = implode(',',$arr);
			$col_host[] = $FORM["$i-host"];
		}
	}else{
		$errors[] = "更新エラーが発生しました";
	}
	
	// ホスト名の重複エラーチェック
	$unique_host = array_unique($col_host);
	if( count($unique_host) != count($col_host) ) {
		$errors[] = "編集：ホスト名が重複します。";
	}
	$header = $csv->getCsvHeader();
	$header = implode(',',$header);
	array_unshift($str, $header);
	
	//書き込み処理開始
	if(!$errors){
		$line=join("\n",$str);
		$fh=fopen(LIST_DAT_FILE,"w");
		fwrite($fh,$line);
		fclose($fh);
	}
}



// メールグループリストの取得
$groupList = $group->getCsvColumn('mailgroup');

// チェック中のリストの取得
$csvlist = $csv->getCsv();
//check対象かどうかでソートする
foreach($csvlist as $k => $v){
	$sortlist[$k] = $v['check'];	// checkカラムでソート
}
if($sort == "asc"){
	array_multisort($sortlist, SORT_ASC, SORT_STRING, $csvlist);
} else {
	array_multisort($sortlist, SORT_DESC, SORT_STRING, $csvlist);
}

// 監視中のportを配列にする
foreach($csvlist as $v) {
	if ($v['ports']){
		$v['ports'] = explode(':',$v['ports']);
	}
	$portarray[] = $v;
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['groupList'] = $groupList;
$data['portList'] = $portList;
$data["list"] = $portarray;
$data['checkexec'] = $checkexec[0];
$data['errors'] = $errors;

printhtml($data);
