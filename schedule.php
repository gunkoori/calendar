<?php
require_once 'function.php';
require_once 'database.php';
require_once 'unset_session.php';

//36文字のランダムな文字列生成
$str = randomStr(36);

//ワンタイムトークン生成　$_SESSION['key']に格納
$get_token = getToken($str);


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
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/form.js"></script>

</head>
<body>
<h3>スケジュール登録</h3>
<div id="schedule_form">
<form method="post" action="post.php">
<input type="hidden" name="year" value="<?php echo $year ?>">
<input type="hidden" name="month" value="<?php echo $month ?>">
<input type="hidden" name="day" value="<?php echo $day ?>">
<table id="regist_form">
    <tr>
        <th>開始<br />※必須</th>
        <td>
            <select id="start_ym" name="start_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option  value="<?php echo h($ymi[$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <select id="start_day" name="start_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option  value="<?php echo h($i);?>" <?php if ($i == $day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select><br />
            <span id="alert_start_date" class="error">正当な日付ではありません<br /></span>
            <span id="alert_error_date" class="error">開始日が終了日よりも遅く設定されています<br /></span>
            <select id="start_hour" name="start_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option  value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select id="start_min" name="start_min">
                <option class="start_min" value="00">00分</option>
                <option class="start_min" value="30">30分</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>終了<br />※必須</th>
        <td>
            <select id="end_ym" name="end_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option  value="<?php echo h($end_ymi[$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($end_ym[$i]);?></option>
            <?php endfor; ?>
            </select>
            <select id="end_day" name="end_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option  value="<?php echo h($i);?>" <?php if ($i == $end_day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select><br />
            <span id="alert_end_date" class="error">正当な日付ではありません<br /></span>
            <select id="end_hour" name="end_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option  value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
            <?php endfor; ?>
            </select>
            <select id="end_min" name="end_min">
                <option class="end_min" value="00">00分</option>
                <option class="end_min" value="30">30分</option>
            </select>
        </td>
    </tr>
    <tr>
        <th>タイトル<br />※必須</th>
        <td>
            <input type="text" id="schedule_title" name="schedule_title" placeholder="タイトルを入力してください" value="<?php if (isset($_SESSION['error']['keep_title']) && !isset($schedule_id)) { echo $_SESSION['error']['keep_title'][$year][$formatted_month][$day];} else { echo h($schedule_sql[$year][$month][$day][$schedule_id]['title']);}?>" /><br />
            <div id="alert_schedule_title" class="error">タイトルを入力してください</div>
        </td>
    </tr>
    <tr>
        <th>詳細<br />※必須</th>
        <td>
            <textarea id="schedule_detail" name="schedule_detail"  placeholder="詳細を入力してください"　rows=5 cols=40><?php if (isset($_SESSION['error']['keep_detail']) && !isset($schedule_id)) { echo $_SESSION['error']['keep_detail'][$year][$formatted_month][$day]; } else { echo h($schedule_sql[$year][$month][$day][$schedule_id]['detail']); }?></textarea>
            <br /><span id="alert_schedule_detail" class="error" class="error">詳細を入力してください</span>
        </td>
    </tr>
    <tr><td colspan="2" class="center">
    <?php if(!empty($schedule_id)):?>
        <input type="hidden" name="schedule_id" value="<?php echo h($schedule_id);?>" />
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <input type="submit" id="btn-update" class="btn" name="update" value="更新" />

        <input type="hidden" id="delete" name="delete" value="delete" />
        <span id="btn-delete"><input type="submit" class="btn" value="削除" /></span>
    <?php else:?>
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <span id="btn-regist"><input type="submit" class="btn" name="insert" value="登録" /></span>
    <?php endif;?>
    </td></tr>
</table>
</form>
</div><!-- schedule_form -->
</body>
</html>