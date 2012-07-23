<?php
/**
 * index.php
 * チェックリストの一覧とステータス表示
 */

/*------------------------------------------------------------
 環境設定
 ------------------------------------------------------------*/
require_once './include/init.inc';
// 認証されていなければリダイレクト
if(!$session->isAuthenticated()) {
	header("Location:./login.php");
	exit();
}
$data['user'] = $session->get('user',''); // ログインしていた場合にユーザー名をsmartyに渡す

/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
// チェックリストの一覧を取得
try{
	$csv = new Csv(LIST_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}

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

// チェックログの一覧を取得
try{
	$check = new Csv(CHECK_CURRENT_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}
$checklog = $check->getCsv();

// 最後のチェックのデータを取得
$checkexec = file(CHECK_LOG_EXEC_DAT_FILE);
$checkexec = explode(',',$checkexec[0]);
$data['date'] = $checkexec[0];
$data['errornum'] = $checkexec[1];
$data['exectime'] = $checkexec[2];

// チェックリストとログを一つの配列にする
foreach($csvlist as $v) {
	if ($v['ports']){
		$v['ports'] = explode(':',$v['ports']);
	}
	foreach($checklog as $vv){
		if($vv['host'] === $v['host']){
			$v['result_date'] = $vv['date'];
			$v['result_check'] = $vv['check'];
			$v['result_ports'] = explode(':',$vv['ports']);
			$v['result_httpchk'] = $vv['httpchk'];
			$v['result_pingchk'] = $vv['pingchk'];
			$v['ipaddr'] = gethostbyname($v['host']);
		}
		$vv = '';
	}
	$arr[] = $v;
	$v = '';
}
/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['list'] = $arr;
$data['checkexec'] = $checkexec[0];
printhtml($data);

