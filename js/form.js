$(function() {
    $('#btn-regist').click(function() {
        var params = {};
        var ret = true;

        //フォームの値を取得する
        params.schedule_title = $('#schedule_title').val();
        params.schedule_detail = $('#schedule_detail').val();

        $('#alert_schedule_title').text('');
        $('#alert_schedule_detail').text('');
        //空ならばエラー文を表示させる
        if (params.schedule_title == '') {
            $('#alert_schedule_title').text('タイトルを入力してください');
            ret = false;
        } else {
            $('#alert_schedule_title').text('');
        }
        if (params.schedule_detail == '') {
            $('#alert_schedule_detail').text('詳細を入力してください');
            ret = false;
        } else {
            $('#alert_schedule_detail').text('');
        }
        return ret;
    });
});

