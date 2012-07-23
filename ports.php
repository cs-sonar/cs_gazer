<?php
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
// 監視できるポートリストの取得
$portList = explode(',', file_get_contents(PORTS_DAT_FILE));

// 監視中のポートがあればエラーとする
try{
	$csv = new Csv(LIST_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}
$checking_ports = $csv->getCsvColumn('ports');
$checking_ports = array_filter($checking_ports, 'strlen');
foreach($checking_ports as $k => $v) {
	$tmp = explode(':', $v);
	foreach($tmp as $vv) {
			$is_checked[$vv] = $vv;
	}
}
$is_checked = array_unique($is_checked);
$data[is_checked] = $is_checked;
	
// 追加時
if(isset($FORM['add'])){
	$addPorts = explode(',', $FORM['ports']);
	foreach ($addPorts as $k => $port) {
		$port = trim($port);
		$port = ltrim($port, '0');
		if(!(ctype_digit((string)$port) && 0 <= $port && $port <= 65535)){
			$errors[] = 'ポート番号として解釈できない記述があります';
			break;
		}
		$addPorts[$k] = $port;
	}
	if(!isset($errors)){
		$newPorts = array_merge($portList, $addPorts); // 追加ポートと既存ポートをマージ
		$newPorts = array_unique($newPorts, SORT_NUMERIC); // 重複ポートの削除
		sort($newPorts, SORT_NUMERIC); // ポート番号順にソート
		file_put_contents(PORTS_DAT_FILE, implode(',', $newPorts)); // ファイルへ書き出し
		$portList = $newPorts;
	}
}

// 削除時
if(isset($FORM['del'])){
	foreach($portList as $v) {
		if($FORM["ports$v"]){
			$delPorts[] = $FORM["ports$v"]; // 削除ポートを配列で取得
		}
	}
	
	// エラーチェック
	if(!$delPorts){
		$errors[] = "削除するポートを選択して下さい。";
	}
	
	//監視中のポートがあった場合はエラーにする。
	foreach($checking_ports as $k => $v) {
		$tmp = explode(':', $v);
		foreach($tmp as $vv) {
			if(in_array($vv, $delPorts)) {
				$errors[] = "ポート".$vv."は".$k."にて監視中のポートです。";
			}
		}
	}
	
	if(!isset($errors)){ // エラーがなければ削除実行
		$newPorts = array_diff($portList, $delPorts); // 差分のみを取得(既存ポートから削除ポートを除いたもの)
		file_put_contents(PORTS_DAT_FILE, implode(',', $newPorts));
		$portList = $newPorts;
	}
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data["ports"] = $portList;
$data['errors'] = $errors;
$data['currentpage'] = "ports";

printhtml($data);
