<?php
//一応最新アプリ。
// NEXT STEP:  selectboxで選択するたびに、他の選択肢が減るようにする	>>> した。2023/11/6
// 停留所の時刻表表示プログラム。（ルート選択→曜日選択→停留所選択)		>>> した。選択順番も自由。2023/11/6
// 表示順番が、同じバスごと(trip_id)に、時間順に整列できている。
// 表示に時間がかかるので、高速化。
// 複数のルートやバス停を選択可能とした。>>> 2024/1/23
// セッションクリアボタンを追加した。>>> 2024/1/23
session_start();
require 'php/dbconnect.php';

//セッションクリア用
if (filter_input(INPUT_GET, 'button') === 'bt_unset') {
	unset($_SESSION['route_id']);
	unset($_SESSION['stop_id']);
	unset($_SESSION['service_id']);
	unset($_SESSION['trip_id']);
}
echo '<form method="GET">';
echo '<button type="submit" name="button" value="bt_unset">UNSET</button>';
echo '</form>';


//未定義の場合、[]にする
if (!isset($_SESSION['route_id'])) {
	$_SESSION['route_id']    = [];
}
if (!isset($_SESSION['stop_id'])) {
	$_SESSION['stop_id']      = [];
}
if (!isset($_SESSION['service_id'])) {
	$_SESSION['service_id'] = [];
}
if (!isset($_SESSION['trip_id'])) {
	$_SESSION['trip_id']      = [];
}
if (!isset($_GET['route_id'])) {
	$_GET['route_id'] = [];
}
if (!isset($_GET['stop_id'])) {
	$_GET['stop_id'] = [];
}
if (!isset($_GET['service_id'])) {
	$_GET['service_id'] = [];
}
if (!isset($_GET['trip_id'])) {
	$_GET['trip_id'] = [];
}

//フォーム入力内容を変数に入れ
 $route_id    = $_GET['route_id']  ;
 $stop_id     = $_GET['stop_id']   ;
 $service_id  = $_GET['service_id'];
 $trip_id     = $_GET['trip_id']   ;
//フォーム内容を記憶しておく
if (filter_input(INPUT_GET, 'button') === 'bt_route_id') {
	if ($_GET['route_id'][0]  ==="") {$route_id   = [];}
	$_SESSION['route_id']   = $route_id;
}
if (filter_input(INPUT_GET, 'button') === 'bt_stop_id') {
	if ($_GET['stop_id'][0]   ==="") {$stop_id    = [];}
	$_SESSION['stop_id']   = $stop_id;
}
if (filter_input(INPUT_GET, 'button') === 'bt_service_id') {
	if ($_GET['service_id'][0]==="") {$service_id = [];}
	$_SESSION['service_id'] = $service_id;
}
if (filter_input(INPUT_GET, 'button') === 'bt_trip_id') {
	if ($_GET['trip_id'][0]   ==="") {$trip_id    = [];}
	$_SESSION['trip_id']    = $trip_id;
}

//sql文絞り込み用
if ($_SESSION['route_id'] === []) {
	$route_id_where = " r.route_id LIKE '%' ";
} else {
	$i = 0;
	$route_id_where = "(";
	foreach ($_SESSION['route_id'] as $row) {
		if ($i === 0) {
			$route_id_where .= " r.route_id= '".$row."'";
		} else {
			$route_id_where .= " OR  r.route_id= '" . $row."'";
		}
		$i++;
	}
	$route_id_where .= ")";
}

if ($_SESSION['stop_id'] === []) {
	$stop_id_where = " s.stop_id LIKE '%' ";
} else {
	$i = 0;
	$stop_id_where = "(";
	foreach ($_SESSION['stop_id'] as $row) {
		if ($i === 0) {
			$stop_id_where .= " s.stop_id= '".$row."'";
		} else {
			$stop_id_where .= " OR  s.stop_id= '" . $row."'";
		}
		$i++;
	}
	$stop_id_where .= ")";
}

if ($_SESSION['service_id'] === []) {
	$service_id_where = " t.service_id LIKE '%' ";
} else {
	$i = 0;
	$service_id_where = "(";
	foreach ($_SESSION['service_id'] as $row) {
		if ($i === 0) {
			$service_id_where .= " t.service_id= '".$row."'";
		} else {
			$service_id_where .= " OR  t.service_id= '" . $row."'";
		}
		$i++;
	}
	$service_id_where .= ")";
}

