<?php
    try {                                   //PHPからMySQLに接続する
        $db = new PDO('mysql:dbname=dbbus; host=127.0.0.1; charset=utf8', 'root', '');  //データベースオプジェクトの作成　行いたい処理
    } catch (PDOException $e) {  //エラークラス エラーのインスタンスを入れる変数
        echo 'DB接続エラー: ' . $e->getMessage();  //tryの処理ができなかった時の処理
    }
?>
