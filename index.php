<?php
require_once 'database.php';
require_once 'function.php';

// $hoge = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);
// var_dump($hoge);
/*
define(GOOGLE_CAL_URL, 'japanese__ja@holiday.calendar.google.com');
//日付のタームゾーンを変更
ini_set("date.timezone", "Asia/Tokyo");

//現在の年月日、曜日の取得
$year = date('Y');
$month = date('m');
//月のスタート
$start_date = 1;
//カレンダー数
$display_count = 3;
$calendars = array();
$end_days = array();
$before_cell = array();
$after_cell  = array();

//GET値がある場合。ない場合は現在の年月
$ym = isset($_GET['ym']) ? $_GET['ym']:($year.'-'.$month);//2014-04-01
$explode_ym = explode('-', $ym);//[0] => 2014 [1] => 04
$year_of_ym = $explode_ym[0];//2014
$month_of_ym = $explode_ym[1];//04
if (checkdate($month_of_ym, 01, $year_of_ym) == false) {
    header('Location: http://kensyu.aucfan.com/');
    exit;
}

//先月
$last_month = array(
  'year' => date('Y', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
  'month' => date('m', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);
//来月
$next_month = array(
  'year' => date('Y', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
  'month' => date('m', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);

// Y-mを取得。$now_yearの前後1年
for ($i=-12; $i<=12; $i++) {
    $months[] = date('Y-m', mktime(0, 0, 0, $month_of_ym+($i), 1, $year_of_ym));
}

//// $prev_month  = $month_of_ym -1;はつかわない
$prev_month = $last_month['month'];
$prev_month2 = $last_month['month'];
$prev_month3 = $last_month['month'];
$prev_month4 = $last_month['month'];
*/

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);

//祝日
$holiday = getHoliday($last_month, $next_month, $end_days);

//オークショントピック
$auc_topi = aucTopi();

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
<div id="prev"><a href="?ym=<?php echo $last_month['year'].'-'.$last_month['month'];?>">前月</a></div>
<div id="this"><a href="/">今月</a></div>
<div id="next"><a href="?ym=<?php echo $next_month['year'].'-'.$next_month['month']; ?>">次月</a></div>
<form method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
    <select name="ym">
    <option>選択してください</option>
    <?php for ($i=0; $i<=24; $i++):?>
    <option id="select_year_month" value="<?php echo $months[$i];?>"><?php echo $months[$i] ;?></option>
    <?php endfor; ?>
    </select>
    <input type="submit" value="表示する">
</form>
</div><!--header-->

<!-- カレンダーループ 3回ループ -->
<?php foreach (/*$calendars*/$make_calendar['calendars'] as $key => $value) :?>
<table class="calendar">
    <thead>
    <tr>
        <th colspan="7">
        <?php
            $explode_cal = explode('-', $value);
            $cal_year = $explode_cal[0];
            $cal_month = $explode_cal[1];
        ?>
        <?php echo $cal_year.'年'.$cal_month.'月';?>
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
        <?php for($i=1; $i<=/*$before_cell[$key]*/$make_calendar['before_cell'][$key]; $i++) :?>
            <td></td>
        <?php endfor ;?>

        <!-- 日付挿入 -->
        <?php for ($day=$start_date; $day<=/*$end_days[$key]*/$make_calendar['end_days'][$key]; $day++):?>

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
                    <?php $holiday_name = '<br />'.$holiday[$value.'-'.$days]; ?>
                <?php endif;?>

                <?php $auc_topi_feed = '';?><!-- オークショントピック -->
                <?php if (isset($auc_topi['title'][$value.'-'.$days])):?>
                    <?php $class = 'auc_topi';?>
                    <?php $auc_topi_feed = $auc_topi['title'][$value.'-'.$days];?>
                <?php endif;?>

                    <td class="<?php echo $class; ?>">
                        <!-- 日付出力 -->
                        <span class="day">
                            <a href="http://kensyu.aucfan.com/schedule.php?year=<?php echo $cal_year;?>&month=<?php echo $cal_month;?>&day=<?php echo $day;?>"><?php echo $day;?></a>
                        </span>
                        <!-- 祝日出力 -->
                        <span>
                            <?php echo $holiday_name;?>
                        </span>
                        <!-- オクトピ出力 -->
                        <span>
                            <br /><a href="<?php echo $auc_topi['link'][$value.'-'.$days];?>" title="<?php echo $auc_topi_feed;?>" target="_blank">
                            <?php echo shortStr($auc_topi_feed);?>
                            </a>
                        </span><br />

                        <!-- DBに登録されている予定出力 -->
                        <span>
                            <br /><span class="schedule">

                            <?php if (isset($schedules[$cal_year][$cal_month][$day])):?>
                                <?php foreach ($schedules[$cal_year][$cal_month][$day] as $schedule_id => $schedule):?>
                                    <a href="http://kensyu.aucfan.com/schedule.php?year=<?php echo $cal_year;?>&month=<?php echo $cal_month;?>&day=<?php echo $day.'&id='.$schedule_id;?>"
                                    title="<?php echo $schedule['detail'];?>">
                                    <?php echo $schedule['title'];?><br />
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
        <?php for ($i=1; $i<(7-/*$after_cell[$key])*/$make_calendar['after_cell'][$key]); $i++) :?>
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