if ($_SESSION['trip_id'] === []) {
	$trip_id_where = " t.trip_id LIKE '%' ";
} else {
	$i = 0;
	$trip_id_where = "(";
	foreach ($_SESSION['trip_id'] as $row) {
		if ($i === 0) {
			$trip_id_where .= " t.trip_id= '".$row."'";
		} else {
			$trip_id_where .= " OR  t.trip_id= '" . $row."'";
		}
		$i++;
	}
	$trip_id_where .= ")";
}


$select = $route_id_where . ' AND ' . $stop_id_where . ' AND ' . $service_id_where . ' AND ' . $trip_id_where;
$select1 = $stop_id_where . ' AND ' . $service_id_where . ' AND ' . $trip_id_where;
$select2 = $route_id_where . ' AND ' . $service_id_where . ' AND ' . $trip_id_where;
$select3 = $route_id_where . ' AND ' . $stop_id_where . ' AND ' . $trip_id_where;

?>

<!-- ルート -->
<form method="GET">
	<select multiple name="route_id[]" style="height:90; width:600;">
		<option value=""></option>
		<?php $sql0 = 'SELECT DISTINCT r.route_long_name, r.route_short_name, r.route_id 
		FROM routes r, trips t, stops s, stop_times st 
		WHERE ' . $select1 . ' AND r.route_id = t.route_id AND t.trip_id = st.trip_id AND s.stop_id = st.stop_id
		ORDER BY r.route_long_name , r.route_short_name'; ?>
		<?php $sql = $db->query($sql0); ?>
		<?php foreach ($sql as $row) : ?>
			<?php if ($row["route_id"] === $_SESSION['route_id']) {
				$sel = "";
			} else {
				$sel = "";
			} ?>
			<option value="<?php echo ($row["route_id"]) . '" ' . $sel; ?>><?php echo $row["route_long_name"] . " >>> " . $row["route_short_name"]; ?></option>
		<?php endforeach; ?>
	</select>
	<button type=" submit" name="button" value="bt_route_id">route</button>
</form>

<!-- 停留所 -->
<form method="GET">
	<select multiple name="stop_id[]" style="height:190; width:600;">
		<option value=""></option>
		<?php $sql0 = 'SELECT  DISTINCT s.stop_id, s.stop_name, st.stop_headsign 
		FROM routes r, trips t, stops s, stop_times st 
		WHERE ' . $select2 . ' AND r.route_id = t.route_id AND t.trip_id = st.trip_id AND st.stop_id = s.stop_id 
		ORDER BY s.stop_id'; ?>
		<?php $sql = $db->query($sql0); ?>
		<?php foreach ($sql as $row) : ?>
			<?php if ($row["stop_id"] === $_SESSION['stop_id']) {
				$sel = "";
			} else {
				$sel = "";
			} ?>
			<option value="<?php echo ($row["stop_id"]) . '" ' . $sel; ?>><?php echo $row["stop_name"] . '発 - 行先' . $row["stop_headsign"]; ?></option>
		<?php endforeach; ?>
	</select>
	<button type=" submit" name="button" value="bt_stop_id">stop</button>
</form>

<!-- 曜日 -->
<form method="GET">
	<select multiple name="service_id[]" style="height:90; width:600;">
		<option value=""></option>
		<?php $sql0 = 'SELECT  DISTINCT t.service_id 
		FROM routes r, trips t, stops s, stop_times st 
		WHERE ' . $select3 . ' AND r.route_id = t.route_id AND t.trip_id = st.trip_id AND st.stop_id = s.stop_id 
		ORDER BY t.service_id'; ?>
		<?php $sql = $db->query($sql0); ?>
		<?php foreach ($sql as $row) : ?>
			<?php if ($row["service_id"] === $_SESSION['service_id']) {
				$sel = "";
			} else {
				$sel = "";
			} ?>
			<option value="<?php echo ($row["service_id"]) . '" ' . $sel; ?>><?php echo $row["service_id"]; ?></option>
		<?php endforeach; ?>
	</select>
	<button type=" submit" name="button" value="bt_service_id">service</button>
</form>

<!-- バス -->
<form method="GET">
	<select multiple name="trip_id[]" style="height:50; width:600;">
		<option value=""></option>
		<?php $sql0 = 'SELECT  DISTINCT t.trip_id 
		FROM routes r, trips t, stops s, stop_times st 
		WHERE ' . $select . ' AND r.route_id = t.route_id AND t.trip_id = st.trip_id AND st.stop_id = s.stop_id 
		ORDER BY t.trip_id'; ?>
		<?php $sql = $db->query($sql0); ?>
		<?php foreach ($sql as $row) : ?>
			<?php if ($row["trip_id"] === $_SESSION['trip_id']) {
				$sel = "";
			} else {
				$sel = "";
			} ?>
			<option value="<?php echo $row['trip_id'] . '" ' . $sel; ?>><?php echo $row['trip_id']; ?></option>
		<?php endforeach; ?>
	</select>
	<button type=" submit" name="button" value="bt_trip_id">バス</button>
