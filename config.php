<?php
/**
 * config.php
 * 設定
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

$data['user'] = $session->get('user'); // ログインしていた場合にユーザー名をsmartyに渡す
$FORM = form_xss();
/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/

$cs_gazer_url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
$cs_gazer_url = str_replace("config.php", "", $cs_gazer_url);

if($FORM['clean_log_dat']){
	unlink(CHECK_CURRENT_DAT_FILE);
	unlink(CHECK_LOG_DAT_FILE);
	unlink(CHECK_LOG_EXEC_DAT_FILE);
	touch(CHECK_LOG_EXEC_DAT_FILE);
	chmod(CHECK_LOG_EXEC_DAT_FILE,0666);
	clean_dat_file(CHECK_LOG_DAT_FILE, $header);
	clean_dat_file(CHECK_CURRENT_DAT_FILE, $header);
}

if($FORM['clean_all_dat']){
	unlink(CHECK_CURRENT_DAT_FILE);
	unlink(CHECK_LOG_DAT_FILE);
	unlink(CHECK_LOG_EXEC_DAT_FILE);
	touch(CHECK_LOG_EXEC_DAT_FILE);
	chmod(CHECK_LOG_EXEC_DAT_FILE,0666);
	clean_dat_file(CHECK_LOG_DAT_FILE, $header);
	clean_dat_file(CHECK_CURRENT_DAT_FILE, $header);
	unlink(LIST_DAT_FILE);
	clean_dat_file(LIST_DAT_FILE, $checklist_header);
	unlink(PORTS_DAT_FILE);
	clean_dat_file(PORTS_DAT_FILE, '25,110');
	unlink(MAILGROUP_DAT_FILE);
	clean_dat_file(MAILGROUP_DAT_FILE, $mailgroup_header . "\nlist001,1");
	unlink(MAIL_DAT_FILE);
	clean_dat_file(MAIL_DAT_FILE, $mail_header . "\n1,to@example.com");
	unlink(SETTING_DAT_FILE);
	clean_dat_file(SETTING_DAT_FILE, "7200,3,5000,5,from@example.com," . $cs_gazer_url);
}

if($FORM['clean_list_dat']){
	unlink(LIST_DAT_FILE);
	clean_dat_file(LIST_DAT_FILE, $checklist_header);
}

if($FORM['clean_ports_dat']){
	unlink(PORTS_DAT_FILE);
	clean_dat_file(PORTS_DAT_FILE, '25,110');
}

if($FORM['clean_mailgroup_dat']){
	unlink(MAILGROUP_DAT_FILE);
	clean_dat_file(MAILGROUP_DAT_FILE, $mailgroup_header . "\nlist001,1");
}

if($FORM['clean_mail_dat']){
	unlink(MAIL_DAT_FILE);
	clean_dat_file(MAIL_DAT_FILE, $mail_header . "\n1,to@example.com");
}

if($FORM['clean_setting_dat']){
	unlink(SETTING_DAT_FILE);
	$cs_gazer_url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
	$cs_gazer_url = str_replace("config.php", "", $cs_gazer_url);
	clean_dat_file(SETTING_DAT_FILE, "7200,3,5000,5,from@example.com," . $cs_gazer_url);
}

// 設定ファイルの編集
if($FORM['submit_setting']){
	if(!preg_match('/^[1-9][0-9][0-9][0-9]+$/', $FORM['session_timeout'])){
		$errors['session_timeout'] = "セッション時間は1000以上の正の整数で入力して下さい。";
	}
	if( 9999 < $FORM['session_timeout'] ){
		$errors['session_timeout'] = "セッション時間は10000秒未満で入力して下さい。";
	}
	if(!preg_match('/^[1-9]$/', $FORM['timeout'])){
		$errors['timeout'] = "チェックタイムアウト時間は1〜9秒の正の整数で入力して下さい。";
	}
	if(!preg_match('/^[1-9][0-9][0-9][0-9]$/', $FORM['line_max'])){
		$errors['line_max'] = "ログの最大行数は1000以上の正の整数で入力して下さい。";
	}
	if( 10000 <= $FORM['line_max'] ){
		$errors['line_max'] = "ログの最大行数は10000行未満で入力して下さい。";
	}
	if(!preg_match('/^[0-9]+$/', $FORM['oldlog_max'])){
		$errors['oldlog_max'] = "ログ保存数は1〜999の正の整数で入力して下さい。";
	}
	if(substr($FORM['oldlog_max'], 0, 1) === '0' && strlen($FORM['oldlog_max']) >= '2' ){
		$errors['oldlog_max'] = "ログ保存数の設定がゼロから始まった数字になっています。";
	}
	if( 999 < $FORM['oldlog_max'] ){
		$errors['oldlog_max'] = "ログ保存数は1〜999の正の整数で入力して下さい。";
	}
    if(!f_AddressChk($FORM['frommailaddr'])) {$errors[] = "設定しようとしているメールアドレスが不正です。";}
    if(200 < mb_strlen($FORM['frommailaddr'])){$errors[]="メールアドレスの文字数オーバーです。200文字以内で入力して下さい。";}
    if (!preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$%#]+)$/', $FORM['cs_gazer_url'])){$errors[]="URLが不正です";}
	if(!$errors){
		$str = $FORM['session_timeout'] . "," . $FORM['timeout'] . "," . $FORM['line_max'] . "," . $FORM['oldlog_max'] . "," . $FORM['frommailaddr'] . "," . $FORM['cs_gazer_url'];
		clean_dat_file(SETTING_DAT_FILE, $str);
	}
}


/*------------------------------------------------------------
チェック
------------------------------------------------------------*/
//php関数のチェック
if( function_exists(curl_init) ){
	$data['curl'] = 'ok';
}else{
	$data['curl'] = 'ng';
}

