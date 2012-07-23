<?php
/**
 * flot.php
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

/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
if(isset($_GET['host'])){
	// ログの一覧を取得
	try{
		$csv = new Csv(CHECK_LOG_DAT_FILE);
	} catch (Exception $e){
		error($e->getMessage());
	}
	$csvlist = $csv->getCsv();
	foreach($csvlist as $v) {
		if($_GET['host'] === $v['host'] && isset($v['pingchk']) && $v['pingchk'] != "ng"){
			$timecount = str_replace('ms','',$v['pingchk']);
			$pingtime[] = $timecount;
		}
	}
	$data['pingtime'] = $pingtime;
}
/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/

printhtml($data);

