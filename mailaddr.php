<?php
/**
 * mailaddr.php
 * メールアドレスの追加、削除
 */

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
// class
try{
	$mail = new Csv(MAIL_DAT_FILE);
	$group = new Csv(MAILGROUP_DAT_FILE);
} catch (Exception $e){
	error($e->getMessage());
}
/*------------------------------------------------------------*/
// 削除ボタンが押された時
if (isset($FORM['del'])) {
	// エラーチェック
	if(!$_POST['mailaddr']){ $errors[] = "削除対象アドレスが選択されていません"; }
	// チェック中メールアドレスidを取得
	$mailGroup = $group->getCsv();
	foreach ($mailGroup as $v) {
		$mailid = explode(':',$v['mailid']);
		foreach($mailid as $vv) {
			if(in_array($vv, $_POST['mailaddr'])) { $errors[] = '削除対象アドレスはグループでの送信先に使用されています。<a href="mailgroup.php">メールグループ設定</a>を確認してください。'; }
		}
	}
	// 削除開始
	foreach($_POST['mailaddr'] as $v) {
		$mail->delCsv($v);
	}
}

/*------------------------------------------------------------*/
// 追加ボタンが押された時
if (isset($FORM['add'])) {
	// エラーチェック
	if(!$FORM['mailaddr']){ $errors[] = "メールアドレスが入力されていません。"; }
	// メールアドレスIDで一番大きなものを+1し、新規のIDとしてセットする
	$mailAddrlist = $mail->getCsv();
	foreach($mailAddrlist as $v) {
		$mailid[] = $v['id'];
		$mailaddrnow[] = $v['mailaddr'];
	}
	$id = max($mailid) + 1;
	// POSTされたメールアドレスのチェックを行いながら追加用配列を作成する
	$addmailaddr = explode(',',$FORM['mailaddr']);
	foreach ($addmailaddr as $addaddr) {
		if(!f_AddressChk($addaddr)) { $errors[] = "追加しようとしているメールアドレスが不正です。" . $addaddr; }
		if(in_array($addaddr, $mailaddrnow)) { $errors[] = "追加しようとしているメールアドレスはすでにリストに存在します。" . $addaddr; }
		$adddata[$id] = $addaddr;
		$id++;
	}
	// 追加処理を行う
	if(!$errors){
		foreach ($adddata as $k => $v){
			$arr = array($k,$v);
			$mail->addCsv($arr);
		}
	}else{
		// エラーがある場合は入力内容を保持して表示
		$data['mailaddr'] = $FORM['mailaddr'];
	}
}

/*------------------------------------------------------------*/
$mailGroup = $group->getCsv();
$mailAddr = $mail->getCsv();

// チェック中メールアドレスidを取得
foreach ($mailGroup as $v) {
	$mailid = explode(':',$v['mailid']);
	foreach($mailid as $vv) {
		$is_checked[$vv] = $vv;
	}
}
$is_checked = array_unique($is_checked);

foreach($mailAddr as $v) {
	$allMailAddr[$v['id']] = $v['mailaddr'];// 全てのメールアドレスを取得
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['mailaddrAsGroup'] = $mailaddrAsGroup;
$data['allMailAddr'] = $allMailAddr;
$data['is_checked'] = $is_checked;
$data['errors'] = $errors;
$data['currentpage'] = "mailaddr";

printhtml($data);
