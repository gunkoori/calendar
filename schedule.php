<?php
$year = $_GET['year'];
$month = $_GET['month'];
$day = $_GET['day'];

?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title></title>
<link href="calendar.css" rel="stylesheet">
</head>
<body>
<h3>スケジュール登録</h3>
<div id="schedule_form">
    <form method="post" action="http://kensyu.aucfan.com/">
        <input type="text" id="start_day" name="start_day" value="<?php echo $year.'-'.$month.'-'.$day?>" />〜
        <input type="text" id="end_day" name="end_day" value="<?php echo $year.'-'.$month.'-'.$day?>" /><br />
        タイトル：<input type="text" id="schedule_title" name="schedule_title" /><br />
        詳細：<input type="text" id="schedule_detail" name="schedule_detail" /><br />
        <input type="submit" value="登録する" />
    </form>
</div>
</body>
</html>