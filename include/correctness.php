<?php

/**
 * correctness.php
 * ファイルの正当性チェック
 */

define("AUTH_DAT_FILE", HOME_PATH . 'dat/admin.dat');
define("LIST_DAT_FILE", HOME_PATH . 'dat/checklist.dat');
define("PORTS_DAT_FILE", HOME_PATH . 'dat/ports.dat');
define("MAIL_DAT_FILE", HOME_PATH . 'dat/mail.dat');
define("MAILGROUP_DAT_FILE", HOME_PATH . 'dat/mail_group.dat');
define("CHECK_CURRENT_DAT_FILE", HOME_PATH . 'dat/check_log_current.dat');
define("CHECK_LOG_EXEC_DAT_FILE", HOME_PATH . 'dat/check_log_exec.dat');
define("CHECK_LOG_DAT_FILE", HOME_PATH . 'dat/check_log.dat');

if(!is_writable(HOME_PATH . 'dat')){
	$errors['dat'] = 'datディレクトリに書き込み権限がありません';
}else{
	if(!is_writable(AUTH_DAT_FILE)){
	}
}

// headers
$admin_header = "user,password";
$mailgroup_header = "mailgroup,mailid";
$mail_header = "id,mailaddr";
$checklist_header = "host,check,summary,mailgroup,ports,pingchk,httpchk";
$checklog_header = "id,host,date,check,ports,pingchk,httpchk";
$checklogcurrent_header = "id,host,date,check,ports,pingchk,httpchk";

