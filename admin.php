<?php
/**
 * admin.php
 * ログインユーザーの追加/削除
 */

// a
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
$csv = new Csv(AUTH_DAT_FILE);
$adminLists = $csv->getCsv();
$adminListUser = $csv->getCsvColumn('user');

if(isset($FORM['edit'])){
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
	if(12 < mb_strlen($FORM['user'])){ $errors[]="文字数オーバーです。12文字以内で入力して下さい。";}
	if(20 < mb_strlen($FORM['password'])){ $errors[]="文字数オーバーです。20文字以内で入力して下さい。";}
	
	if(!isset($errors)){
		$csv->editCsv($FORM['password'], $session->get('user'), password);
		if($session->get('user') !== $FORM['user']){
			$csv->editCsv($FORM['user'], $session->get('user'), user);
			$session->set('user',$FORM['user']);
		}
		$flg = 1;
	}
	$data['user'] = $FORM['user'];
}

$admins = array();
if($flg){$adminLists = $csv->getCsv();$data['finish'] = "更新が完了しました";} // 更新が行われればリストを再取得し、更新完了を通知
foreach($adminLists as $account){
	$admins[] = $account['user'];
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['loginuser'] = $session->get('user');
$data['admins'] = $admins;
$data['errors'] = $errors;
$data['currentpage'] = "admin";
printhtml($data);
