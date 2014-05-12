<?php
// session_start();
require_once 'function.php';
require_once 'database.php';


$post_data = $_POST;
// var_dump($post_data);
// var_dump($_SESSION);
// require_once 'validate.php';

// $form_validate = formValidate($form_data);
// var_dump($form_validate);

// var_dump($form_data);
//ワンタイムトークンの生成
// $token = getToken($key = '');


//DB接続
$connect_db = connectDB();

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);

//フォームのデータ整形
$form_data = formData($post_data, $make_calendar);

//フォーム、バリデート
$form_validate = formValidate($form_data);
// var_dump($form_data);
//フォームデータのエスケープ
$escape_formdata = escapeFormdata($connect_db, $form_data);

//ワンタイムトークン生成
$get_token = getToken();

//ワンタイムトークンチェックする
$check_token = checkToken($form_data['token']);
var_dump($check_token);
//SQL文の生成
$sql_create = sqlCreate($escape_formdata, $check_token);
// var_dump($sql_create);
//INSERT UPDATEの実行
if (isset($sql_create['sql'])) {
    $insert_update =  sqlResult($escape_formdata, $connect_db, $sql_create);
    $insert_update['insert_or_update'];
    header('Location: http://kensyu.aucfan.com/');
    return;
}



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
    setcookie('schedule_id', $schedule_id);
} else {
    setcookie("get_parameter", "?year=$year&month=$month&day=$day", time()+10);
}

//エラーメッセージの受け取り
$error_hour = $_COOKIE['error_hour'];
$error_ymd = $_COOKIE['ymd'];
$error_schedule_title = $_COOKIE['schedule_title'];
$error_schedule_detail = $_COOKIE['schedule_detail'];
$error_date =  $_COOKIE['error_compare_date'];
$date_error = $_COOKIE['date_error'];//無効な日付

$sql_result = sqlResult($form_data, $connect_db, $sql_create);
$schedule_sql = $sql_result['schedules'];



//新規登録のとき
//if (!isset($schedule_id)) {
    $year = $_GET['year'];
    $month = $_GET['month'];
    $day = $_GET['day'];
    $end_year = $year;
    $end_month = $month;
    $end_day = $day;
/*} else {//編集のとき
    $year = $schedule_year;
    $month = $schedule_month;
    $day = $schedule_day;
    $end_year = $end_schedule_year;
    $end_month = $end_schedule_month;
    $end_day = $end_schedule_day;
}*/

setcookie('error_year', $year, time()+20000);
setcookie('error_month', $month, time()+20000);
setcookie('error_day', $day, time()+20000);
setcookie('error_id', $schedule_id, time()+20000);



/*
*コンボボックス
*/
$ym = array();
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
<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<table>
    <tr>
        <th>開始<br />※必須</th>
        <td>
            <select name="start_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo h($year.'-'.$month);?>"><?php echo h($ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select name="start_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option id="select_start_day" value="<?php echo h($i);?>" <?php if ($i == $day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error_ymd']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['date_error']);?></span><br />
            <select name="start_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="start_hour" value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select name="start_min">
                <option class="start_min" value="00">00分</option>
                <option class="start_min" value="30">30分</option>
            </select>
            <br /><span class="error"><?php echo h($_SESSION['error_hour']);?></span>
        </td>
    </tr>
    <tr>
        <th>終了<br />※必須</th>
        <td>
            <select name="end_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo h($end_year.'-'.$end_month);?>"><?php echo h($ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select name="end_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option id="select_end_day" value="<?php echo h($i);?>" <?php if ($i == $day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error_ymd']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['date_error']);?></span><br />
            <select name="end_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="end_hour" value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select name="end_min">
                <option class="end_min" value="00">00分</option>
                <option class="end_min" value="30">30分</option>
            </select>
            <br /><span class="error"><?php echo h($_SESSION['error_hour']);?></span>
        </td>
    </tr>
    <tr>
        <th>タイトル<br />※必須</th>
        <td>
            <input type="text" id="schedule_title" name="schedule_title" value="<?php echo h($schedule_sql[$year][$month][$day][$schedule_id]['title']);?>" /><br />
            <span class="error"><?php echo h($_SESSION['error_schedule_title']);?></span>
        </td>
    </tr>
    <tr>
        <th>詳細<br />※必須</th>
        <td>
            <textarea id="schedule_detail" name="schedule_detail" rows=5 cols=40><?php echo h($schedule_sql[$year][$month][$day][$schedule_id]['detail']);?></textarea>
            <br /><span class="error"><?php echo h($_SESSION['error_schedule_detail']);?></span>
        </td>
    </tr>

    <?php if(!empty($schedule_id)):?>
        <input type="hidden" name="schedule_id" value="<?php echo h($schedule_id);?>" />
        <input type="hidden" name="token" value="<?php echo h($token);?>" />
        <input type="submit" name="update" value="更新" />
    <?php else:?>
        <input type="hidden" name="token" value="<?php echo h($token);?>" />
        <input type="submit" name="insert" value="登録" />
    <?php endif;?>

</table>
</form>
<form method="post" action="http://kensyu.aucfan.com/">
    <input type="hidden" name="token" value="<?php echo h($token);?>" />
    <input type="hidden" id="delete" name="delete" value="delete" />
    <input type="hidden"  name="schedule_id" value="<?php echo h($schedule_id);?>" />
    <input type="submit" value="削除" />
</form>

</div><!-- schedule_form -->
</body>
</html>