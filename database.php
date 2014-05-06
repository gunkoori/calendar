<?php
require_once 'function.php';

$post_data = $_POST;

$connect_db = connectDB();

$make_calendar = makeCalendar($display_count, $prev_month, $prev_month2, $prev_month3, $prev_month4, $year_of_ym);

$form_data = formData($post_data, $make_calendar);

$form_validate = formValidate($post_data, $form_data);

$sql_create = sqlCreate($form_data);

$sql_escape = sqlEscape($connect_db, $sql_create);

//INSERT UPDATEの実行
if (isset($sql_create['sql'])) {
    $insert_update =  sqlResult($form_data, $connect_db, $sql_create); 
    $insert_update['insert_or_update'];
    header('Location: http://kensyu.aucfan.com/');
    exit;
}

/*
*DB接続
*/
function connectDB() {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'calendar';

    $return = true;
    // MySQL に接続し、データベースを選択
    $db = mysqli_connect($host, $user, $password, $database);

    // 接続状況をチェック
    if (mysqli_connect_errno()) {
        die(mysqli_connect_error());
        $return = false;
    }
    return array(
        'db' => $db,
        'return' => $return
        );
}


/*
*フォームからPOSTされたデータ
*/
function formData($post_data, $make_calendar) {
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
    $delete = $post_data['delete'];
    $schedule_id = $_COOKIE['schedule_id'];
    $between_begin = $make_calendar['calendars'][1].'-01 00:00:01';
    $between_end = $make_calendar['calendars'][3].'-'.$make_calendar['end_days'][3].' 23:59:59';
    return array(
        'start_time' => $start_time,
        'end_time' => $end_time,
        'start_day' => $start_day,
        'end_day' => $end_day,
        'schedule_title' => $schedule_title,
        'schedule_detail' => $schedule_detail,
        'id' => $id,
        'delete' => $delete,
        'schedule_id' => $schedule_id,
        'between_begin' => $between_begin,
        'between_end' => $between_end
        );
}
 

function formValidate($post_data, $form_data) {
    //エラー（入力漏れがあった）場合は受け取る
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
    if ($form_data['schedule_title'] == '') {
        setcookie('schedule_title', 'タイトルは必須です', time()+1);
    }
    if ($form_data['schedule_detail'] == '') {
        setcookie('schedule_detail', '詳細は必須です', time()+1);
    }
    if (strtotime($form_data['start_day']) > strtotime($form_data['end_day'])) {
        setcookie('error_compare_date', '開始日時が終了日時より遅く設定されています', time()+1);
    }

    //無効な日付かチェックする ex.)2月３１日には登録できない
    $explode_start_ym = explode('-', $post_data['start_ym']);
    $explode_end_ym = explode('-', $post_data['end_ym']);
    $check_start_ym = checkdate($explode_start_ym[1], $post_data['start_day'], intval($explode_start_ym[0]));
    $check_end_ym = checkdate($explode_end_ym[1], $post_data['end_day'], intval($explode_end_ym[0]));
    if ($check_start_ym == false || $check_end_ym == false) {
        setcookie('date_error', '無効な日付です', time()+1);
    }
    //再度入力フォームに戻す
    if (isset($post_data['insert']) || isset($post_data['update'])) {
        if (empty($post_data['start_ym']) || empty($post_data['start_day']) || empty($post_data['start_hour']) || empty($post_data['start_min']) ||empty($post_data['end_ym']) || empty($post_data['end_day']) || empty($post_data['end_hour']) || empty($post_data['end_min']) || empty($post_data['schedule_title']) || empty($post_data['schedule_detail']) || $check_start_ym == false || $check_end_ym == false || (strtotime($start_day) > strtotime($end_day))) {
            $return = header("Location: http://kensyu.aucfan.com/schedule.php?year=".$error_year."&month=".$error_month."&day=".$error_day.$error_id);
            exit;
        }
    }
    else {
        $return = 'true';
    }
    return $return;
}

/*
*登録編集削除、DBからの抽出
*/
function sqlCreate($form_data) {
    $start_day = $form_data['start_day'];
    $end_day = $form_data['end_day'];
    $schedule_title = $form_data['schedule_title'];
    $schedule_detail = $form_data['schedule_detail'];
    $id = $form_data['id'];
    $between_begin = $form_data['between_begin'];
    $between_end = $form_data['between_end'];
    $schedule_id = $_GET['id'];

    //UPDATEじゃないとき、そして予定のタイトルが空じゃないとき
    if (empty($form_data['id']) && ($form_data['schedule_title'] != null)) {

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
    elseif (isset($form_data['id']) && !isset($form_data['delete'])) {

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
    elseif ($form_data['delete'] == 'delete') {

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
$schedule_3months=<<<END
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

$schedule_sql=<<<END
    SELECT
         schedule_id, start_date, end_date, schedule_title, schedule_detail
     FROM
         cal_schedules
     WHERE
         schedule_id="$schedule_id"

     AND
         deleted_at
     IS
         null

END;

return array(
    'sql' => $sql,
    'schedule_3months' => $schedule_3months,
    'schedule_sql' => $schedule_sql
    );
}

/*
 *エスケープ処理
 */
function sqlEscape($connect_db, $sql_create) {
    $db = $connect_db['db'];
    $return = array(
        'sql' => mysqli_real_escape_string($db, $sql_create['sql']),
        'schedule_3months' => mysqli_real_escape_string($db, $sql_create['schedules_3months']),
        'schedule_sql' => mysqli_real_escape_string($db, $sql_create['schedule_sql'])
    );
    return $return;
}


/*
*SQL実行
*/
function sqlResult($form_data, $connect_db, /*$sql_create*/$sql_escape) {
    $db = $connect_db['db'];
    

    //SQL実行
    if (isset($form_data['start_day']) && !empty($sql_escape['sql'])) {
        $insert_or_update = mysqli_query($db, $sql_escape['sql']);
    }
    if ($result = mysqli_query($db, $sql_escape['schedule_3months'])) {
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            list($schedule_year, $schedule_month, $schedule_day) = explode('-', date('Y-m-j',strtotime($row['start_date'])));
            list($end_schedule_year, $end_schedule_month, $end_schedule_day) = explode('-', date('Y-m-j',strtotime($row['end_date'])));
            $schedules_3months[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
            $schedules_3months[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
            if ($row['start_date'] != $row['end_date']) {
                for ($i=$schedule_day; $i<=$end_schedule_day; $i++) {
                    $schedules_3months[$schedule_year][$schedule_month][$i][$row['schedule_id']]['title'] = $row['schedule_title'];
                }
            }
        }
        mysqli_free_result($result);
    }
    if ($result2 = mysqli_query($db, $sql_escape['schedule_sql'])) {
        while ($row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
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
        mysqli_free_result($result2);
    }
    mysqli_close($db);

    return array(
        'insert_or_update' => $insert_or_update,
        'schedules_3months' =>$schedules_3months,
        'schedules' => $schedules
        );
}
