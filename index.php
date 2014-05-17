<?php
require_once 'database.php';
require_once 'function.php';
require_once 'unset_session.php';

//年月日、時間の取得
$get_ymdh = getYmdh($year_of_ym, $month_of_ym);

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $year_of_ym);

//祝日
$holiday = getHoliday($last_month, $next_month);

//オークショントピック
$auc_topi = aucTopi();

//DB接続
$connect_db = connectDB();
if ($connect_db['return'] == false) {//接続状況の確認
    echo 'DB接続失敗';
}

//フォームのデータ整形
$form_data = formData($make_calendar);

//エスケープ
$escape_formdata = escapeFormdata($connect_db, $form_data);

//SQL文の生成
$sql_create = sqlCreate($escape_formdata, $check_token = true);

//SQL実行
$sql_result = sqlResult($escape_formdata, $connect_db, $sql_create);
$schedules_months = $sql_result['schedules_months'];

//SESSION初期化
$unset_session = unsetSession();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<link href="calendar.css" rel="stylesheet">
<script type="text/javascript" src="/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="/js/register.js"></script>
</head>
<body>
<div id="shadow"></div><!-- shadow -->
<div id="header">
<h3>郡カレンダー</h3>
<div id="prev"><a href="?ym=<?php echo h($last_month['year'].'-'.$last_month['month']);?>">前月</a></div>
<div id="this"><a href="/">今月</a></div>
<div id="next"><a href="?ym=<?php echo h($next_month['year'].'-'.$next_month['month']);?>">次月</a></div>
<form method="get" action="<?php $_SERVER['PHP_SELF'];?>">
    <select name="ym">
    <option>選択してください</option>
    <?php for ($i=0; $i<=24; $i++):?>
    <option id="select_year_month" value="<?php echo h($months[$i]);?>"><?php echo h($months[$i]);?></option>
    <?php endfor; ?>
    </select>
    <input type="submit" value="表示する">
</form>
</div><!--header-->

<!--
************ ポップアップ ************
 -->
<div class="popup">

