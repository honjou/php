<?php
/**
 * 素のPHPでDBのレコードを表示する（mysqli）
 *
 * @author Honjo Masanori
 */

//****************************************
//     DB接続
//****************************************

// mysqliクラスのオブジェクトを作成
if($_SERVER['HTTP_HOST']=='localhost'){
    // ローカル環境
    // 引数は各自の環境にあわせて入れてください
    $mysqli = new mysqli('localhost', 'root', '', 'practice_db');
}else{
    // 本番環境
    // 引数は各自の環境にあわせて入れてください
    $mysqli = new mysqli('host', 'username', 'passwd', 'dbname');
}

if($mysqli->connect_errno) {
    echo $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
    exit();
}else{
    // 文字コードを設定する
    // これがないと抽出できない！
    $mysqli->set_charset('utf8');
}

//****************************************
//     ページャー: 変数
//****************************************

//最新の20件を表示
$linenum = "20";

//ページNo
$pageNum = 0;

//最大行数
$maxRows = 20;

//サーバのホスト名取得
$currentPage = $_SERVER["PHP_SELF"];


//****************************************
//     2ページ目以降の処理
//****************************************

if (isset($_GET['pageNum'])){
    //Startページ数 計算
    $pageNum = $_GET['pageNum'];
    //Endページ数 計算
    $EndRow = $linenum * ($_GET['pageNum']+1);
}else{
    //Endページ数 計算
    $EndRow = $linenum;
}

//SQLリミット計算(スタート)
$startRow = $pageNum * $maxRows;

//SQL
$query = "SELECT * FROM profiles ORDER BY id DESC";

//LIMIT句（$startRowから$maxRowsまで）
$query_limit = sprintf("%s LIMIT %d, %d", $query, $startRow, $maxRows);

//クエリー送信
$result = $mysqli->query($query_limit);

//結果における行の数を得る
$totalRows = $result->num_rows;


//****************************************
//     ページ数 計算
//****************************************

//Endページ数 計算(例外処理)
if($result->num_rows != $linenum){
    $EndRow = $result->num_rows + $startRow;
}

if (isset($_GET['totalRows'])){
    //既に計算済み
    $totalRows = $_GET['totalRows'];
} else {
    //LMITなしでクエリー送信
    $all = $mysqli->query($query);
    //結果セットから行の数を取得
    $totalRows = $all->num_rows;
}

//引数で指定した数値から、次に大きい整数を返す
$totalPages = ceil($totalRows/$maxRows)-1;

$num_hit = $totalPages;

$queryString = "";


//****************************************
//     URLパラメータ(GET)を取得
//****************************************

if (!empty($_SERVER['QUERY_STRING'])){
    //"&"で分割
    $params = explode("&", $_SERVER['QUERY_STRING']);
    $newParams = array();

    //foreach( 配列 as $value )
    foreach ($params as $param){
        //stristr(処理対象の文字列,検索する文字列)
        if(stristr($param, "pageNum") == false && stristr($param, "totalRows") == false){
            array_push($newParams, $param);
        }
    }
    if (count($newParams) != 0) { $queryString = "&" . implode("&", $newParams); }
}

$queryString = sprintf("&totalRows=%d%s", $totalRows, $queryString);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>素のPHPでDBのレコードを表示する（mysqli）</title>

    <!--Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!--Font Awesome5-->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">

    <!--自作CSS -->
    <style type="text/css">
        <!--
        /*ここに調整CSS記述*/

        -->
    </style>
</head>
<body>
<div class="container pt-5">

    <h2 class="mb-5">素のPHPでDBのレコードを表示する（mysqli）</h2>

    <!--ページャー Start-->
    <?php if($totalRows > 0){//0件の場合非表示 ?>
        <table class="table">
            <tr>
                <td width="55%" align="left">
                    <strong class="font-size14 red"><?= $totalRows ?></strong>件中／<?= $startRow+1 ?>～<?= $EndRow ?> 件表示	</td>
                <td width="15%" align="right">
                    <?php if ($pageNum > 0) { ?>
                        <a href="<?php printf("%s?pageNum=%d%s", $currentPage, max(0, $pageNum - 1), $queryString) ;?>"> &lt;&lt; 前の<?= $linenum ;?>件へ</a>
                    <?php } ?>
                </td>
                <td width="15%" align="right">
                    <?php if ($pageNum > 0) { ?>
                        <a href="<?php printf("%s?pageNum=%d%s", $currentPage, 0, $queryString); ?>">最新の行にもどる</a>
                    <?php }  ?>
                </td>
                <td width="15%" align="right">
                    <?php if ($pageNum < $totalPages) { ?>
                        <a href="<?php printf("%s?pageNum=%d%s", $currentPage, min($totalPages, $pageNum + 1), $queryString); ?>" >次の <?= $linenum ;?>件へ &gt;&gt;</a>
                    <?php }  ?>
                </td>
            </tr>
        </table>
    <?php } ?>
    <!--ページャー End-->

    <table class="table">
        <thead class="thead-dark">
        <tr>
            <th>id</th>
            <th style="white-space: nowrap;">名前</th>
            <th>住所</th>
            <th style="white-space: nowrap;">生年月日</th>
            <th style="white-space: nowrap;">電話番号</th>
            <th>メッセージ</th>
        </tr>
        </thead>
        <tbody>
        <!--ループ処理-->
        <?php while ($row = $result->fetch_assoc()){ ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['address'] ?></td>
                <td><?= $row['birthdate'] ?></td>
                <td><?= $row['tel'] ?></td>
                <td><?= $row['msg'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

</div><!-- /container -->

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" ></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" ></script>
</body>
</html>