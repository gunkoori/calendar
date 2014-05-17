<?php
session_start();
require_once 'function.php';
require_once 'unset_session.php';

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
    $db = @mysqli_connect($host, $user, $password, $database);

    // 接続状況をチェック
    if (mysqli_connect_errno()) {
        $return = false;
    }
    return array(
        'db' => $db,
        'return' => $return
        );
}

function splitYM($ym) {
    return explode('-', $ym);
}

/*
*フォームからPOSTされたデータ
*/
function formData($make_calendar) {
    $post_data = $_POST;
    //ワンタイムトークン
    $token = $post_data['token'];
    //開始時間と終了時間
    $start_hour = $post_data['start_hour'];
    $start_min = $post_data['start_min'];
    $end_hour = $post_data['end_hour'];
    $end_min = $post_data['end_min'];
    $start_time = $start_hour.':'.$start_min.':00';
    $end_time = $end_hour.':'.$end_min.':00';
    //開始日と終了日
    $start_ym = $post_data['start_ym'];
    $start_day = $post_data['start_day'];
    $end_ym = $post_data['end_ym'];
    $end_day = $post_data['end_day'];
    $start_timestamp = $start_ym.'-'.$post_data['start_day'].' '.$start_time;
    $end_timestamp = $end_ym.'-'.$end_day.' '.$end_time;

    $split_ym = splitYM($start_ym);
    $year = $split_ym[0];
    $month = $split_ym[1];
    $end_split_ym = splitYM($end_ym);
    $end_year = $end_split_ym[0];
    $end_month = $end_split_ym[1];
    $day = $post_data['day'];
    //予定のタイトルと詳細
    $schedule_title = $post_data['schedule_title'];
    $schedule_detail = $post_data['schedule_detail'];
    $id = $post_data['schedule_id'];
    $delete = $post_data['delete'];
    $between_begin = $make_calendar['calendars'][1].'-01 00:00:01';
    $between_end = $make_calendar['calendars'][3].'-'.$make_calendar['end_days'][3].' 23:59:59';
    if (isset($post_data['insert'])) {
        $insert = 'insert';
    }
    if (isset($post_data['update'])) {
        $update = 'update';
    }
    return array(
        'year' => $year,
        'month' => $month,
        'end_year' => $end_year,
        'end_month' => $end_month,
        'day' => $day,
        'token' => $token,
        'start_hour' => $start_hour,
        'start_min' => $start_min,
        'end_hour' => $end_hour,
        'end_min' => $end_min,
        'start_ym' => $start_ym,
        'start_day' => $start_day,
        'end_ym' => $end_ym,
        'end_day' => $end_day,
        'start_time' => $start_time,
        'start_timestamp' => $start_timestamp,
        'end_time' => $end_time,
        'end_timestamp' => $end_timestamp,
        'start_day' => $start_day,
        'end_day' => $end_day,
        'schedule_title' => $schedule_title,
        'schedule_detail' => $schedule_detail,
        'id' => $id,
        'delete' => $delete,
        'schedule_id' => $schedule_id,
        'between_begin' => $between_begin,
        'between_end' => $between_end,
        'insert' => $insert,
        'update' => $update
        );
}

/*
 *バリデート
 */
function formValidate() {
    $session_form_data = $_SESSION['form_data'];
    $year = $session_form_data['year'];
    $month = $session_form_data['month'];
    $day = $session_form_data['day'];

    $start_time = date('H:i:s', strtotime($session_form_data['start_hour'].':'.$session_form_data['start_min'].':00'));
    $start_date = date('Y-m-d H:i:s', strtotime($session_form_data['start_ym'].'-'.$session_form_data['start_day'].' '.$start_time));

    $end_time = date('H:i:s', strtotime($session_form_data['end_hour'].':'.$session_form_data['end_min'].':00'));
    $end_date = date('Y-m-d H:i:s', strtotime($session_form_data['end_ym'].'-'.$session_form_data['end_day'].' '.$end_time));

    $error_schedule_title = '';
    $error_schedule_detail = '';
    $error_compare_date = '';

    // idが空じゃないとき
    if ($_POST) {
        //タイトルが空のとき
        if (empty($session_form_data['schedule_title'])) {
            $error_schedule_title = 'タイトルは必須です';// エラーメッセージ
            $keep_detail[$year][$month][$day] = $session_form_data['schedule_detail'];// 値を保持させるために代入
        }
        //詳細が空のとき
        if (empty($session_form_data['schedule_detail'])) {
            $error_schedule_detail = '詳細は必須です';// エラーメッセージ
            $keep_title[$year][$month][$day] = $session_form_data['schedule_title'];// 値を保持させるために代入
        }
        //開始時間が終了時間よりも遅いとき
        if (strtotime($start_date) > strtotime($end_date)) {
            $error_compare_date = '開始日時が終了日時より遅く設定されています';
        }
        //無効な日付かチェックする ex.)2月３１日には登録できない
        $explode_start_ym = explode('-', $session_form_data['start_ym']);
        $explode_end_ym = explode('-', $session_form_data['end_ym']);
        $check_start_ym = checkdate($explode_start_ym[1], intval($session_form_data['start_day']), intval($explode_start_ym[0]));
        $check_end_ym = checkdate($explode_end_ym[1], intval($session_form_data['end_day']), intval($explode_end_ym[0]));
        if ($check_start_ym == false || $check_end_ym == false) {
            $error_date = '無効な日付です';
        }

        $_SESSION['error'] = array(
            'error_schedule_title' => $error_schedule_title,
            'error_schedule_detail' => $error_schedule_detail,
            'error_compare_date' => $error_compare_date,
            'error_date' => $error_date,
            'keep_detail' => $keep_detail,
            'keep_title' => $keep_title
        );
        // １つでも文章が入っていればfalse
        foreach ($_SESSION['error'] as $is_error) {
            if ($is_error) {
                return false;
            }
        }
    }
    // $_POSTがないとき＝新規登録時はtrue
    return true;
}


