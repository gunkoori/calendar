<?php
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$date = sprintf('%02d', $day);
// print_r($_COOKIE['schedule_title']);
/*
*DB接続
*/
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'calendar';

// MySQL に接続し、データベースを選択
$db = mysqli_connect($host, $user, $password, $database);

// 接続状況をチェック
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}

$schedule_sql =<<<END
    SELECT
         start_date, schedule_title, schedule_detail
     FROM
         cal_schedules
END;


//SQL実行
if ($result = mysqli_query($db, $schedule_sql)) {
    while ($row = mysqli_fetch_row($result)) {
        $explode_db_date = explode(' ', $row[0]);
        $schedule_list[$explode_db_date[0]] = $row[1];
        $schedule_list_detail[$explode_db_date[0]] = $row[2];
    }
    mysqli_free_result($result);
}
mysqli_close($db);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<link href="calendar.css" rel="stylesheet">
</head>
<body>
<h3>スケジュール登録</h3>
<div id="schedule_form">
    <form method="post" action="http://kensyu.aucfan.com/">
        <input type="text" id="start_day" name="start_day" value="<?php echo $year.'-'.$month.'-'.$day?>" />〜
        <input type="text" id="end_day" name="end_day" value="<?php echo $year.'-'.$month.'-'.$day?>" /><br />
        タイトル：<input type="text" id="schedule_title" name="schedule_title" value="<?php echo $schedule_list[$year.'-'.$month.'-'.$date];?>" /><br />
        詳細：<input type="text" id="schedule_detail" name="schedule_detail" value="<?php echo $schedule_list_detail[$year.'-'.$month.'-'.$date];?>"/><br />
        <?php if(isset($schedule_list[$year.'-'.$month.'-'.$date])):?>
            <?php setcookie('update', 'update', time()+10);?>
            <input type="submit" value="更新する" />
        <?php else:?>
            <input type="submit" value="登録する" />
        <?php endif;?>
    </form>
</div>
</body>
</html>