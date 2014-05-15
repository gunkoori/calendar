$(function() {
    $('#btn-regist').click(function() {
        var params = {};
        var ret = true;

        //フォームの値を取得する
        params.schedule_title = $('#schedule_title').val();
        params.schedule_detail = $('#schedule_detail').val();

        //空ならばエラー文を表示させる
        if (params.schedule_title == '') {
            $('#alert_schedule_title').css('display', 'block');
            ret = false;
        } else {
            $('#alert_schedule_title').css('display', 'none');
        }
        if (params.schedule_detail == '') {
            $('#alert_schedule_detail').css('display', 'block');
            ret = false;
        } else {
            $('#alert_schedule_detail').css('display', 'none');
        }
        return ret;
    });
});

