<?php
// 停留所の時刻表表示プログラム。（ルート選択→曜日選択→停留所選択)
session_start();
require 'php/dbconnect.php';
$num = 150;	//1ページ表示数
$clear = filter_input(INPUT_POST, 'clear');		//clearボタンをフォームから受け取り変数に代入

if ($clear === "clear") {		//clearボタンが押されたら、
	unset($_SESSION['route_id'])  ;	//セッションを破壊する
	unset($_SESSION['service_id']);	//セッションを破壊する
	unset($_SESSION['stop_id'])   ;	//セッションを破壊する
	unset($_SESSION['start'])     ;	//セッションを破壊する
}
$route_id = filter_input(INPUT_POST, 'route_id');		//$route_idをフォームから受け取り変数に代入
$service_id = filter_input(INPUT_POST, 'service_id');		//$service_idをフォームから受け取り変数に代入
$stop_id = filter_input(INPUT_POST, 'stop_id');			//$stop_idをフォームから受け取り変数に代入
$select = filter_input(INPUT_POST, 'select');		//selectをフォームから受け取り変数に代入

$row_num = filter_input(INPUT_POST, 'row_num');		//prevをフォームから受け取り変数に代入
if (!isset($_SESSION['start'])) {
	$_SESSION['start']      = 0;
}   //$_SESSION['start']が未定義 の場合


if ($select === "select1") {
	$_SESSION['route_id']   = $route_id;		//セレクトボックスで選択した経路をセッションに入れる
	$_SESSION['service_id'] = "";
	$_SESSION['stop_id']    = "";
} elseif ($select === "select2") {
	$_SESSION['service_id'] = $service_id ?? "";		//セレクトボックスで選択したサービスをセッションに入れる
	$_SESSION['stop_id']    = "";
} elseif ($select === "select3") {
	$_SESSION['stop_id'] = $stop_id ?? '';		//セレクトボックスで選択したバス停をセッションに入れる
} else {
	$_SESSION['route_id']   = "";
	$_SESSION['service_id'] = "";
	$_SESSION['stop_id']    = "";
}
if ($row_num === "prev") {	//prevボタンが定義され、prevボタンが押された場合、
	$_SESSION['start'] = max($_SESSION['start'] - $num, 0);	//$_SESSION['start']を$num減らす
}
if ($row_num === "next") {	//nextボタンが定義され、nextボタンが押された場合、
	$_SESSION['start'] = $_SESSION['start'] + $num;	//$_SESSION['start']を$num増やす
}

?>



<form action="" method="post">
	ルート: <select name="route_id" style="height:30; width:600;">
		<?php	//ルートを選択するセレクトボックス用
		echo '<option value=""></option>';	//空行
		$sql = $db->query('SELECT route_id, route_long_name, route_short_name FROM routes ORDER BY route_long_name');
		foreach ($sql as $row) {
			echo '<option value="' . $row['route_id'] . '"';
			if (isset($_SESSION['route_id'])) {
				if ($row['route_id'] === $_SESSION['route_id'] && (isset($select) || isset($row_num))) {
					echo 'selected';
				}
			}
			echo '>'  . $row['route_long_name'] . ' >>> ' .$row['route_short_name'] . '</option>';
		}
		?>
	</select>
	<input type="submit" name="select" value="select1" style="height:30; width:100;">
</form>

<form action="" method="post">
	曜日 : <select name="service_id" style="height:30; width:600;">
		<?php		//サービス(曜日)を選択するセレクトボックス用。曜日を決めて、trip_idを絞り込む。
		echo '<option value=""></option>';
		$sql = $db->prepare('SELECT DISTINCT t.service_id FROM routes r,trips t  WHERE r.route_id=? AND r.route_id=t.route_id ORDER BY t.service_id');
		$sql->bindParam(1, $_SESSION['route_id'], PDO::PARAM_STR);	//サービスを選択するときは、上で選択したルートで絞り込んでおく
		$sql->execute();
		foreach ($sql as $row) {
			echo '<option value="' . $row['service_id'] . '"';
			if ($row['service_id'] === $_SESSION['service_id'] && (isset($select) || isset($row_num))) {
				echo 'selected';
			}
			echo '>'  . $row['service_id'] . '</option>';
		}
		?>
	</select>
	<input type="submit" name="select" value="select2" style="height:30; width:100;">
</form>
<!-- "1835444037"
"24_土曜(共通)" -->

<form action="" method="post">
	バス停: <select name="stop_id" style="height:30; width:600;">
		<?php		//バス停を選択するセレクトボックス用。trip_idからstop_idを絞り込む。
		echo '<option value=""></option>';
		$sql = $db->prepare('SELECT DISTINCT s.stop_id, s.stop_name FROM routes r, trips t, stops s, stop_times st WHERE r.route_id=? 
		AND t.service_id=? AND t.trip_id = st.trip_id AND r.route_id=t.route_id AND st.stop_id = s.stop_id ORDER BY st.arrival_time');
		$sql->bindParam(1, $_SESSION['route_id'], PDO::PARAM_STR);	//バス停を選択するときは、上で選択したルートで絞り込んでおく
		$sql->bindParam(2, $_SESSION['service_id'], PDO::PARAM_STR);	//バス停を選択するときは、上で選択したサービスで絞り込んでおく
		$sql->execute();
		foreach ($sql as $row) {
			echo '<option value="' . $row['stop_id'] . '"';
			if ($row['stop_id'] === $_SESSION['stop_id'] && (isset($select) || isset($row_num))) {
				echo 'selected';
			}
			echo '>'  . $row['stop_name'] . '</option>';
		}
		?>
	</select>
	<input type="submit" name="select" value="select3" style="height:30; width:100;">
	<br>
	<input type="submit" name="row_num" value="prev" style="height:30; width:100;">
	<input type="submit" name="row_num" value="next" style="height:30; width:100;">
	<input type="submit" name="clear" value="clear" style="height:30; width:100;">

</form>


<?php

if (isset($select) || isset($row_num)) {
	$sql = $db->prepare(' SELECT r.route_short_name, r.route_long_name, t.service_id, t.trip_id, t.trip_headsign, st.arrival_time, st.departure_time, st.stop_sequence, st.stop_headsign, s.stop_id, s.stop_name FROM routes r, trips t, stop_times st, stops s WHERE r.route_id = t.route_id AND t.trip_id = st.trip_id AND st.stop_id = s.stop_id AND r.route_id=? AND t.service_id = ? AND s.stop_id=? ORDER BY st.arrival_time,st.stop_sequence, t.service_id LIMIT ?,?');
	$sql->bindParam(1, $_SESSION['route_id'], PDO::PARAM_STR);
	$sql->bindParam(2, $_SESSION['service_id'], PDO::PARAM_STR);
	$sql->bindParam(3, $_SESSION['stop_id'], PDO::PARAM_STR);
	$sql->bindParam(4, $_SESSION['start'], PDO::PARAM_INT);
	$sql->bindParam(5, $num, PDO::PARAM_INT);
	$sql->execute();
	echo " バス停  出発時間  経由  終点";
	echo '<br>';
	foreach ($sql as $row) {

		// echo $row[('stop_id')];
		// echo ' - ';
		echo $row[('stop_name')];	//バス停
		echo ' - ';
		// echo $row[('stop_sequence')];		//始発からのバス停順
		// echo ' - ';
		echo $row[('departure_time')];	//出発時刻
		echo ' - ';
		echo $row[('trip_headsign')];	//経由
		echo ' - ';
		echo $row[('stop_headsign')];	//終点
		echo '<br>';
	}
	echo '<br>';
}

?>