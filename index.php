<?php
require_once 'database.php';
require_once 'function.php';
require_once 'unset_session.php';

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);

//祝日
$holiday = getHoliday($last_month, $next_month, $end_days);

//オークショントピック
$auc_topi = aucTopi();

//DB接続
$connect_db = connectDB();

//フォームのデータ整形
$form_data = formData(/*$post_data, */$make_calendar);

//エスケープ
$escape_formdata = escapeFormdata($connect_db, $form_data);

//SQL文の生成
$sql_create = sqlCreate($escape_formdata, $check_token = true);

//SQL実行
$sql_result = sqlResult($escape_formdata, $connect_db, $sql_create);
$schedules_3months = $sql_result['schedules_3months'];

//SESSION初期化
$unset_session = unsetSession();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<link href="calendar.css" rel="stylesheet">
</head>
<body>
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

<!-- カレンダーループ 3回ループ -->
<?php foreach ($make_calendar['calendars'] as $key => $value) :?>
<table class="calendar">
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

                    <td class="<?php echo h($class); ?>">
                        <!-- 日付出力 -->
                        <span class="day">
                            <a href="http://kensyu.aucfan.com/schedule.php?year=<?php echo h($cal_year);?>&month=<?php echo h($cal_month);?>&day=<?php echo h($day);?>"><?php echo h($day);?></a>
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
                            <br /><span class="schedule">

                            <?php if (isset($schedules_3months[$cal_year][$cal_month][$day])):?>
                                <?php foreach ($schedules_3months[$cal_year][$cal_month][$day] as $schedule_id => $schedule):?>
                                    <a href="http://kensyu.aucfan.com/schedule.php?year=<?php echo h($cal_year);?>&month=<?php echo h($cal_month);?>&day=<?php echo h($day.'&id='.$schedule_id);?>"
                                    title="<?php echo h($schedule['detail']);?>">
                                    <?php echo h($schedule['title']);?><br />
                                <?php endforeach;?>
                            <?php endif;?>

                            </a></span>
                        </span>
                    </td>

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
</div><!--calendar-->
<?php endforeach ;?>

<div id="footer">
</div><!--footer-->
</body>
</html>