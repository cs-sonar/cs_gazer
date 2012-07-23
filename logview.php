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

$FORM = form_xss();

/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
// ログの一覧を取得
try{
	$csv = new Csv(CHECK_LOG_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}

$csvlist = $csv->getCsv();
krsort($csvlist);

// 最後のチェックのデータを取得
try{
	$checkexec = file(CHECK_LOG_EXEC_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}

$checkexec = explode(',',$checkexec[0]);
$data['date'] = $checkexec[0];
$data['errornum'] = $checkexec[1];
$data['exectime'] = $checkexec[2];


// チェック過去ログの一覧
$dat_path = HOME_PATH . "dat/";
$logs_command = 'ls -1 ' . $dat_path . ' | grep check_log.dat_';

// ログファイルのファイル名を取得
exec( $logs_command , $old_logs );

if($FORM["current_logfile"]){
	if($FORM["current_logfile"] !== CHECK_LOG_DAT_FILE ) {
		error("ログファイルの指定にふさわしくない文字が存在します。");
	}
	if($FORM['Submit']){
		download_file(CHECK_LOG_DAT_FILE);
	}
	if($FORM['Submit_del']){
		if(unlink(CHECK_LOG_DAT_FILE)){
			clean_dat_file(CHECK_LOG_DAT_FILE, $header);
			header("Location:./logview.php");
			exit;
		}else{
			$errors[] = "ログファイルのクリアに失敗しました。";
		}
	}
}

if($FORM["logfile"]){
	if(!preg_match("/^[a-zA-Z0-9_.]+$/", $FORM["logfile"])){
		error("ログファイルの指定にふさわしくない文字が存在します。");
	}
	if($FORM['Submit']){
		download_file($dat_path . $FORM["logfile"]);
	}
	if($FORM['Submit_del']){
		if(unlink($dat_path . $FORM["logfile"])){
			header("Location:./logview.php");
			exit;
		}else{
			$errors[] = "削除に失敗しました。";
		}
	}
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['old_logs'] = $old_logs;
$data['list'] = $csvlist;
$data['log_current_path'] = CHECK_LOG_DAT_FILE;
printhtml($data);

