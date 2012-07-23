<?php
require_once "./include/init.inc";
$session->clear(); // セッション情報破棄
$session->setAuthenticated(false); // 認証情報破棄
header('Location:./login.php'); // リダイレクト
exit;