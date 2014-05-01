<?php
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

$prev_month  = $month_of_ym -1;
$prev_month2 = $month_of_ym -1;
$prev_month3 = $month_of_ym -1;
$prev_month4 = $month_of_ym -1;

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

$calendar_year = array();
$calendar_month = array();
//3ヶ月分の空セル等を取得
for ($i=1; $i<=$display_count; $i++) {
    $position = $i-(floor($display_count/2)+1);
    $calendars[$i] = date("Y-m", mktime(0, 0, 0, $prev_month++, 1, $year_of_ym));
    $before_cell[$i] = date('w', mktime(0, 0, 0, $prev_month2++, 1, $year_of_ym));
    $after_cell[$i]  = date('w', mktime(0, 0, 0, $prev_month3+1, 0, $year_of_ym));
    $prev_month3++;
    $end_days[$i] = date('t', mktime(0,0,0, $prev_month4++, 1, $year_of_ym));
}

//祝日
$holiday = getHoliday($last_month, $next_month, $end_days);

//オークショントピック
$auc_topi = aucTopi();


/*
* Googlle Calendar API 祝日取得
*/
function getHoliday($last_month, $next_month, $end_days) {
    $min_date = $last_month['year'].'-'.$last_month['month'].'-01';
    $max_date = $next_month['year'].'-'.$next_month['month'].'-'.$end_days[2];
    $holidays_url = sprintf(
            "http://www.google.com/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json" ,
            "outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com" , // 'japanese@holiday.calendar.google.com' ,
            "$min_date" ,  // 取得開始日
            "$max_date" ,  // 取得終了日
            50             // 最大取得数
            );
    if ($results = file_get_contents($holidays_url)) {
            $results = json_decode($results, true);

            $holidays = array();
            foreach ($results['feed']['entry'] as $key =>$val ) {
                    $date  = $val['gd$when'][0]['startTime'];
                    $title = $val['title']['$t'];
                    $holidays[$date] = $title;// [2007-01-01] => 元日 / Ganjitsu / New Year's Day
            }
            ksort($holidays);
    }

    $explode_date = array();
    $explode_holidays = array();
    $holiday_list = array();
    foreach ($holidays as $date => $holiday) {
        $explode_date[]  = explode('-', $date);
        $explode_holidays[] = explode(' / ', $holiday);
        foreach ($explode_holidays as $key => $value) {
            $holiday_list[$date] = $value[0];//[2007-01-01] => 元日
        }
    }
    return $holiday_list;
}


/*
*オークショントピック
*/
function aucTopi() {
    $rss = simplexml_load_file('http://aucfan.com/article/feed/');
    $data = get_object_vars($rss);
    if (empty($rss)) {
        return;
    }

    $title = array();
    $date = array();
    $link = array();
    $auc_topi_title = array();
    foreach ($rss->channel->item as $key => $value) {
        $title = (string)$value->title;
        $date = date('Y-m-d', strtotime((string)$value->pubDate));
        $link = (string)$value->link;
        $auc_topi['title'][$date] = $title;
        $auc_topi['link'][$date] = $link;
    }
    return $auc_topi;
}

/*
*文字数の制限
*/
function shortStr ($str, $length = 15) {
    if (mb_strlen($str) <= $length) {
        return $str;
    } else {
        return mb_substr($str, 0, $length, 'utf-8');
    }
}


/*
*DB接続
*/
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'calendar';

// MySQL に接続し、データベースを選択
$db = mysqli_connect($host, $user, $password, $database);

// 接続状況をチェック
if (mysqli_connect_errno()) {
    die(mysqli_connect_error());
}

/*
*フォームからPOSTされたデータ
*/
$post_data = $_POST;
//開始時間と終了時間
$start_time = $post_data['start_hour'].':'.$post_data['start_min'].':00';
$end_time = $post_data['end_hour'].':'.$post_data['end_min'].':00';
//開始日と終了日
$start_day = $post_data['start_ym'].'-'.$post_data['start_day'].' '.$start_time;
$end_day = $post_data['end_ym'].'-'.$post_data['end_day'].' '.$end_time;
//予定のタイトルと詳細
$schedule_title = $post_data['schedule_title'];
$schedule_detail = $post_data['schedule_detail'];
$id = $post_data['schedule_id'];
$between_begin = $calendars[1].'-01 00:00:01';
$between_end = $calendars[3].'-'.$end_days[3].' 23:59:59';

//エラー（入力漏れがあった）場合は
$error_year = $_COOKIE['error_year'];
$error_month = $_COOKIE['error_month'];
$error_day = $_COOKIE['error_day'];
$error_id = '';
if (isset($_COOKIE['error_id'])) {
    $error_id = '&id='.$_COOKIE['error_id'];
}

