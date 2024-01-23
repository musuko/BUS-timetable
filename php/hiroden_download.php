<?php
// zipダウンロード法その1
// $zipfile = file_get_contents("https://ajt-mobusta-gtfs.mcapps.jp/static/8/current_data.zip");
// file_put_contents("mini/current_data.zip", $zipfile);
// zipダウンロード法その2
// $src = fopen("https://ajt-mobusta-gtfs.mcapps.jp/static/8/current_data.zip", "rb");
// $dst = fopen("mini/current_data.zip", "wb");
// stream_copy_to_stream($src, $dst);

//フォルダ内の清掃
foreach ( glob('../mini/*') as $file ) {
  unlink($file);
}
// zipダウンロード法その3
$ch = curl_init();
curl_setopt_array($ch, [
CURLOPT_URL => "https://ajt-mobusta-gtfs.mcapps.jp/static/8/current_data.zip",
CURLOPT_FILE => fopen("../mini/current_data.zip", "wb"),
]);
curl_exec($ch);

// zip解凍
// php.iniにて「extension=zip」を有効する必要があります。
$file = "../mini/current_data.zip";
 
// 圧縮・解凍するためのオブジェクト生成
$zip = new ZipArchive();
 //ファイルを生成する準備
$result = $zip->open($file);
if($result === true)
{
  $zip->extractTo('../mini/');
  //ファイルの生成
  $zip->close();
}
?>
