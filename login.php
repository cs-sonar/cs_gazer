<?php
/**
 * login.php
 */

/*------------------------------------------------------------
 環境設定
 ------------------------------------------------------------*/
// インクルード
require('./include/init.inc');
$FORM = form_xss(); // フォームパラメータ取得

// 認証ファイルがなければ新規インストールとみなす
if(!file_exists(AUTH_DAT_FILE)){
	header('Location:./installer.php');
}

// 認証済みなら管理画面へ
if($session->isAuthenticated()){
	header('Location:./index.php');
	exit();
}

/*------------------------------------------------------------
 プログラム
 ------------------------------------------------------------*/
// フォーム送信時
if( isset($FORM['submit_x']) && isset($FORM['submit_y']) ){
	// 入力値取得
	$id = $FORM['id'];
	$password = $FORM['password'];

	//エラー
	if(empty($FORM['id'])){
		$errors['id'] = 'IDが未入力です';
	}
	if(empty($FORM['password'])){
		$errors['password'] = 'パスワードが未入力です';
	}

	//認証データファイル読み込み
	try{
		$csv = new Csv(AUTH_DAT_FILE);
	} catch (Exception $e){
		header('Location:./installer.php');
		exit;
	}
	
	$csvline = $csv->getCsvLine($FORM['id']);
	if(!$csvline) {
		$errors['auth'] = 'ID又はパスワードが違います';
	}

	//認証
	if($csvline['password'] === $FORM['password'] ){
		$session->set('user', $id);
		$session->setAuthenticated(true);
		header('Location:./index.php'); // 成功時リダイレクト
		exit;
	}

	if(!isset($errors)){
		$errors['auth'] = 'ID又はパスワードが違います';
	}
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['nologin'] = 1;
$data['errors'] = $errors;
printhtml($data);
