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

$prev_month = $last_month['month']-1;

/*
* 年月日取得
*
* @param  string $year_of_ym YYYY形式
* @param  string $month_of_ym MM形式
* @return array 'ym' => Y年M月, 'month' => Y-M
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
* カレンダー生成
*
* @param  int $display_count カレンダー数
* @param  int $prev_month 先月より1ヶ月前
* @param  string $year_of_ym YYYY形式
* @return array カレンダー、前の空セル、後の空セル、月の最終日
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
*
* @param  string $last_month 先月の年と月
* @param  string $next_month 来月の年と月
* @return array $holiday_list[2007-01-01]:string
*/
function getHoliday($last_month, $next_month) {
    $make_calendar =  makeCalendar($display_count, $prev_month, $year_of_ym);
    $min_date      = $last_month['year'].'-'.$last_month['month'].'-01';
    $max_date      = $next_month['year'].'-'.$next_month['month'].'-'.$make_calendar['end_days'][3];
    $holidays_url  = sprintf(
            "http://www.google.com/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json" ,
            "outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com" , // 'japanese@holiday.calendar.google.com' ,
            "$min_date" ,  // 取得開始日
            "$max_date" ,  // 取得終了日
            50             // 最大取得数
            );
    $context = stream_context_create(array(
      'http' => array('ignore_errors' => true)
    ));
    if ($results = file_get_contents($holidays_url, false, $context)) {
            $results = json_decode($results, true);

            $holidays = array();
            if (is_array($results)) {
                foreach ($results['feed']['entry'] as $key =>$val ) {
                        $date  = $val['gd$when'][0]['startTime'];
                        $title = $val['title']['$t'];
                        $holidays[$date] = $title;// [2007-01-01] => 元日 / Ganjitsu / New Year's Day
                }
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
    //祝日の取得に失敗した場合
    if (is_array($results) === false)  {
        $holiday_list = false;
    }
    return $holiday_list;
}


/*
*オークショントピック
*
* @return array $auc_topi['title']['Y-m-d']:string
* @return array $auc_topi['link']['Y-m-d']:string
*/
function aucTopi() {
    $rss = @simplexml_load_file('http://aucfan.com/article/feed/');
    $data = @get_object_vars($rss);
    if (empty($rss)) {
        $auc_topi = false;
    }
// var_dump($rss->channel);
    $titles = array();
    $dates = array();
    $link = array();
    $item = array();
    $auc_topi = array();
    // var_dump($rss->channel);
    if (isset($rss)) {
        foreach ($rss->channel->item as $value) {
            // var_dump($value);
            $titles[] = (string)$value->title;
            $dates[] = date('Y-m-d', strtotime((string)$value->pubDate));

            // var_dump($dates);
            $links[] = (string)$value->link;

            foreach ($dates as $key => $date) {
                $auc_topi[$date]['title'][$key] = $titles[$key];
                $auc_topi[$key][$date]['link'] = $links[$key];

            }

           // $item = array(
           //      $titles
           //  );
        }

    }
    return $auc_topi;
}

/*
* 文字数の制限
* @param  string  $str 元の文字列
* @param  string  $str 省略したの文字列
*/
function shortStr ($str, $length = 13) {
    if (mb_strlen($str) <= $length) {
        return $str;
    } else {
        return mb_substr($str, 0, $length, 'utf-8');
    }
}


/*
* HTML特殊文字をエスケープする
* @return  string  $str 文字列のエスケープ
*/
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/*
* ランダムな文字列の生成 36文字まで生成可能
* @return
*/
function randomStr($length) {
    return substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, $length);
}


/*
* ワンタイムトークンを生成する
* @return  string  $token トークン
*/
function getToken($key = '') {
    $_SESSION['key'] = $key;
    $token = sha1($key);//ハッシュ化する
    return $token;
}

/*
* ワンタイムトークンをチェックする
* @return  trueかfalse
*/
function checkToken($token = '') {
    return ($token === sha1($_SESSION['key']));
}
