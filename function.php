<?php
require_once 'database.php';

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
    header('Location: /');
    exit;
}

//先月
$last_month = array(
    'year'  => date('Y', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
    'month' => date('m', strtotime('last month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);
//来月
$next_month = array(
    'year'  => date('Y', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01'))),
    'month' => date('m', strtotime('next month', strtotime($year_of_ym.'-'.$month_of_ym.'-01')))
);

// Y-mを取得。$now_yearの前後1年
for ($i=-12; $i<=12; $i++) {
    $months[] = date('Y-m', mktime(0, 0, 0, $month_of_ym+($i), 1, $year_of_ym));
}

$prev_month = $last_month['month'];


/*
*
*/
function getYmdh($year_of_ym, $month_of_ym) {
    for ($i=-13; $i<=12; $i++) {
        list($years, $months, $days) = explode('-', date('Y-n-t', mktime(0, 0, 0, $month_of_ym+($i), 1, intval($year_of_ym)) ));
        $ym[]  = $years.'年'.$months.'月';
        $ymi[] = $years.'-'.$months;
    }

    return array(
        'ym'  => $ym,
        'ymi' => $ymi
        );
}


/*
*カレンダー生成
*/
function makeCalendar($display_count, $prev_month,  $year_of_ym) {
    global $end_days;
    //3ヶ月分の空セル等を取得
    for ($i=1; $i<=$display_count; $i++) {
        $calendars[$i]   = date("Y-m", mktime(0, 0, 0, $prev_month+$i, 1, $year_of_ym));
        $before_cell[$i] = date('w', mktime(0, 0, 0, $prev_month+$i, 1, $year_of_ym));
        $after_cell[$i]  = date('w', mktime(0, 0, 0, $prev_month+$i+1, 0, $year_of_ym));
        $end_days[$i]    = date('t', mktime(0,0,0, $prev_month+$i, 1, $year_of_ym));
    }
    return array(
        'calendars'   => $calendars,
        'before_cell' => $before_cell,
        'after_cell'  => $after_cell,
        'end_days'    => $end_days
        );
}


/*
* Googlle Calendar API 祝日取得
*/
function getHoliday($last_month, $next_month) {
    $make_calendar =  makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);
    $min_date      = $last_month['year'].'-'.$last_month['month'].'-01';
    $max_date      = $next_month['year'].'-'.$next_month['month'].'-'.$make_calendar['end_days'][3];
    $holidays_url  = sprintf(
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
*XSS対策
*HTML特殊文字をエスケープする
* < → &lt;
* > → &gt;
* & → &amp;
* " → &quot;
*
*ENT_QUOTES
* ' → &apos;
*/
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


/*
*ワンタイムトークンを生成する
*
*/
function getToken($key = '') {
    $_SESSION['key'] = $key;
    $token = sha1($key);//ハッシュ化する
    return $token;
}

/*
*ワンタイムトークンをチェックする
*
*/
function checkToken($token = '') {
    return ($token === sha1($_SESSION['key']));
}