</form>

<?php
//どれか一つを選択したら、バス情報をリスト表示する
// var_dump ($_SESSION['route_id']);echo '<br>';
// var_dump ($_SESSION['stop_id']);echo '<br>';
// var_dump ($_SESSION['service_id']);echo '<br>';
// var_dump ($route_id);echo '<br>';
// var_dump ($stop_id);echo '<br>';
// var_dump ($service_id);echo '<br>';
if ($_SESSION['route_id'] !== [] || $_SESSION['stop_id'] !== [] || $_SESSION['service_id'] !== []) {
	// $sql1 = 'SELECT st.trip_id, MIN(st.departure_time) FROM stop_times st GROUP BY t.trip_id ORDER BY  MIN(st.departure_time) ASC';
	// $sql11 = $db->prepare($sql1);
	// $sql11->execute();
	// foreach ($sql11 as $earlybus) {
	// 	echo $earlybus[0].' -- '.$earlybus[1]; echo "<br>";
	// }

	$sql0 = ' SELECT t.trip_id
	FROM routes r, routes_jp rj, trips t, stop_times st, stops s, stops_direction sd 
	WHERE ' . $select . ' AND r.route_id = rj.route_id AND r.route_id = t.route_id AND t.trip_id = st.trip_id 
	AND st.stop_id = s.stop_id AND st.stop_id = sd.stop_id 
	GROUP BY t.trip_id ORDER BY  MIN(st.departure_time) ASC';
	// echo $sql0;
	$sql = $db->prepare($sql0);
	$sql->execute();

	echo '<table>';
	echo '<tr>';
	echo '<th style="text-align: left"> ルート名 </th><th style="text-align: left"> バス停 </th><th style="text-align: left">方向</th><th style="text-align: left"> 出発時間 </th><th style="text-align: left"> バス停順 </th><th style="text-align: left"> 終点</th><th style="text-align: left"> 曜日 </th><th style="text-align: left"> バス </th>';
	echo '</tr>';

	foreach ($sql as $earlybus) {
		$trip = $earlybus[0];
		$sql0 = ' SELECT  r.route_id, r.route_short_name, r.route_long_name, r.agency_id, t.service_id, 
		rj.origin_stop, rj.destination_stop, rj.jp_parent_route_id, t.trip_id, t.trip_headsign, st.arrival_time, 
		st.departure_time, st.stop_sequence, st.stop_headsign, s.stop_id, s.stop_name, sd.direction 
		FROM routes r, routes_jp rj, trips t, stop_times st, stops s, stops_direction sd 
		WHERE ' . $select . ' AND r.route_id = rj.route_id AND r.route_id = t.route_id AND t.trip_id = st.trip_id 
		AND t.trip_id = ? AND st.stop_id = s.stop_id AND st.stop_id = sd.stop_id
		ORDER BY st.departure_time';
		$sql10 = $db->prepare($sql0);
		$sql10->bindParam(1, $trip, PDO::PARAM_STR);
		$sql10->execute();

		foreach ($sql10 as $row) {
			echo '<tr>';

			echo '<td style="text-align: left">' . $row["route_long_name"] . " >>> " . $row["route_short_name"] . '</td>';	//route名
			echo '<td style="text-align: left">' . $row[('stop_name')] . '</td>';	//バス停
			echo '<td style="text-align: left">' . $row[('direction')] . '</td>';	//方向
			echo '<td style="text-align: left">' . $row[('departure_time')] . '</td>';	//出発時刻
			echo '<td style="text-align: center">' . $row[('stop_sequence')] . '</td>';	//バス停順
			echo '<td style="text-align: left">' . $row[('stop_headsign')] . '</td>';	//終点
			echo '<td style="text-align: left">' . $row[('service_id')] . '</td>';	//曜日
			echo '<td style="text-align: left"><a href="index.php?trip_id=' . $row[('trip_id')] . '">' . $row[('trip_id')] . '</a></td>';	//バス
			echo '</tr>';
		}
	}
	echo '</table>';
} else {
	echo "どれか一つ選択してください。";
}
?>