cs_gazer
=========

PHPで作ったサーバー死活監視プログラム

![画面の例](https://raw.github.com/cs-sonar/cs_gazer/master/screenshot.png)

Install
--------
ドキュメントルートにて

    git clone git://github.com/cs-sonar/cs_gazer.git


WEBサーバーから書き込みが可能にする必要があります

    chmod 777 ./cs_gazer

設置したcs_gazerにブラウザから接続します。

    http://example.com/cs_gazer

インストール画面が表示されるので進めて下さい。

cron登録
--------

    crontab -e

cron登録例

    */15 * * * * php -f /path/to/cs_gazer/lib/check.php >/dev/null 2>&1
