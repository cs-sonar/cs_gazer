<?php
//php init
session_cache_limiter('none');
error_reporting(E_ALL ^ E_NOTICE);
ini_set( "display_errors", "On");
ini_set("session.gc_maxlifetime", SESSION_TIMEOUT);

//require init
require_once("config.inc.php");
require_once("common.php");
require_once("Csv.php");
require_once("Session.php");

//ホームファイルのバージョンチェック
if(!is_writable(HOME_PATH)){
	error_plane("cs_gazerをインストールしたディレクトリ「" . HOME_PATH . "」に書き込み権限がありません。<br />該当のディレクトリに書き込み権限を与えて下さい。");
}

// キャッシュディレクトリの存在チェック。なければ作る。
if(!file_exists(HOME_PATH . '/templates_c')){
	mkdir(HOME_PATH . '/templates_c' , 0777);
	chmod(HOME_PATH . '/templates_c' , 0777);
	clean_dat_file(HOME_PATH . 'templates_c/.htaccess', 'Order allow,deny'."\n".'Deny from all');
	chmod(HOME_PATH . 'dat/.htaccess' , 0644);
}

//PHPのバージョンチェック
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    error_plane('PHP5 より古いバージョンの PHP を使っています。使用中のPHPバージョンは ' . PHP_VERSION . " です。<br />cs_gazerはPHP5以上でなければ動作しません。");
}

//smarty init
$data = array(); // Smarty用配列作成
$data['home_url'] = HOME_URL;

//session init
$session = new Session();
