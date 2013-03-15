<?php
/**
 * installer.php
 */

/*------------------------------------------------------------
 環境設定
 ------------------------------------------------------------*/
// インクルード
require('./include/init.inc');



$FORM = form_xss(); // フォームパラメータ取得

if($session->isAuthenticated()) {
	$session->clear(); // セッション情報破棄
	$session->setAuthenticated(false); // 認証情報破棄
}
//PHPのバージョンチェック
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    error_plane('PHP5 より古いバージョンの PHP を使っています。使用中のPHPバージョンは ' . PHP_VERSION . " です。<br />cs_gazerはPHP5以上でなければ動作しません。");
    $php4 = 1;
    exit;
}

/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
if(!isset($php4)){
    try{
    	$check = new Csv(AUTH_DAT_FILE);
    } catch (Exception $e){
    	$install_flg = 1;
    }
}

if(!$install_flg){
	header('Location:./login.php');
	exit;
}else{

	/*-------------------------------------------------------------
	 * ファイル削除が実行された時
	*/
	if($FORM['clean']){
	unlink(AUTH_DAT_FILE);
	unlink(CHECK_CURRENT_DAT_FILE);
	unlink(CHECK_LOG_DAT_FILE);
	unlink(CHECK_LOG_EXEC_DAT_FILE);
	unlink(LIST_DAT_FILE);
	unlink(PORTS_DAT_FILE);
	unlink(MAILGROUP_DAT_FILE);
	unlink(MAIL_DAT_FILE);
	unlink(SETTING_DAT_FILE);
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

	// datディレクトリの存在チェック。なければ作る。
	if(!file_exists(HOME_PATH . '/dat')){
		mkdir(HOME_PATH . 'dat' , 0777);
		chmod(HOME_PATH . 'dat' , 0777);
		clean_dat_file(HOME_PATH . 'dat/.htaccess', 'Order allow,deny'."\n".'Deny from all');
		chmod(HOME_PATH . 'dat/.htaccess' , 0644);
	}	
	
	// テンプレートファイルの書き込み権限チェック
	if(is_writable(HOME_PATH . '/templates_c')){
		$data['smartycache'] = 'ok';
	}else{
		$data['smartycache'] = 'ng';
		$data['smartycache'] = '書き込み権限がありません';
	}


	// dat dirの書き込み権限チェック
	if(is_writable(HOME_PATH . '/dat')){
		$data['dat'] = 'ok';
	}else{
		$data['dat'] = 'ng';
		$errors['dat'] = 'ng';
	}

	// ファイルの存在チェック(dat_files_array)
	foreach($dat_files as $k => $v){
		if(file_exists($v)){
			$data[$k] = 'ng';
			$errors[$k] = $v . 'ファイルが存在します。削除して下さい。';
			$error_exists_flg = 1;
		}else{
			$data[$k] = 'ok';
		}
	}

	if($FORM['install'] && !$error_exists_flg){
		// 入力チェック
		if(empty($FORM['user'])) $errors['user'] = 'IDが入力されていません';
		if(empty($FORM['password'])) $errors['password'] = 'パスワードが入力されていません';
		if(empty($FORM['passwordConfirm'])) $errors['passwordConfirm'] = '確認用のパスワードが入力されていません';
		if($FORM['password'] != $FORM['passwordConfirm']) $errors['password'] = 'パスワードの組が一致しません';
		//if(array_key_exists($FORM['user'], $adminListUser)) $errors['user'] = 'すでに使用されているIDです';
		if(!preg_match("/^[a-zA-Z0-9]+$/", $FORM['user'])){ $errors['user'] = "値が不正です"; }
		if(!preg_match("/^[a-zA-Z0-9]+$/", $FORM['password'])){ $errors['passwod'] = "値が不正です"; }
		if(preg_match("/^user$/", $FORM['user'])){ $errors['user'] = "値userは使用できません。"; }
		if(preg_match("/^password$/", $FORM['password'])){ $errors['password'] = "値passwordは使用できません。"; }
		if(preg_match('/,/', $FORM['user'])){ $errors['user'] = "値が不正です"; }
		if(preg_match('/,/', $FORM['password'])){ $errors['password'] = "値が不正です"; }
		if(12 < mb_strlen($FORM['user'])){$errors[]="文字数オーバーです。12文字以内で入力して下さい。";}
		if(20 < mb_strlen($FORM['password'])){$errors[]="文字数オーバーです。20文字以内で入力して下さい。";}

		if(!$FORM['mailaddr']){$errors[] = "メールアドレスが入力されていません。";}
		if(!f_AddressChk($FORM['mailaddr'])) {$errors[] = "設定しようとしているメールアドレスが不正です。";}
		if(200 < mb_strlen($FORM['mailaddr'])){$errors[]="文字数オーバーです。200文字以内で入力して下さい。";}
        if(!$FORM['frommailaddr']){$errors[] = "メールアドレスが入力されていません。";}
        if(!f_AddressChk($FORM['frommailaddr'])) {$errors[] = "設定しようとしているメールアドレスが不正です。";}
        if(200 < mb_strlen($FORM['frommailaddr'])){$errors[]="文字数オーバーです。200文字以内で入力して下さい。";}

		if(!$errors){
			unlink(CHECK_CURRENT_DAT_FILE);
			unlink(CHECK_LOG_DAT_FILE);
			unlink(CHECK_LOG_EXEC_DAT_FILE);
			touch(CHECK_LOG_EXEC_DAT_FILE);
			chmod(CHECK_LOG_EXEC_DAT_FILE,0666);
			clean_dat_file(CHECK_LOG_DAT_FILE, $header);
			clean_dat_file(CHECK_CURRENT_DAT_FILE, $header);
			unlink(AUTH_DAT_FILE);
			clean_dat_file(AUTH_DAT_FILE, $admin_header . "\n".$FORM['user']."," . $FORM['password']);
			unlink(LIST_DAT_FILE);
			clean_dat_file(LIST_DAT_FILE, $checklist_header);
			unlink(PORTS_DAT_FILE);
			clean_dat_file(PORTS_DAT_FILE, '25,110');
			unlink(MAILGROUP_DAT_FILE);
			clean_dat_file(MAILGROUP_DAT_FILE, $mailgroup_header . "\nlist001,1");
			unlink(MAIL_DAT_FILE);
			clean_dat_file(MAIL_DAT_FILE, $mail_header . "\n1,".$FORM['mailaddr']);
			unlink(SETTING_DAT_FILE);
			clean_dat_file(SETTING_DAT_FILE, "7200,3,5000,5,".$FORM['frommailaddr']);
			//header('Location:./login.php');
			//exit;

			$finish_install = '1';
		}
	}
}

/*------------------------------------------------------------
 出力
 ------------------------------------------------------------*/
if($error_exists_flg){
	$data['error_exists_flg'] = 1;
}
if($finish_install){
	$data['finish_install'] = 1;
}
$data['errors'] = $errors;
printhtml($data);
