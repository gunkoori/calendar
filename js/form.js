$(function() {
    $('#btn-regist, #btn-update').click(function() {
        var params = {};
        var ret = true;

        //フォームの値を取得する
        params.start_ym = $('#start_ym').val();
        params.start_year = params.start_ym.split('-')[0];
        params.start_month = params.start_ym.split('-')[1];
        params.start_day = $('#start_day').val();
        params.start_hour = $('#start_hour').val();
        params.start_min = $('#start_min').val();

        params.end_ym = $('#end_ym').val();
        params.end_year = params.end_ym.split('-')[0];
        params.end_month = params.end_ym.split('-')[1];
        params.end_day = $('#end_day').val();
        params.end_hour = $('#end_hour').val();
        params.end_min = $('#end_min').val();

        params.schedule_title = $('#schedule_title').val();
        params.schedule_detail = $('#schedule_detail').val();

        //日付の正当性をチェック
        var di = new Date(params.start_year,  params.start_month-1, params.start_day);//月は0〜11で返ってくる
        //もし日付がその月の最大日を超えている場合は翌月に繰り越すのでdi.getMonth() == params.start_month-1 にならない
        if (di.getFullYear() == params.start_year && di.getMonth() == params.start_month-1 && di.getDate() == params.start_day) {
            $('#alert_start_date').css('display', 'none');
        } else {
            $('#alert_start_date').css('display', 'block');
            ret = false;
        }
        var di = new Date(params.end_year,  params.end_month-1, params.end_day);//月は0〜11で返ってくる
        //もし日付がその月の最大日を超えている場合は翌月に繰り越すのでdi.getMonth() == params.end_month-1にならない
        if (di.getFullYear() == params.end_year && di.getMonth() == params.end_month-1 && di.getDate() == params.end_day) {
            $('#alert_end_date').css('display', 'none');
        } else {
            $('#alert_end_date').css('display', 'block');
            ret = false;
        }

        //開始日時が終了日時よりも後になっていないかチェック
        if ( (params.start_year + '/' +  params.start_month + '/' + params.start_day + '/' + params.start_hour + ':' + params.start_min) >  (params.end_year + '/' +  params.end_month + '/' + params.end_day + '/' + params.end_hour + ':' + params.end_min) ) {
             $('#alert_error_date').css('display', 'block');
        } else {
             $('#alert_error_date').css('display', 'none');
        }

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

