<?php   //GTFS
//dbbusというデータベースは、あらかじめ作成しておく。
//バス協会のzipデータをダウンロードし、解凍する。
//解凍したファイルを一つずつ読み込む。
//BOM codeを削除する。
//バスデータのカラム名に、型を追加する。
//https://www.bus-kyo.or.jp/gtfs-open-data
//https://developers.google.com/transit/gtfs/reference#field_types
//https://developers.google.com/transit/gtfs/reference#field_definitions

//Maximum execution time of xx seconds exceeded対策
set_time_limit(600);

require '../php/dbconnect.php';
//https://www.bus-kyo.or.jp/gtfs-open-data
//hirodenフォルダのデータをダウンロードする
require '../php/hiroden_download.php';
echo "サーバーからデータを読み込みました。";
echo "<br>";

// $array = ["mini/shapes.txt"];  //フォルダー内のファイル名を取得し配列に入れる ($array[0]="agency_jp.txt)
$data = file("../key/primary.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); // $data: テーブル名と主キーカラムの配列。
$cntmax = 300000;    //読み込み上限行数

$tablename = "";
$filename = "../mini/shapes.txt";
$tablename = pathinfo($filename, PATHINFO_FILENAME);
// $len = strlen($filename);   //$filename (mini/agency_jp.txt など)からテーブル名(agency_jp など)を作成 $tablename
// $tablename = substr($filename, 5, $len - 4 - 5); // -4は .txtの分。-5は、0から数えて5文字目から表示の分。mini/ を取り除く。

$info = file_get_contents($filename);      //データファイル(.txt)をテキスト形式で読み込む
$cnt = substr_count($info, "\n");

if ($cnt < $cntmax) {       //行数が指定値より小さい場合、実行。
    $info = str_replace(array("\r\n", "\r", "\n"), "\n", $info);    //　\nに改行コードを統一しておく
    $info = trim($info);    //　最後の不要な改行コードを削除
    $bom = hex2bin('EFBBBF');   //BOM のコード　GTFSデータ先頭にこれが存在する
    $info = preg_replace("/^{$bom}/", '', $info);       //文字先頭のBOM codeを""に変換する
    // if ($filename === "mini/stops_direction.txt") {
    //     $info = str_replace('19370 3,"横川駅前,広島バスセンター方面"', '19370 3,"横川駅前、広島バスセンター方面"', $info); //例外措置。元データに不適切な, (カンマ)が入っているので、読点に変換する。
    // }
    // if ($filename === "mini/stops_mobustation.txt") {
    //     $info = str_replace(',,,3', '', $info); //例外措置。元データに不適切な繰り返し行が入っているので、削除する。
    // }
    // if ($filename === "mini/translations.txt") {
    //     require 'php/correct_translations.php';
    // }
    // if ($filename === "mini/translations_mobustation.txt") {
    //     require 'php/correct_translations_mobustation.php';
    // }
    // $info = htmlspecialchars($info, ENT_QUOTES, 'utf-8');
    // $info = trim($info);    //空白行を消す。UTF-8のカラム+データ　（文字列)    

    $info_array = explode("\n", $info);         //　\nで区切られたテキストを、配列に変換する。[0]はカラムの行の文字列。[1]以降は、データの行の文字列。
    $j = 0; //行数　0スタート
    foreach ($info_array as $value) {       // データファイル(.txt)の行ごとの配列を、行ごとに文字列で読み込む

        $info_array0 = explode(',', trim($value));     //　,で区切られた行の文字列を、配列に変換する

        $txt = '';
        foreach ($info_array0 as $value0) {     //　行方向の配列を、一つずつ読み込む
            if ($j === 0) {                     //先頭行の場合、(すなわちカラム行の場合)
                $txt .= $value0 . ' varchar(100),';      //カラム名に型のvarchar(100)を挿入する。route_id varchar(100)など。その後のint判定は省略してプログラムを短くした。
            } else {
                $txt .= "'" . $value0 . "',";    // 空データ含め全て、'' (""はデータで使用されているので避ける)で囲む
            }
            $info_array[$j] = $txt;     // 各データを$info_arrayに戻す
        }

        $len = strlen($info_array[$j]);
        $info_array[$j] = substr($info_array[$j], 0, $len - 1); //行末のカンマを削除
        if ($j < $cnt - 1) {    //最終行のデータでなければ
            $info_array[$j] = $info_array[$j] . "\n";     //データの行末に改行を追加する
        }

        $j++;
    }

    file_put_contents($filename, $info_array);   //編集した内容でデータを上書きする




    // データベースに、テーブルとカラムを作成。既に存在する場合、先に削除する。
    $sql0 = "DROP TABLE if exists " . $tablename;
    $sql = $db->prepare($sql0);
    $sql->execute();
    $sql0 = 'CREATE TABLE if not exists ' . $tablename . '(' . $info_array[0] . ')';
    $sql = $db->prepare($sql0);
    $sql->execute();

    // 各テーブルに主キーを設定する。各テーブルにデータを書き込む。
    // 主キー情報を読み込む。
    foreach ($data as  $value) {    //primary.txtから読み込んだ要素の中で、テーブル名($dataの先頭カラム)が一致する要素を探すため、foreachで回す。
        $keyname = explode(",", $value);    //テーブル名と主キーカラムをカンマで区切り、配列にする。
        if ($tablename === $keyname[0]) {   //テーブル名($tablename[0])が、primary.txtから読み込んだテーブル名と一致する場合、作業する
            $keyname = array_diff($keyname, [$keyname[0]]);   //配列の[0]を消す。（カラム名を消す)

            // 主キーに設定するカラムを$primarykeyとする。
            $primarykey = trim(implode(",", $keyname));
            // 主キーをテーブルに設定する。(まだ設定されていない場合)
            $sql0 = 'ALTER TABLE ' . $tablename . ' add primary key if not exists (' . $primarykey . ')';
            // echo $sql0;
            // echo "<br>";
            $sql = $db->prepare($sql0);
            $sql->execute();
        }
    }

    //ここが最も演算時間を要する。
    //データの行を、テーブルに書き込む。

    echo $tablename . ' は要素数が' . $cntmax . '未満(' . $cnt . ')のため、テーブルへの書き込みを実施します。';
    echo "<br>";
    $k = 0;
    foreach ($info_array as $row_data) {
        if ($k > 0) {   //0行目はカラム行でテーブルに書き込み済みのため、含めない。
            $sql0 = "INSERT INTO " . $tablename . " VALUES (" . trim($row_data) . ")";

            $sql = $db->prepare($sql0);
            $sql->execute();
        }
        $k++;
    }
} else {
    //データ数が多い場合は、テーブルへの書き込みをスキップする。
    echo '<p style="color:red;">' . $tablename . ' は要素数が' . $cntmax . '以上(' . $cnt . ')のため、テーブルへの書き込みをスキップします。</p>';
    echo "<br>";
}

echo "テーブルへの書き込みを終了しました。";
$pdo = null;
