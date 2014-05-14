<?php
require_once 'database.php';
require_once 'function.php';



// $form_validate = formValidate($form_data);


/*
 *バリデート
 */
function formValidate($form_data) {
    //エラー（入力漏れがあった）場合は受け取る
    $error_year = $_COOKIE['error_year'];
    $error_month = $_COOKIE['error_month'];
    $error_day = $_COOKIE['error_day'];
    $error_id = '';
    if (isset($_COOKIE['error_id'])) {
        $error_id = '&id='.$_COOKIE['error_id'];
    }

    if ($_GET['status'] == 'again') {
    if ($form_data['start_hour'] == '' || $form_data['start_min'] == '' || $form_data['end_hour'] == '' || $form_data['end_min'] == '') {
        // setcookie('error_hour', '時間は必須です', time()+1);
        $_SESSION['error_hour'] = '時間は必須です';
    }
    if ($form_data['start_ym'] == '' || $form_data['start_day'] == '' || $form_data['end_ym'] == '' || $form_data['end_day'] == '') {
        // setcookie('ymd', '年月日は必須です', time()+1);
        $_SESSION['error_ymd'] = '年月日は必須です';
    }
    if ($form_data['schedule_title'] == '') {
        // setcookie('schedule_title', 'タイトルは必須です', time()+1);
        $_SESSION['error_schedule_title'] = 'タイトルは必須です';
    }
    if ($form_data['schedule_detail'] == '') {
        // setcookie('schedule_detail', '詳細は必須です', time()+1);
        $_SESSION['error_schedule_detail'] = '詳細は必須です';
    }
    if (strtotime($form_data['start_day']) > strtotime($form_data['end_day'])) {
        // setcookie('error_compare_date', '開始日時が終了日時より遅く設定されています', time()+1);
        $_SESSION['error_compare_date'] = '開始日時が終了日時より遅く設定されています';
    }
    // $_SESSION['count'] = 0;
    }

    //無効な日付かチェックする ex.)2月３１日には登録できない
    $explode_start_ym = explode('-', $form_data['start_ym']);
    $explode_end_ym = explode('-', $form_data['end_ym']);
    $check_start_ym = checkdate($explode_start_ym[1], intval($form_data['start_day']), intval($explode_start_ym[0]));
    $check_end_ym = checkdate($explode_end_ym[1], intval($form_data['end_day']), intval($explode_end_ym[0]));
    if ($check_start_ym == false || $check_end_ym == false) {
        // setcookie('date_error', '無効な日付です', time()+1);
        $_SESSION['date_error'] = '無効な日付です';
    }
    //再度入力フォームに戻す
    if (isset($form_data['insert']) || isset($form_data['update'])) {
        if (empty($form_data['start_ym']) || empty($form_data['start_day']) || empty($form_data['start_hour']) || empty($form_data['start_min']) ||empty($form_data['end_ym']) || empty($form_data['end_day']) || empty($form_data['end_hour']) || empty($form_data['end_min']) || empty($form_data['schedule_title']) || empty($form_data['schedule_detail']) || $check_start_ym == false || $check_end_ym == false || (strtotime($start_day) > strtotime($end_day))) {

            // $_SESSION['count']++;
            $return = header("Location: http://kensyu.aucfan.com/schedule.php?year=".$error_year."&month=".$error_month."&day=".$error_day.$error_id.'&status=error');
            exit;
        }
    }
    else {
        $return = 'true';
    }
    return $return;
}