<?php

/**
 * const
 */

$home_path = str_replace("include", '',dirname(__FILE__));
define("HOME_PATH",$home_path );
define("HOME_URL", "./"); //gazerのパス

// smarty dir
define("SMARTY_DIR", HOME_PATH.'lib/smarty/' );

// dat_files
define("AUTH_DAT_FILE", HOME_PATH . 'dat/admin.dat');
define("LIST_DAT_FILE", HOME_PATH . 'dat/checklist.dat');
define("PORTS_DAT_FILE", HOME_PATH . 'dat/ports.dat');
define("MAIL_DAT_FILE", HOME_PATH . 'dat/mail.dat');
define("SETTING_DAT_FILE", HOME_PATH . 'dat/setting.dat');
define("MAILGROUP_DAT_FILE", HOME_PATH . 'dat/mail_group.dat');
define("CHECK_CURRENT_DAT_FILE", HOME_PATH . 'dat/check_log_current.dat');
define("CHECK_LOG_EXEC_DAT_FILE", HOME_PATH . 'dat/check_log_exec.dat');
define("CHECK_LOG_DAT_FILE", HOME_PATH . 'dat/check_log.dat');

//dat_files_array
$dat_files = array(
	"auth_dat_file" => AUTH_DAT_FILE,
	"list_dat_file" => LIST_DAT_FILE,
	"ports_dat_file" => PORTS_DAT_FILE,
	"mail_dat_file" => MAIL_DAT_FILE,
	"setting_dat_file" => SETTING_DAT_FILE,
	"mailgroup_dat_file" => MAILGROUP_DAT_FILE,
	"check_current_dat_file" => CHECK_CURRENT_DAT_FILE,
	"check_log_dat_file" => CHECK_LOG_DAT_FILE,
	"check_log_exec_dat_file" => CHECK_LOG_EXEC_DAT_FILE
);

// headers
$admin_header = "user,password";
$mailgroup_header = "mailgroup,mailid";
$mail_header = "id,mailaddr";
$checklist_header = "host,check,summary,mailgroup,ports,pingchk,httpchk";
$header = "id,host,date,check,ports,pingchk,httpchk"; //チェックログのヘッダー
$checklogcurrent_header = "id,host,date,check,ports,pingchk,httpchk";

/*------------------------------------------------------------
* setting_dat_file
*/
if(file_exists(SETTING_DAT_FILE)){
	$line = file(SETTING_DAT_FILE);
	$setting = explode(',', $line[0]);
}else{
	$setting = array(7200,2,5000,5,'from@example.com',"http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
}

/*------------------------------------------------------------
* ping cmd
*/
if(file_exists("/bin/ping")){
	$ping_cmd = "/bin/ping";
}elseif(file_exists("/sbin/ping")){
	$ping_cmd = "/sbin/ping";
}elseif(file_exists("/usr/sbin/ping")){
	$ping_cmd = "/usr/sbin/ping";
}else{
	$ping_cmd = "ping";
}


/*------------------------------------------------------------
* sendmail cmd
*/
if(file_exists("/usr/sbin/sendmail")) {
	$sendmail_cmd = "/usr/sbin/sendmail";
}elseif(file_exists("/usr/lib/sendmail")) {
	$sendmail_cmd = "/usr/lib/sendmail";
}else{
	$sendmail_cmd = "sendmail";
}

// session settings
$session_timeout = $setting[0];
define("SESSION_TIMEOUT",$session_timeout); // 2時間
define("SESSION_KEY",'feLvH4hfeHjfvY75Fdh'); // seacret key

// チェック設定
$timeout = $setting[1]; //チェックのタイムアウト判定時間
$line_max = $setting[2]; //チェックログのMAX行数（これ以上の場合はローテートを行います）
$oldlog_max = $setting[3]; //過去ログのMAX件数。これ以上は削除します
$from_mailaddr = $setting[4]; //送信元メールアドレス
$cs_gazer_url = $setting[5]; //cs_gazer設置先URL

//debug mode
//define("SMARTY_DEBUG",'true');
