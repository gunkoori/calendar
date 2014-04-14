<?php
$holidays_url = sprintf(
    'http://74.125.235.142/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json' ,
    'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com' ,
    '2012-01-01',    // 取得開始日
    '2014-12-31',    // 取得終了日
    100                // 最大取得数
);

if($results=file_get_contents($holidays_url)) {
    $results = json_decode($results, true);
    $holidays = array();
    foreach($results['feed']['entry'] as $val) {
        $date = $val['gd$when'][0]['startTime']; // 日付を取得
        $title = $val['title']['$t']; // 何の日かを取得
        $holidays[$date] = $title; // 日付をキーに、祝日名を値に格納
    }
    ksort($holidays); // 日付順にソート
}
print_r($holidays);
