<?php
$now_year  = date('Y'); //現在の年を取得
$now_month = date('n'); //現在の月を取得
$now_day   = date('j'); //現在の日を取得
if(isset($_GET['ym']))
{
  $now_year  = date('Y',strtotime($_GET['ym'].'-1'));
  $now_month = date('n',strtotime($_GET['ym'].'-1'));
}
$now = time();
$today = getdate($now);
// print_r($today);

$prev_month = $now_month - 1;
$next_month = $now_month + 1;

//前月、翌月リンク
$prev = date('Y-n', mktime(0, 0, 0, $now_month-1, 1, $now_year));
$now  = date('Y-n', mktime(0, 0, 0, $now_month, 1, $now_year));
$next = date('Y-n', mktime(0, 0, 0, $now_month+1, 1, $now_year));

// Y-nを取得。$now_yeaeの前後1年
for ($i=-12; $i<=12; $i++) {
    $months[] = date('Y-n', mktime(0, 0, 0, $now_month+($i), 1, $now_year));
}

$start_day      = 1;
$prev_month_day = 1;
$next_month_day = 1;

$wd1      = date('w', mktime(0, 0, 0, $now_month, 1, $now_year)); //1日の曜日を数値で取得
$wdx      = date('w', mktime(0, 0, 0, $now_month + 1, 0, $now_year));
$prev_wd1 = date('w', mktime(0, 0, 0, $prev_month, 1, $now_year)); //1日の曜日を数値で取得
$prev_wdx = date('w', mktime(0, 0, 0, $prev_month + 1, 0, $now_year));
$next_wd1 = date('w', mktime(0, 0, 0, $next_month, 1, $now_year)); //1日の曜日を数値で取得
$next_wdx = date('w', mktime(0, 0, 0, $next_month + 1, 0, $now_year));


/*Google Calendar API*/
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
      <div id="prev"><a href="?ym=<?php echo $prev;?>">前の月</a></div>
      <div id="next"><a href="?ym=<?php echo $next;?>">次の月</a></div>

      <form id="" name="" method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
        <select name="ym">
          <?php for ($i=0; $i<=69; $i++):?>
          <option id="select_year_month" value="<?php echo $months[$i] ;?>"><?php echo $months[$i] ;?></option>
          <?php endfor; ?>
        </select>
        <input type="submit" value="表示する">
      </form>
      <?php //print_r($_GET); ?>
    </div>

    <div id="calendar">
      <table id="prev_calendar">
        <thead>
          <tr>
              <th colspan="7">
                <?php
                  if (!isset($_GET['ym'])) {
                    echo $prev;
                  }
                  else {
                    echo $prev;
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
            <?php
              // 空セルを挿入
              for ($i=1; $i<=$prev_wd1; $i++) {
                  echo "<td></td>";
              }
              while (checkdate($prev_month, $prev_month_day, $now_year)) {
                $prev_month_weekend=date("w", mktime(0, 0, 0, $prev_month, $prev_month_day, $now_year));
                switch( $prev_month_weekend ){
                  case 0: //日曜日の色
                      echo '<td class="sunday">'.$prev_month_day.'</td>';
                      break;
                  case 6: //土曜日の色
                      echo '<td class="saturday">'.$prev_month_day.'</td>' ;
                      break;
                  default: //月～金曜日の色
                      if ($prev_month_day == $now_day && $prev == date('Y-n')) {
                        echo '<td class="today">'.$prev_month_day.'</td>';
                        break;
                      }
                      echo "<td>$prev_month_day</td>";
                      break;
                }
                $prev_month_day++;
                //土曜日で改行する
                if (date("w", mktime(0,0,0, $prev_month, $prev_month_day, $now_year)) == 0) {
                    echo "</tr>";

                    //新たな週を準備
                    if (checkdate($prev_month, $prev_month_day + 1, $now_year)) {
                      echo "<tr>";
                    }
                }
              }
              //空セル挿入
              for ($i = 1; $i < 7 -$prev_wdx; $i++) {
                  echo "<td></td>";
              }
            ?>

        </tbody>
      </table>


      <table id="main_calendar">
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
            <?php
              // 空セルを挿入
              for ($i=1; $i<=$wd1; $i++) {
                  echo "<td></td>";
              }
                while (checkdate($now_month, $start_day, $now_year)) {

                $now_month_weekend=date("w", mktime(0, 0, 0, $now_month, $start_day, $now_year));
                switch( $now_month_weekend ){
                  case 0: //日曜日の色
                      echo '<td class="sunday">'.$start_day.'</td>';
                      break;
                  case 6: //土曜日の色
                      echo '<td class="saturday">'.$start_day.'</td>' ;
                      break;
                  default: //月～金曜日の色
                      if ($start_day == $now_day && $now == date('Y-n')) {
                        echo '<td class="today">'.$start_day.'</td>';
                        break;
                      }
                      echo "<td>$start_day</td>";
                      break;
                }
                $start_day++;

                //土曜日で改行する
                if (date("w", mktime(0,0,0, $now_month, $start_day, $now_year)) == 0) {
                    echo "</tr>";

                    //新たな週を準備
                    if (checkdate($now_month, $start_day + 1, $now_year)) {
                      echo "<tr>";
                    }
                }
              }
              //空セル挿入
              for ($i = 1; $i < 7 -$wdx; $i++) {
                  echo "<td></td>";
              }
            ?>
        </tbody>
      </table>


      <table id="next_calendar">
        <thead>
          <tr>
              <th colspan="7">
                <?php
                  if (!isset($_GET['ym'])) {
                    echo $next;
                  }
                  else {
                    echo $next;

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
            <?php
              // 空セルを挿入
              for ($i=1; $i<=$next_wd1; $i++) {
                  echo "<td></td>";
              }
              while (checkdate($next_month, $next_month_day, $now_year)) {

                $next_month_weekend=date("w", mktime(0, 0, 0, $next_month, $next_month_day, $now_year));
                switch( $next_month_weekend ){
                  case 0: //日曜日の色
                      echo '<td class="sunday">'.$next_month_day.'</td>';
                      break;
                  case 6: //土曜日の色
                      echo '<td class="saturday">'.$next_month_day.'</td>' ;
                      break;
                  default: //月～金曜日の色
                      if ($next_month_day == $now_day && $next == date('Y-n')) {
                        echo '<td class="today">'.$next_month_day.'</td>';
                        break;
                      }
                      echo "<td>$next_month_day</td>";
                      break;
                }
                $next_month_day++;

                //土曜日で改行する
                if (date("w", mktime(0,0,0, $next_month, $next_month_day, $now_year)) == 0) {
                    echo "</tr>";

                    //新たな週を準備
                    if (checkdate($next_month, $next_month_day + 1, $now_year)) {
                      echo "<tr>";
                    }

                }
              }
              //空セル挿入
              for ($i = 1; $i < 7 -$next_wdx; $i++) {
                  echo "<td></td>";
              }
            ?>
        </tbody>
      </table>
    </div>


    <div id="footer">
    </div>
  </body>
</html>