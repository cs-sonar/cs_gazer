<?php
/**
 * mailgroup.php
 * メールグループ設定
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

// 編集ボタンが押された時
if ($FORM['set']){
	$mailAddr = $mail->getCsv();
	foreach($_POST['setmail'] as $v) { // $_POST['setmail']はチェックされた値の配列
		foreach($mailAddr as $vv ){
			if($vv["mailaddr"] === $v){
				$mailid[] = $vv['id']; // チェックされたメールアドレスをidに変更する
			}
		}
	}
	$mailid = implode(':', $mailid);
	$group->editCsv($mailid, $FORM['group'], 'mailid'); // 編集を実行
}

// 追加が押された時
if ($FORM['add']){
	//エラーチェック
	if(in_array($FORM['group'],$group->getCsvColumn('mailgroup'))){ $errors[] = "グループ名が重複しています";}
	if(empty($FORM['group'])) $errors[] = 'グループ名が入力されていません';
	if(!preg_match("/^[a-zA-Z0-9]+$/", $FORM['group'])){$errors[] = "値が不正です";}
	if(12 < mb_strlen($FORM['group'])){$errors[]="文字数オーバーです。12文字以内で入力して下さい。";}
	if(preg_match("/^group$/", $FORM['group'])){$errors['group'] = "値groupは使用できません。";}
	if(preg_match('/,/', $FORM['group'])){$errors['group'] = "値が不正です";}
	//入力開始
	if(!$errors){
		$arr = array();
		$arr[] = $FORM['group'];
		$group->addCsv($arr); // 追加を実行
	}
}

// 削除ボタンが押された時
if ($FORM['groupdel']){
	$checklist = new Csv(LIST_DAT_FILE);
	$checklist = $checklist->getCsvColumn('mailgroup');
	if(in_array($FORM['group'],$checklist)){ $errors[] = "監視リストにてメール送信先として設定されているグループです。<br />設定を解除後、実行して下さい。"; }
	$mailGroup = $group->getCsv();
    $mailCount = count($mailGroup);
    if($mailCount === 1){ $errors[] = "メールグループが１つしかない為、削除できません";}

	if(!$errors) {
		$group->delCsv($FORM['group']); // 削除を実行
	}
}

$mailGroup = $group->getCsv();
$mailAddr = $mail->getCsv();

foreach ($mailGroup as $v) {
	$mailGroupAsMailaddr = explode(':',$v['mailid']); // メールアドレスidを配列にする
	foreach($mailGroupAsMailaddr as $vv) {
		$tmp = $mail->getCsvLine($vv); // メールアドレス一覧datからidの1行を取得
		$mailaddrAsGroup[$v['mailgroup']][$tmp['mailaddr']] = 1; // メールアドレスをkeyとしてvalueにフラグを立てる。 
	}
}

foreach($mailAddr as $v) {
	$allMailAddr[] = $v['mailaddr'];// 全てのメールアドレスを取得
}

/*------------------------------------------------------------
 smarty
 ------------------------------------------------------------*/
$data['mailaddrAsGroup'] = $mailaddrAsGroup;
$data['allMailAddr'] = $allMailAddr;
$data['errors'] = $errors;
$data['currentpage'] = "mailgroup";

printhtml($data);
