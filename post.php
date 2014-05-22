<?php
require_once 'function.php';
require_once 'database.php';

//DB接続
$connect_db = connectDB();

//カレンダー生成
$make_calendar = makeCalendar($display_count, $prev_month, $year_of_ym);

//フォームのデータ整形
$form_data = formData($make_calendar);


//POSTされたデータをSESSIONに保存
$_SESSION['form_data'] = $form_data;

//フォーム、バリデート
$is_form_valid = formValidate();

//フォームデータのエスケープ
$escape_formdata = escapeFormdata($connect_db, $form_data);

//POSTされたデータの受取り
$form_data = formData($make_calendar);

//ワンタイムトークンチェックする hiddenでPOSTしたトークンと$_SESSION['key']が一致するかチェック
if (isset($form_data['insert']) || isset($form_data['update']) || isset($form_data['delete'])) {
    $check_token = checkToken($form_data['token']);
}

//SESSIONのトークンを削除
if ($check_token === true) {
    unset($_SESSION['key']);
}

//連打された場合
if($check_token === false) {
    echo h('連打はやめましょう！！！！！！！１１１１１');
}

//SQL文の生成
//バリデート通ったとき
if ($is_form_valid === true && $check_token === true) {
    $sql_create = sqlCreate($escape_formdata, $check_token);
}

//INSERTまたはUPDATEのSQLがある場合、実行
if ($check_token === true && empty($_SESSION['error']['error_schedule_title']) && empty($_SESSION['error']['error_schedule_detail']) && isset($sql_create['sql'])) {
    $insert_update =  sqlResult($escape_formdata, $connect_db, $sql_create);
    $insert_update['insert_or_update'];
    unset($_SESSION['error']['keep_title']);
    unset($_SESSION['error']['keep_detail']);
    header('Location: ./');
    return;
}

//DELETEのSQLがある場合、deleted_atにNOW()を入れる
if ($check_token === true && isset($sql_create['delete'])) {
    $delete = sqlResult($escape_formdata, $connect_db, $sql_create);
    $delete['delete'];
    header('Location: ./');
    return;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<link href="calendar.css" rel="stylesheet">
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/form.js"></script>

</head>
<body>
<br /><a href="./">戻る</a>
</body>
</html>