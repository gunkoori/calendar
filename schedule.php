<?php
require_once 'function.php';
require_once 'database.php';
require_once 'unset_session.php';

//DB接続
$connect_db = connectDB();

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);

//フォームのデータ整形
$form_data = formData($make_calendar);


//POSTされたデータをSESSIONに保存
$_SESSION['form_data'] = $form_data;

//フォーム、バリデート
$is_form_valid = formValidate();

//フォームデータのエスケープ
$escape_formdata = escapeFormdata($connect_db, $form_data);

//ワンタイムトークン生成
$get_token = getToken();

//ワンタイムトークンチェックする
$check_token = checkToken($form_data['token']);

//SQL文の生成
//バリデート通ったときと、削除のときはなにかをPOSTするわけではないので、$escape_formdata['delete']があった時点で削除SQL生成
if ($is_form_valid === true  /*isset($escape_formdata['delete'])*/) {
    $sql_create = sqlCreate($escape_formdata, $check_token);
}
//INSERTまたはUPDATEのSQLがある場合、実行
if (empty($_SESSION['error']['error_schedule_title']) && empty($_SESSION['error']['error_schedule_detail']) && isset($sql_create['sql'])) {
    $insert_update =  sqlResult($escape_formdata, $connect_db, $sql_create);
    $insert_update['insert_or_update'];
    unset($_SESSION['error']['keep_title']);
    unset($_SESSION['error']['keep_detail']);
    header('Location: http://kensyu.aucfan.com/');
    return;
}

//DELETEのSQLがある場合、deleted_atにNOW()を入れる
if (isset($sql_create['delete'])) {
    $delete = sqlResult($escape_formdata, $connect_db, $sql_create);
    $delete['delete'];
    header('Location: http://kensyu.aucfan.com/');
    return;
}


$year = !empty($_GET['year']) ? $_GET['year'] : $form_data['year'];
$month = !empty($_GET['month']) ? $_GET['month'] : $form_data['month'];
$formatted_month = sprintf('%1d', $month);
$day = !empty($_GET['day']) ? $_GET['day'] : $form_data['day'];

$end_year = !empty($form_data['end_year']) ? $form_data['end_year'] : $year;
$end_month = !empty($form_data['end_month']) ? $form_data['end_month'] : $month;
$end_day = !empty($form_data['end_day']) ? $form_data['end_day'] : $day;

$schedule_id = !empty($_GET['id']) ? $_GET['id'] : $form_data['id'];
$date = sprintf('%02d', $day);

$start_time = !empty($form_data['start_time']) ? $form_data['start_time'] : time('H');
$end_time = !empty($form_data['end_time']) ? $form_data['end_time'] : $start_time;

$sql_result = sqlResult($form_data, $connect_db, $sql_create);
$schedule_sql = $sql_result['schedules'];

/*
*コンボボックス
*/
$ym = array();
$ymi = array();
$end_ym = array();
$end_ymi = array();
for ($i=-12; $i<=12; $i++) {
    list($years, $months, $days) = explode('-', date('Y-n-t', mktime(0, 0, 0, $month+($i), 1, intval($year)) ));
    list($end_years, $end_months, $end_days) = explode('-', date('Y-n-t', mktime(0, 0, 0, $end_month+($i), 1, intval($end_year)) ));
    $ym[] = $years.'年'.$months.'月';
    $ymi[] = $years.'-'.$months;
    $end_ym[] = $end_years.'年'.$end_months.'月';
    $end_ymi[] = $end_years.'-'.$end_months;
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
<form method="post" action="<?php echo h($_SERVER['PHP_SELF']);?>">
<input type="hidden" name="year" value="<?php echo $year ?>">
<input type="hidden" name="month" value="<?php echo $month ?>">
<input type="hidden" name="day" value="<?php echo $day ?>">
<table>
    <tr>
        <th>開始<br />※必須</th>
        <td>
            <select name="start_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo h($ymi[$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select name="start_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option id="select_start_day" value="<?php echo h($i);?>" <?php if ($i == $day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error']['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error']['error_date']);?></span><br />
            <select name="start_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="start_hour" value="<?php echo h($i);?>" <?php if ($i == $start_time):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select name="start_min">
                <option class="start_min" value="00">00分</option>
                <option class="start_min" value="30">30分</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>終了<br />※必須</th>
        <td>
            <select name="end_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option id="select_year_month" value="<?php echo h($end_ymi[$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($end_ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select name="end_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option id="select_end_day" value="<?php echo h($i);?>" <?php if ($i == $end_day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error']['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error']['error_date']);?></span><br />
            <select name="end_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="end_hour" value="<?php echo h($i);?>" <?php if ($i == $end_time):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select name="end_min">
                <option class="end_min" value="00">00分</option>
                <option class="end_min" value="30">30分</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>タイトル<br />※必須</th>
        <td>
            <input type="text" id="schedule_title" name="schedule_title"  placeholder="タイトルを入力してください" value="<?php if (isset($_SESSION['error']['keep_title']) && !isset($schedule_id)) { echo $_SESSION['error']['keep_title'][$year][$formatted_month][$day];} else { echo h($schedule_sql[$year][$month][$day][$schedule_id]['title']);}?>" /><br />
            <span class="error"><?php echo h($_SESSION['error']['error_schedule_title']);?></span>
        </td>
    </tr>
    <tr>
        <th>詳細<br />※必須</th>
        <td>
            <textarea id="schedule_detail" name="schedule_detail"  placeholder="詳細を入力してください"　rows=5 cols=40><?php if (isset($_SESSION['error']['keep_detail']) && !isset($schedule_id)) { echo $_SESSION['error']['keep_detail'][$year][$formatted_month][$day]; } else { echo h($schedule_sql[$year][$month][$day][$schedule_id]['detail']); }?></textarea>
            <br /><span class="error"><?php echo h($_SESSION['error']['error_schedule_detail']);?></span>
        </td>
    </tr>

    <?php if(!empty($schedule_id)):?>
        <input type="hidden" name="schedule_id" value="<?php echo h($schedule_id);?>" />
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <input type="submit" name="update" value="更新" />
    <?php else:?>
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <input type="submit" name="insert" value="登録" />
    <?php endif;?>

</table>
</form>
<form method="post" action="<?php echo h($_SERVER['PHP_SELF']);?>">
    <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
    <input type="hidden" id="delete" name="delete" value="delete" />
    <input type="hidden"  name="schedule_id" value="<?php echo h($schedule_id);?>" />
    <input type="submit" value="削除" />
</form>

</div><!-- schedule_form -->
</body>
</html>