/*
*formData()のエスケープ
*/
function escapeFormdata($connect_db, $form_data) {
    if ($connect_db['return'] == true) {//接続に成功しているとき
        $db = $connect_db['db'];
        $escape_value = array();
        foreach ($form_data as $name => $data) {
            $escape_value[$name] = mysqli_real_escape_string($db, $data);
        }
    }
    return $escape_value;
}

/*
*登録編集削除、DBからの抽出
*エスケープした$escape_formdataを用いている
*/
function sqlCreate($escape_formdata, $check_token) {
    $start_day = $escape_formdata['start_timestamp'];
    $end_day = $escape_formdata['end_timestamp'];
    $schedule_title = $escape_formdata['schedule_title'];
    $schedule_detail = $escape_formdata['schedule_detail'];
    $id = $escape_formdata['id'];
    $between_begin = $escape_formdata['between_begin'];
    $between_end = $escape_formdata['between_end'];
    $schedule_id = $_GET['id'];

    //UPDATEじゃないとき、そして予定のタイトルが空じゃないとき
    if ($check_token == true && empty($id) && $escape_formdata['insert'] == 'insert') {

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
    elseif ($check_token == true && isset($id) && $escape_formdata['update'] == 'update') {

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
    elseif ($check_token == true && $escape_formdata['delete'] == 'delete') {

$delete=<<<END
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
    'delete' => $delete,
    'schedule_3months' => $schedule_3months,
    'schedule_sql' => $schedule_sql
    );
}


/*
*SQL実行
*/
function sqlResult($escape_formdata, $connect_db, $sql_create) {
    if ($connect_db['return'] == true) {
        $db = $connect_db['db'];
        //SQL実行
        if (isset($sql_create['sql'])) {
            $insert_or_update = mysqli_query($db, $sql_create['sql']);
        }
        if (isset($sql_create['delete'])) {
            $delete = mysqli_query($db, $sql_create['delete']);
        }
        if (isset($sql_create['schedule_3months'])) {
            if ($result = mysqli_query($db, $sql_create['schedule_3months'])) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    list($schedule_year, $schedule_month, $schedule_day) = explode('-', date('Y-m-j',strtotime($row['start_date'])));
                    list($end_schedule_year, $end_schedule_month, $end_schedule_day) = explode('-', date('Y-m-j',strtotime($row['end_date'])));
                    $schedules_months[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
                    $schedules_months[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
                    if ($row['start_date'] != $row['end_date']) {
                        for ($i=$schedule_day; $i<=$end_schedule_day; $i++) {
                            $schedules_months[$schedule_year][$schedule_month][$i][$row['schedule_id']]['title'] = $row['schedule_title'];
                        }
                    }
                }
                mysqli_free_result($result);
            }
        }
        if (isset($sql_create['schedule_sql'])) {
            if ($result2 = mysqli_query($db, $sql_create['schedule_sql'])) {
                while ($row = mysqli_fetch_array($result2, MYSQLI_ASSOC)) {
                    list($schedule_year, $schedule_month, $schedule_day) = explode('-', date('Y-m-j',strtotime($row['start_date'])));
                    list($end_schedule_year, $end_schedule_month, $end_schedule_day) = explode('-', date('Y-m-j',strtotime($row['end_date'])));
                    $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['title'] = $row['schedule_title'];
                    $schedules[$schedule_year][$schedule_month][$schedule_day][$row['schedule_id']]['detail'] = $row['schedule_detail'];
                    if ($row['start_date'] != $row['end_date']) {
                        for ($i=$schedule_day; $i<=$end_schedule_day; $i++) {
                            $schedules[$schedule_year][$schedule_month][$i][$row['schedule_id']]['title'] = $row['schedule_title'];
                            $schedules[$schedule_year][$schedule_month][$i][$row['schedule_id']]['detail'] = $row['schedule_detail'];
                        }
                    }
                }
                mysqli_free_result($result2);
            }
    }
    mysqli_close($db);
}
    return array(
        'insert_or_update' => $insert_or_update,
        'delete' => $delete,
        'schedules_months' =>$schedules_months,
        'schedules' => $schedules
        );
}
