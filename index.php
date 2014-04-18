<?php
//現在の年月日、曜日の取得
$now = time();
$today = getdate($now);
$year = $today['year'];
$month = $today['mon'];
//月のスタート
$start_day = 1;
//カレンダー数
$display_count = 3;
$calendars = array();
$end_day = array();

//GET値がある場合。ない場合は現在の年月
$ym = isset($_GET['ym']) ? $_GET['ym']:($year.'-'.$month);
$explode_ym = explode('-', $ym);//[0] => 2014 [1] => 5
$year_of_ym = $explode_ym[0];
$month_of_ym = $explode_ym[1];

//先月
$last_month = array(
  'year' => date('Y', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
  'month' => date('n', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);
//来月
$next_month = array(
  'year' => date('Y', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
  'month' => date('n', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);


$prev_month  = $month_of_ym -1;
$prev_month2 = $month_of_ym -1;
$prev_month3 = $month_of_ym -1;
$prev_month4 = $month_of_ym -1;
$before_cell = array();
$after_cell  = array();

//奇数月と偶数月
if ($display_count % 2 == 1) {
    for ($i=1; $i<=$display_count; $i++) {
        $position = $i-(floor($display_count/2)+1);
        $calendars[] = date("Y-n", mktime(0, 0, 0, $prev_month++, 1, $year_of_ym));
        $before_cell[] = date('w', mktime(0, 0, 0, $prev_month2++, 1, $year_of_ym));
        $after_cell[]  = date('w', mktime(0, 0, 0, $prev_month3+1, 0, $year_of_ym));
        $prev_month3++;
        $end_day[] = date('t', mktime(0,0,0, $prev_month4++, 1, $year_of_ym));
    }
}

// Y-nを取得。$now_yeaeの前後1年
for ($i=-12; $i<=12; $i++) {
    $months[] = date('Y-n', mktime(0, 0, 0, $month+($i), 1, $year));
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
<div id="header">
<h3>郡カレンダー</h3>
<div id="prev"><a href="?ym=<?php echo $last_month['year'].'-'.$last_month['month'];?>">先月</a></div>
<div id="this"><a href="/">今月</a></div>
<div id="next"><a href="?ym=<?php echo $next_month['year'].'-'.$next_month['month']; ?>">来月</a></div>
<form method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
    <select name="ym">
    <option>選択してください</option>
    <?php for ($i=0; $i<=24; $i++):?>
    <option id="select_year_month" value="<?php echo $months[$i] ;?>"><?php echo $months[$i] ;?></option>
    <?php endfor; ?>
    </select>
    <input type="submit" value="表示する">
</form>
</div><!--header-->

<div id="calendar">
<!-- カレンダーループ 3回ループ -->
<?php foreach ($calendars as $key => $value) :?>
<table class="main_calendar">
    <thead>
    <tr>
        <th colspan="7">
            <?php echo $value;?>
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
        <?php for($i=1; $i<=$before_cell[$key]; $i++) :?>
            <td></td>
        <?php endfor ;?>

        <!-- 日付挿入 -->
        <?php for ($day=$start_day; $day<=$end_day[$key]; $day++):?>
            <!-- 週末取得0~6 -->
            <?php $month_weekend=date("w", strtotime($value.'-'.$day));?>
            <!-- 週末 -->
            <?php if ($month_weekend == 0) :?>
                <tr><td class="sunday"><?php echo $day ;?></td>
            <?php endif ;?>
            <?php if ($month_weekend == 6) :?>
                <td class="saturday"><?php echo $day ;?></td></tr>
            <?php endif ;?>
            <!-- 週末以外 -->
            <?php if ($month_weekend != 0 && $month_weekend != 6) :?>
                <?php if ($today['mday'] === $day && $year.'-'.$month === $value) :?>
                    <td class="today"><?php echo $day;?></td>
                <?php else :?>
                    <td><?php echo $day ;?></td>
                <?php endif ;?>
            <?php endif ;?>
        <?php endfor ;?>

        <!-- 空セル挿入 -->
        <?php for ($i=1; $i<(7-$after_cell[$key]); $i++) :?>
            <td></td>
        <?php endfor ;?>
    </tbody>
</table>
<?php endforeach ;?>
</div><!--calendar-->


<div id="footer">
</div><!--footer-->
</body>
</html>
