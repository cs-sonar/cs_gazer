cs_gazer
=========

PHPで作ったサーバー死活監視プログラム

![画面の例](https://raw.github.com/cs-sonar/cs_gazer/master/screenshot.png)

Install
--------
ドキュメントルートにて

    git clone git://github.com/cs-sonar/cs_gazer.git

パーミッションの設定
--------
WEBサーバーから書き込みが可能にする必要があります

    chmod 777 ./cs_gazer

cron登録
--------

    crontab -e

cron登録例

    */15 * * * * php -f /path/to/cs_gazer/lib/check.php