if( function_exists(mb_send_mail) ){
	$data['mail'] = 'ok';
}else{
	$data['mail'] = 'ng';
}

// dat dirの書き込み権限チェック
if(is_writable(HOME_PATH . '/dat')){
	$data['dat'] = 'ok';
}else{
	$data['dat'] = 'ng';
}

/*------------------------------------------------------------
 * テンプレートファイルの書き込み権限チェック
 */
if(is_writable(HOME_PATH . '/templates_c')){
	$data['smartycache'] = 'ok';
}else{
	$data['smartycache'] = 'ng';
}


/*------------------------------------------------------------
 * auth_dat_file
 */
$data['auth_dat_file'] = 'ok';
try{
	$check = new Csv(AUTH_DAT_FILE);
} catch (Exception $e){
	header('Location:'. $cs_gazer_url .'/installer.php');
	exit;
}

/*------------------------------------------------------------
 * check dat files
 */
foreach($dat_files as $k => $v){
	$data[$k] = 'ok';
	if($k === "setting_dat_file"){
		if(!is_writable($v)){
			$data[$k] = 'ng';
			$errors[$k] = $k . 'に書き込み権限がありません。';
		}
	}else{
		try{
			$check = new Csv($v);
		} catch (Exception $e){
			$errors[$k] = $e->getMessage();
			$data[$k] = 'ng';
		}
	}
}

/*------------------------------------------------------------
 * setting_dat_file
 */
$line = file(SETTING_DAT_FILE);
$setting = explode(',', $line[0]);
$data['session_timeout'] = $setting[0];
$data['timeout'] = $setting[1];
$data['line_max'] = $setting[2];
$data['oldlog_max'] = $setting[3];
$data['from_mailaddr'] = $setting[4];
$data['cs_gazer_url'] = $setting[5];

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['currentpage'] = "config";
$data['errors'] = $errors;

/*
 * php infomation
 */
$data['cron_setting'] = HOME_PATH . "lib/check.php";
$data['php_version'] = PHP_VERSION;
$data['ping_cmd'] = $ping_cmd;
$data['sendmail_cmd'] = $sendmail_cmd;

printhtml($data);

