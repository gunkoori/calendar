$(function() {
    $('#btn-regist').click(function() {
        var params = {};

        //フォームの値を取得する
        params.schedule_title = $('#schedule_title').val();
        params.schedule_detail = $('#schedule_detail').val();

        $('#alert_schedule_title').text('');
        $('#alert_schedule_detail').text('');
        //空ならばエラー文を表示させる
        if (params.schedule_title == '') {
            $('#alert_schedule_title').text('タイトルを入力してください');
            // return false;
        }
        if (params.schedule_detail == '') {
            $('#alert_schedule_detail').text('詳細を入力してください');
            return false;
        }
        console.debug(params.schedule_detail);
    });
});