//バリデート
if ($post_data['start_hour'] == '' || $post_data['start_min'] == '' || $post_data['end_hour'] == '' || $post_data['end_min'] == '') {
    setcookie('error_hour', '時間は必須です', time()+1);
}
if ($post_data['start_ym'] == '' || $post_data['start_day'] == '' || $post_data['end_ym'] == '' || $post_data['end_day'] == '') {
    setcookie('ymd', '年月日は必須です', time()+1);
}
if ($schedule_title == '') {
    setcookie('schedule_title', 'タイトルは必須です', time()+1);
}
if ($schedule_detail == '') {
    setcookie('schedule_detail', '詳細は必須です', time()+1);
}
if (strtotime($start_day) > strtotime($end_day)) {
    setcookie('error_compare_date', '開始日時が終了日時より遅く設定されています', time()+1);
}

//無効な日付化チェックする
$explode_start_ym = explode('-', $post_data['start_ym']);
$explode_end_ym = explode('-', $post_data['end_ym']);
$check_start_ym = checkdate($explode_start_ym[1], $post_data['start_day'], intval($explode_start_ym[0]));
$check_end_ym = checkdate($explode_end_ym[1], $post_data['end_day'], intval($explode_end_ym[0]));
if ($check_start_ym == false || $check_end_ym == false) {
    setcookie('date_error', '無効な日付です', time()+1);
}
//再度入力フォームに戻す
if (isset($post_data['insert']) || isset($post_data['update'])) {
    if (empty($post_data['start_ym']) || empty($post_data['start_day']) || empty($post_data['start_hour']) || empty($post_data['start_min']) ||empty($post_data['end_ym']) || empty($post_data['end_day']) || empty($post_data['end_hour']) || empty($post_data['end_min']) || empty($schedule_title) || empty($schedule_detail) || $check_start_ym == false || $check_end_ym == false || strtotime($start_day) > strtotime($end_day)) {
        header("Location: http://kensyu.aucfan.com/error.php?year=".$error_year."&month=".$error_month."&day=".$error_day.$error_id);
        exit;
    }
}

//UPDATEじゃないとき、そして予定のタイトルが空じゃないとき
if (empty($id) && ($schedule_title != null)) {

$sql=<<<END
    INSERT INTO
         cal_schedules
     SET
        start_date="$start_day",
        end_date="$end_day",
        schedule_title="$schedule_title",
        schedule_detail="$schedule_detail",
        update_at=NOW(),
        created_at=NOW(),
        deleted_at=null
END;

}
elseif (isset($id) && !isset($post_data['delete'])) {

$sql=<<<END
    UPDATE
         cal_schedules
     SET
        start_date="$start_day",
        end_date="$end_day",
        schedule_title="$schedule_title",
        schedule_detail="$schedule_detail",
        update_at=NOW()
     WHERE
        schedule_id="$id"
END;

}
elseif ($post_data['delete'] == 'delete') {

$sql=<<<END
    UPDATE
         cal_schedules
     SET
         update_at=NOW(),
         deleted_at=NOW()
     WHERE
        schedule_id="$id"
END;

}

//予定を3ヶ月分取得
$schedule_sql=<<<END
    SELECT
         schedule_id, start_date, end_date, schedule_title, schedule_detail
     FROM
         cal_schedules
     WHERE
         deleted_at
     IS
         null
     AND
         start_date
     BETWEEN
         "$between_begin"
     AND
         "$between_end"

END;

//SQL実行
if (isset($start_day) && !empty($sql)) {
    $sql_result = mysqli_query($db, $sql);
}
if ($result = mysqli_query($db, $schedule_sql)) {
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        list($schedule_year, $schedule_month, $schedule_day) = explode('-', date('Y-m-j',strtotime($row['start_date'])));
        list($end_schedule_year, $end_schedule_month, $end_schedule_day) = explode('-', date('Y-m-j',strtotime($row['end_date'])));
        $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
        $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
        if ($row['start_date'] != $row['end_date']) {
            for ($i=$schedule_day; $i<=$end_schedule_day; $i++) {
                $schedules[$schedule_year][$schedule_month][$i][$row['schedule_id']]['title'] = $row['schedule_title'];
            }
        }
    }
    mysqli_free_result($result);
}
mysqli_close($db);


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
<?php foreach ($calendars as $key => $value) :?>
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
        <?php for($i=1; $i<=$before_cell[$key]; $i++) :?>
            <td></td>
        <?php endfor ;?>

        <!-- 日付挿入 -->
        <?php for ($day=$start_date; $day<=$end_days[$key]; $day++):?>

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
        <?php for ($i=1; $i<(7-$after_cell[$key]); $i++) :?>
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