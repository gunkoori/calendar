<?php
$now_year = date("Y"); //現在の年を取得
$now_month = date("n"); //現在の月を取得
if(isset($_GET['ym']))
{
  $now_year = date("Y",strtotime($_GET['ym'].'-1'));
  $now_month = date("n",strtotime($_GET['ym'].'-1'));
}


$prev_month = $now_month - 1;
$next_month = $now_month + 1;

//前月、翌月リンク
$prev = date("Y-n", mktime(0, 0, 0, $now_month-1, 1, $now_year));
$now = date("Y-n", mktime(0, 0, 0, $now_month, 1, $now_year));
$next = date("Y-n", mktime(0, 0, 0, $now_month+1, 1, $now_year));


//$ym = isset($_GET['ym']) ? $_GET['ym'] : date("Y-m");
/*
$timeStamp = strtotime($ym . "-01");
print_r($timeStamp);
if ($timeStamp === false) {
  $timeStamp = time();
}
*/

$now_day = date("j"); //現在の日を取得

$start_day=1;
$prev_month_day = 1;
$next_month_day = 1;

$countdate = date("t"); //今月の日数を取得
$weekday = array("月","火","水","木","金","土","日"); //曜日の配列作成

$wd1 = date("w", mktime(0, 0, 0, $now_month, 1, $now_year)); //1日の曜日を数値で取得
$wdx = date("w", mktime(0, 0, 0, $now_month + 1, 0, $now_year));
$prev_wd1 = date("w", mktime(0, 0, 0, $prev_month, 1, $now_year)); //1日の曜日を数値で取得
$prev_wdx = date("w", mktime(0, 0, 0, $prev_month + 1, 0, $now_year));
$next_wd1 = date("w", mktime(0, 0, 0, $next_month, 1, $now_year)); //1日の曜日を数値で取得
$next_wdx = date("w", mktime(0, 0, 0, $next_month + 1, 0, $now_year));

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
      <div id="prev"><a href="?select=prev&ym=<?php echo $prev;?>">前の月</a></div>
      <div id="next"><a href="?select=next&ym=<?php echo $next;?>">次の月</a></div>

      <form id="" name="" method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
        <select name="select=prev">
          <?php for ($i=1990; $i<=$now_year; $i++):?>
          <option id="select_year" value="<?php echo $i ;?>"><?php echo $i."年" ;?></option>
          <?php endfor; ?>
        </select>

        <select name="month">
          <?php for ($i=1; $i<=12; $i++): ?>
          <option id="select_month" value="<?php echo $i ;?>"><?php echo $i."月" ;?></option>
          <?php endfor; ?>
        </select>
        
        <!-- <button class="change_calendar">変更</button> -->
        <!-- <input type="hidden" value="prev"> -->
        <!-- <input type="hidden" value="ym"> -->
        <!-- <a href="?select=prev&ym=<?php echo $_POST['year']."-".$_POST['month'];?>">-->
        <input type="submit" value="表示する">
      </form>
      <?php //print_r($_POST);?>
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
                  elseif (isset($_GET['select']) && $_GET['select'] == "prev") {
                    echo $prev;
                  }
                  elseif (isset($_GET['select']) && $_GET['select'] == "next") {
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
                  case 0: //日曜日の文字色
                      echo '<td class="sunday">'.$prev_month_day.'</td>';
                      break;
                  case 6: //土曜日の文字色
                      echo '<td class="saturday">'.$prev_month_day.'</td>' ;
                      break;
                  default: //月～金曜日の文字色
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
                  elseif (isset($_GET['select']) && $_GET['select'] == "prev") {
                    echo $now;
                  }
                  elseif (isset($_GET['select']) && $_GET['select'] == "next") {
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
                  case 0: //日曜日の文字色
                      echo '<td class="sunday">'.$start_day.'</td>';
                      break;
                  case 6: //土曜日の文字色
                      echo '<td class="saturday">'.$start_day.'</td>' ;
                      break;
                  default: //月～金曜日の文字色
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
                  elseif (isset($_GET['select']) && $_GET['select'] == "prev") {
                    echo $next;
                  }
                  elseif (isset($_GET['select']) && $_GET['select'] == "next") {
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
                  case 0: //日曜日の文字色
                      echo '<td class="sunday">'.$next_month_day.'</td>';
                      break;
                  case 6: //土曜日の文字色
                      echo '<td class="saturday">'.$next_month_day.'</td>' ;
                      break;
                  default: //月～金曜日の文字色
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