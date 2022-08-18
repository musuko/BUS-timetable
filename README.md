# BUS-timetable
GTFSのバスタイムテーブルをMYSQLに保存し、路線の時刻表を作成するプログラム

準備
(0) 大きいデータを読み込むため、php.iniのmax_execution_timeを600　秒位にする。
⑴　MYSQLにdbbusというデータベースを作成
(2) php/dbconnectphpを編集し、ホスト名、ユーザー名、パスワードを自分の設定に合わせる。
デフォルトは、$db = new PDO('mysql:dbname=dbbus; host=127.0.0.1; charset=utf8', 'root', ''); 
(3) バス情報を読み込む。まず、load_files.php を実行する。(GTFSよりダウンロードし、小さいサイズのデータをMYSQLテーブルに挿入。全テーブルのカラム行作成。)
(4) バス情報を読み込む。load_fare_rules.php を実行する。(fare_rules.txtのデータをテーブルに挿入。)
(5) バス情報を読み込む。load_shapes.php を実行する。(shapes.txtのデータをテーブルに挿入。)
(6) バス情報を読み込む。load_stop_times.php を実行する。(このファイルが最も大きい。stop_times.txtのデータをテーブルに挿入。)

実行
<1> index.php を実行する。
(2) ルート、曜日、停留所、trip_id(一本の運行)を選択し、バスの始発～終点時刻表や停留所時刻表を作成する。

欠点
・動きが重い。
・見た目

今後
・地図で停留所を表示
・地図で停留所を選択
・料金計算
・英語、韓国語、中国語表示
全て、データ情報にあるので、やればできるはず。
