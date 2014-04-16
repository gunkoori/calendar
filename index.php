<?php
//現在の年月日、曜日の取得
$now = time();
$today = getdate($now);
$year = $today['year'];
$month = $today['mon'];
$start_day = 1;
$end_day = date('t');

if (isset($_GET['ym'])) {
    $year = date('Y',strtotime($_GET['ym'].'-1'));
    $month = date('n',strtotime($_GET['ym'].'-1'));
}
// print_r($year);

//今月
$this_month = strtotime($year.$month.'1');//1397646881

//先月
$last_month = array(
  'year' => date('Y', strtotime('last month', $this_month)),
  'month' => date('n', strtotime('last month', $this_month))
);

//来月
$next_month = array(
  'year' => date('Y', strtotime('next month', $this_month)),
  'month' => date('n', strtotime('next month', $this_month))
);

//先月
// $last_month = date('m', strtotime('last month', $month));
// print_r($last_month);


/*
$now_year  = date('Y'); //現在の年を取得
$now_month = date('n'); //現在の月を取得
$now_day   = date('j'); //現在の日を取得
if(isset($_GET['ym']))
{
  $now_year  = date('Y',strtotime($_GET['ym'].'-1'));
  $now_month = date('n',strtotime($_GET['ym'].'-1'));
}
*/


$next_next_month = $prev_month+2;

//前月、翌月リンク
$prev = date('Y-n', mktime(0, 0, 0, $month-1, 1, $year));
$now  = date('Y-n', mktime(0, 0, 0, $month, 1, $year));
$next = date('Y-n', mktime(0, 0, 0, $month+1, 1, $year));

// Y-nを取得。$now_yeaeの前後1年
for ($i=-12; $i<=12; $i++) {
    $months[] = date('Y-n', mktime(0, 0, 0, $month+($i), 1, $year));
}

$prev_month_day = 1;
$next_month_day = 1;



$wd1      = date('w', mktime(0, 0, 0, $month, 1, $year)); //1日の曜日を数値で取得
$wdx      = date('w', mktime(0, 0, 0, $month + 1, 0, $year));
$prev_wd1 = date('w', mktime(0, 0, 0, $month, 1, $year)); //1日の曜日を数値で取得
$prev_wdx = date('w', mktime(0, 0, 0, $month + 1, 0, $year));
$next_wd1 = date('w', mktime(0, 0, 0, $month, 1, $year)); //1日の曜日を数値で取得
$next_wdx = date('w', mktime(0, 0, 0, $month + 1, 0, $year));


/*Google Calendar API*/
/*
$last_year = date('Y-01-01', mktime(0, 0, 0, $now_month, 1, $now_year-1)); //今から1年前
$next_year = date('Y-12-31', mktime(0, 0, 0, $now_month, 1, $now_year+1)); //今から1年後

$holidays_url = sprintf(
    'http://74.125.235.142/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json',
    'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com',
    $last_year,    // 取得開始日
    $next_year,    // 取得終了日
    100                // 最大取得数
);

if($results=file_get_contents($holidays_url)) {
    $results  = json_decode($results, true);
    $holidays = array();
    foreach($results['feed']['entry'] as $val) {
        $date  = $val['gd$when'][0]['startTime']; // 日付を取得
        $title = $val['title']['$t']; // 何の日かを取得
        $holidays[$date] = $title; // 日付をキーに、祝日名を値に格納
    }
    ksort($holidays); // 日付順にソート
}
*/
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
<div id="next"><a href="?ym=<?php echo $next_month['year'].'-'.$next_month['month'];?>">来月</a></div>
<form method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
    <select name="ym">
    <?php for ($i=0; $i<=24; $i++):?>
    <option id="select_year_month" value="<?php echo $months[$i] ;?>"><?php echo $months[$i] ;?></option>
    <?php endfor; ?>
    </select>
    <input type="submit" value="表示する">
</form>
</div><!--header-->

<div id="calendar">
<?php //for($months=$last_month['month']; $months<=$next_month['next_month']; $months++):?>
<table class="main_calendar">
    <thead>
    <tr>
       <th colspan="7">
        <?php
            if (!isset($_GET['ym'])) {
                echo $now;
            }
            else {
                echo $now;
            }
        ?>
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
    <?php for($i=1; $i<=$wd1; $i++) :?>
        <td></td>
    <?php endfor ;?>

        <?php //while (checkdate($month, $start_day, $year)) :?>

        <!-- 日付挿入 -->
        <?php for ($start_day=$start_day; $start_day<=$end_day; $start_day++):?>
            <?php $month_weekend=date("w", mktime(0, 0, 0, $month, $start_day, $year));?>
            <!-- 週末 -->
            <?php if ($month_weekend == 0) :?>
                <td class="sunday"><?php echo $start_day ;?></td>
            <?php endif ;?>
            <?php if ($month_weekend == 6) :?>
                <td class="saturday"><?php echo $start_day ;?></td></tr>
            <?php endif ;?>
            <!-- 週末以外 -->
            <?php if ($month_weekend != 0 && $month_weekend != 6) :?>
                <td><?php echo $start_day ;?></td>
            <?php endif ;?>
        <?php endfor ;?>

        <?php //endwhile ;?>

        <!-- 土曜日で改行する -->
        <?php //if ($month_weekend == 6) :?>
        <!-- </tr> -->
        <?php //endif ;?>
        <!-- 新たな週を準備 -->
        <?php //if (checkdate($month, $start_day + 1, $year)) :?>
            <!-- <tr> -->
        <?php //endif ;?>

      <!-- 空セル挿入 -->
      <?php for ($i = 1; $i < 7 -$wdx; $i++) :?>
          <td></td>
      <?php endfor ;?>
    </tbody>
</table>
<?php //endfor ;?>
</div><!--calendar-->


<div id="footer">
</div><!--footer-->
</body>
</html>
