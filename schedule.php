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

if (isset($schedule_id)) {
    setcookie("get_parameter", "?year=".$year."&month=".$month."&day=".$day."&id=".$schedule_id, time()+10);
} else {
    setcookie("get_parameter", "?year=$year&month=$month&day=$day", time()+10);
}

$error_hour = $_COOKIE['error_hour'];
$error_ymd = $_COOKIE['ymd'];
$error_schedule_title = $_COOKIE['schedule_title'];
$error_schedule_detail = $_COOKIE['schedule_detail'];

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
         schedule_id, start_date, end_date, schedule_title, schedule_detail
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
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        list($schedule_year, $schedule_month, $schedule_day) = explode('-', date('Y-m-j',strtotime($row['start_date'])));
        list($end_schedule_year, $end_schedule_month, $end_schedule_day) = explode('-', date('Y-m-j',strtotime($row['end_date'])));
        $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
        $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
        $schedules[$end_schedule_year][$end_schedule_month][$end_schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
        $schedules[$end_schedule_year][$end_schedule_month][$end_schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
    }
    mysqli_free_result($result);
}
mysqli_close($db);

//新規登録のとき
if (!isset($schedule_id)) {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $day = $_GET['day'];
    $end_year = $year;
    $end_month = $month;
    $end_day = $day;
} else {//編集のとき
    $year = $schedule_year;
    $month = $schedule_month;
    $day = $schedule_day;
    $end_year = $end_schedule_year;
    $end_month = $end_schedule_month;
    $end_day = $end_schedule_day;
}

setcookie('error_year', $year, time()+10);
setcookie('error_month', $month, time()+10);
setcookie('error_day', $day, time()+10);
setcookie('error_id', $schedule_id, time()+10);


/*
*コンボボックス
*/
for ($i=-12; $i<=12; $i++) {
    list($years, $months, $days) = explode('-', date('Y-n-t', mktime(0, 0, 0, $month+($i), 1, intval($year)) ));
    $ym[] = $years.'年'.$months.'月';
    // $d[] = $days;
    $combo[$years][$months]=$days;

    for ($j=1; $j<=$days; $j++) {
            $combos[$years][$months] = $j;
    }
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

<table>
    <tr>
        <th>開始</th>
        <td>
            <select name="start_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo $year.'-'.$month;?>"><?php echo $ym[$i]?></option>
            <?php endfor; ?>
            </select>
            <input type="text" id="start_day" name="start_day" value="<?php echo $day;?>" />日<br />
            <?php echo $error_ymd;?><br />
            <input type="text" id="start_hour" name="start_hour" value="<?php echo date('G');?>" />時
            <input type="text" id="start_min" name="start_min" value="<?php echo date('i');?>" />分
            <br /><?php echo $error_hour;?>
        </td>
    </tr>
    <tr>
        <th>終了</th>
        <td>
            <select name="end_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo $end_year.'-'.$end_month;?>"><?php echo $ym[$i]?></option>
            <?php endfor; ?>
            </select>
            <input type="text" id="end_day" name="end_day" value="<?php echo $end_day;?>" />日<br />
            <?php echo $error_ymd;?><br />
            <input type="text" id="end_hour" name="end_hour" value="<?php echo date('G');?>" />時
            <input type="text" id="end_min" name="end_min" value="<?php echo date('i');?>" />分
            <br /><?php echo $error_hour;?>
        </td>
    </tr>
    <tr>
        <th>タイトル</th>
        <td>
            <input type="text" id="schedule_title" name="schedule_title" value="<?php echo $schedules[$schedule_year][$schedule_month][$schedule_day][$schedule_id]['title'];?>" /><br />
            <?php echo $error_schedule_title;?>
        </td>
    </tr>
    <tr>
        <th>詳細</th>
        <td>
            <textarea id="schedule_detail" name="schedule_detail" rows=5 cols=40><?php echo $schedules[$schedule_year][$schedule_month][$schedule_day][$schedule_id]['detail'];?></textarea>
            <br /><?php echo $error_schedule_detail;?>
        </td>
    </tr>

    <?php if(!empty($schedule_id)):?>
            <input type="hidden" name="schedule_id" value="<?php echo $schedule_id;?>" />

            <input type="submit" name="update" value="更新" />
    <?php else:?>
            <input type="submit" name="insert" value="登録" />
    <?php endif;?>

</table>
</form>
<form method="post" action="http://kensyu.aucfan.com/">
    <input type="hidden" id="delete" name="delete" value="delete" />
    <input type="hidden"  name="schedule_id" value="<?php echo $schedule_id;?>" />
    <input type="submit" value="削除" />
</form>

</div><!-- schedule_form -->
</body>
</html>