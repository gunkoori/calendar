<?php
$year = isset($_GET['year']) ? $_GET['year']:'';
$month = '';
$day = '';
$update ='';
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];
$schedule_id = $_GET['id'];
$date = sprintf('%02d', $day);

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
         start_date, end_date, schedule_title, schedule_detail
     FROM
         cal_schedules
     WHERE
         schedule_id="$schedule_id"

     AND
         deleted_at
     IS
         null

END;


//SQL実行
if ($result = mysqli_query($db, $schedule_sql)) {
    while ($row = mysqli_fetch_row($result)) {
        $start_date = explode(' ', $row[0]);//開始日
        $end_date = explode(' ', $row[1]);//終了日
        $schedule_end_date[$start_date[0]] = explode('-', $end_date[0]);//[2014-04-01] => Array([0] => 2014,[1] => 05,[2] => 01)
        $schedule_list[$start_date[0]] = $row[2];
        $schedule_list_detail[$start_date[0]] = $row[3];
    }

    mysqli_free_result($result);
}
mysqli_close($db);

//終了日
$end_year = $schedule_end_date[$year.'-'.$month.'-'.$date][0];
$end_month = $schedule_end_date[$year.'-'.$month.'-'.$date][1];
$end_day = $schedule_end_date[$year.'-'.$month.'-'.$date][2];

//
if (!isset($schedule_id)) {
    $end_year = $year;
    $end_month = $month;
    $end_day = $day;
    $schedule_list[$year.'-'.$month.'-'.$date] = '';
    $schedule_list_detail[$year.'-'.$month.'-'.$date] = '';
}
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
        <span>
            <input type="text" id="start_year" name="start_year" value="<?php echo $year;?>" />年
            <input type="text" id="start_month" name="start_month" value="<?php echo $month;?>" />月
            <input type="text" id="start_day" name="start_day" value="<?php echo $day;?>" />日〜
            <input type="text" id="end_year" name="end_year" value="<?php echo $end_year;?>" />年
            <input type="text" id="end_month" name="end_month" value="<?php echo $end_month;?>" />月
            <input type="text" id="end_day" name="end_day" value="<?php echo $end_day;?>" />日
        </span>
        <br />
        <span>
            <input type="text" id="start_hour" name="start_hour" value="<?php echo date('G');?>" />時
            <input type="text" id="start_min" name="start_min" value="<?php echo date('i');?>" />分〜
            <input type="text" id="end_hour" name="end_hour" value="<?php echo date('G');?>" />時
            <input type="text" id="end_min" name="end_min" value="<?php echo date('i');?>" />分
        </span>
        <br />
        タイトル：<input type="text" id="schedule_title" name="schedule_title" value="<?php echo $schedule_list[$year.'-'.$month.'-'.$date];?>" /><br />
        詳細：<input type="text" id="schedule_detail" name="schedule_detail" value="<?php echo $schedule_list_detail[$year.'-'.$month.'-'.$date];?>"/><br />
        <input type="hidden" id="schedule_id" name="schedule_id" value="<?php echo $schedule_id;?>" />
        <?php if(!empty($schedule_id)):?>
            <?php setcookie('update', 'update', time()+10);?>
            <input type="submit" value="更新する" />
        <?php else:?>
            <input type="submit" value="登録する" />
        <?php endif;?>
    </form>
</div>
</body>
</html>