<form method="post" id="popup_regist_form">
<input type="hidden" name="year" value="<?php echo $year ?>">
<input type="hidden" name="month" value="<?php echo $month ?>">
<input type="hidden" name="day" value="<?php echo $day ?>">
<table id="regist_form">
    <tr>
        <th>開始<br />※必須</th>
        <td>
            <select id="start_ym" name="start_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option value="<?php echo h($get_ymdh['ymi'][$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($get_ymdh['ym'][$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select id="start_day" name="start_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option  value="<?php echo h($i);?>" <?php if ($i == $day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error']['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error']['error_date']);?></span><br />
            <select name="start_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="start_hour" value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
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
            <select id="end_ym" name="end_ym">
            <?php for ($i=0; $i<=24; $i++):?>
                <option value="<?php echo h($get_ymdh['ymi'][$i]);?>" <?php if ($i == 12):?>selected<?php endif;?>><?php echo h($get_ymdh['ym'][$i]);?></option>
            <?php endfor; ?>
            </select>
            <!-- TODO:月によって日付が違うのでJSで直す -->
            <select id="end_day" name="end_day">
            <?php for ($i=1; $i<=31; $i++):?>
                <option value="<?php echo h($i);?>" <?php if ($i == $end_day):?>selected<?php endif;?>><?php echo h($i);?>日</option>
            <?php endfor; ?>
            </select>
            <span class="error"><?php echo h($_SESSION['error']['error_compare_date']);?></span><br />
            <span class="error"><?php echo h($_SESSION['error']['error_date']);?></span><br />
            <select name="end_hour">
            <?php for ($i=1; $i<24; $i++):?>
                <option id="end_hour" value="<?php echo h($i);?>" <?php if ($i == date('H')):?>selected<?php endif;?>><?php echo h($i);?>時</option>
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
            <div id="alert_schedule_title" class="error">タイトルを入力してください</div>
            <span class="error"><?php //echo h($_SESSION['error']['error_schedule_title']);?></span>
        </td>
    </tr>
    <tr>
        <th>詳細<br />※必須</th>
        <td>
            <textarea id="schedule_detail" name="schedule_detail"  placeholder="詳細を入力してください"　rows=5 cols=40><?php if (isset($_SESSION['error']['keep_detail']) && !isset($schedule_id)) { echo $_SESSION['error']['keep_detail'][$year][$formatted_month][$day]; } else { echo h($schedule_sql[$year][$month][$day][$schedule_id]['detail']); }?></textarea>
            <br /><span id="alert_schedule_detail" class="error">詳細を入力してください</span>
            <span class="error"><?php //echo h($_SESSION['error']['error_schedule_detail']);?></span>
        </td>
    </tr>

    <?php if(!empty($schedule_id)):?>
        <input type="hidden" name="schedule_id" value="<?php echo h($schedule_id);?>" />
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <input type="submit" class="btn" name="update" value="更新" />
    <?php else:?>
        <input type="hidden" name="token" value="<?php echo h($get_token);?>" />
        <span id="btn-regist"><input type="submit"  class="btn" name="insert" value="登録" /></span>
    <?php endif;?>

</table>
</form>
</div>
<!--
************ ポップアップEND ************
 -->
 <div></div>

<div clsss="calendar">
<!-- カレンダーループ 3回ループ -->
<?php foreach ($make_calendar['calendars'] as $key => $value) :?>

<table class="calendar_table">
    <thead>
    <tr>
        <th colspan="7">
        <?php
            $explode_cal = explode('-', $value);
            $cal_year = $explode_cal[0];
            $cal_month = $explode_cal[1];
        ?>
        <?php echo h($cal_year.'年'.$cal_month.'月');?>
        </th>
    </tr>
    <tr>
        <th class="sunday">日</th>
        <th>月</th>
        <th>火</th>
        <th>水</th>
        <th>木</th>
        <th>金</th>
        <th class="saturday">土</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <!-- 空セル挿入 -->
        <?php for($i=1; $i<=$make_calendar['before_cell'][$key]; $i++) :?>
            <td></td>
        <?php endfor ;?>

        <!-- 日付挿入 -->
        <?php for ($day=$start_date; $day<=$make_calendar['end_days'][$key]; $day++):?>

            <!-- 桁数を揃える -->
            <?php $days = sprintf('%02d', $day) ;?>
            <!-- 週末取得0~6 -->
            <?php $month_weekend=date("w", strtotime($value.'-'.$day));?>

                <?php $class = ''; ?>
                <?php if($month_weekend == 0):?><!-- 日曜日 -->
                    <?php $class = 'sunday'; ?>
                <?php elseif($month_weekend == 6):?><!-- 土曜日 -->
                    <?php $class = 'saturday'; ?>
                <?php endif;?>

                <?php if(date('j') == $day && $year.'-'.$month === $value) :?><!-- 今日 -->
                    <?php $class = 'today'; ?>
                <?php endif;?>

                <?php $holiday_name = ''; ?><!-- 祝日 -->
                <?php if(isset($holiday[$value.'-'.$days])):?>
                    <?php $class = 'holiday'; ?>
                    <?php $holiday_name = $holiday[$value.'-'.$days]; ?>
                <?php endif;?>

                <?php $auc_topi_feed = '';?><!-- オークショントピック -->
                <?php if (isset($auc_topi['title'][$value.'-'.$days])):?>
                    <?php $class = 'auc_topi';?>
                    <?php $auc_topi_feed = $auc_topi['title'][$value.'-'.$days];?>
                <?php endif;?>

                    <div class="cell"><a href=""><td class="<?php echo h($class); ?>">
                        <!-- 日付出力 -->
                        <span class="day" id="<?php echo $cal_year.'-'.$cal_month.'-'.$day;?>">
                            <a href="/?year=<?php echo h($cal_year);?>&month=<?php echo h($cal_month);?>&day=<?php echo h($day);?>"><?php echo h($day);?></a>
                        </span>
                        <!-- 祝日出力 -->
                        <span>
                            <?php echo h($holiday_name);?>
                        </span>
                        <!-- オクトピ出力 -->
                        <span>
                            <br /><a href="<?php echo h($auc_topi['link'][$value.'-'.$days]);?>" title="<?php echo h($auc_topi_feed);?>" target="_blank">
                            <?php echo h(shortStr($auc_topi_feed));?>
                            </a>
                        </span><br />

                        <!-- DBに登録されている予定出力 -->
                        <span>
                            <br />
                            <?php if (isset($schedules_months[$cal_year][$cal_month][$day])):?>
                                <?php foreach ($schedules_months[$cal_year][$cal_month][$day] as $schedule_id => $schedule):?>
                                    <a class="schedule" href="/schedule?year=<?php echo h($cal_year);?>&month=<?php echo h($cal_month);?>&day=<?php echo h($day.'&id='.$schedule_id);?>"
                                    title="<?php echo h($schedule['detail']);?>">
                                    <?php echo h($schedule['title']);?><br />
                                <?php endforeach;?>
                            <?php endif;?>
                            </a>
                        </span>
                    </td></a></div>

                <?php if($month_weekend == 6): ?><!-- 土曜日で改行 -->
                    </tr>
                <?php endif; ?>
        <?php endfor ;?>

        <!-- 空セル挿入 -->
        <?php for ($i=1; $i<(7-$make_calendar['after_cell'][$key]); $i++) :?>
            <td></td>
        <?php endfor ;?>
    </tbody>

</table>

<?php endforeach ;?>
</div><!--calendar-->
</body>
</html>
