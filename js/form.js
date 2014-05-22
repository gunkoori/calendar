$(function() {

    var ymd;
    var year;
    var month;
    var day;
    var years = [];
    var months = [];
    var a_click_href = 0;
    //予定をクリックしたときはフラグを1に
    $('table td.day_td a,table td.day_td').click(function(e) {
        //ポップアップ出ているときは消さない
        if($('.popup').css('display') != 'none' && a_click_href == 0) {
            return false;
        }
        //最後の/の手前を取得する
        var url = window.location.href.match(/.+\//)[0];
        if($(this).attr('href') &&  a_click_href == 0) {//クリックした予定のhrefを代入する
            a_click_href = $(this).attr('href');
            return true;
        }
        //リンクにオクトピが含まれている場合オクトピのリンクに飛べるように
        if(a_click_href.indexOf('aucfan.com/article/')!=-1){
            e.stopPropagation();
        } else {
            e.preventDefault();//「href="#"」は無効にしたいけれど、親にイベントをバブリングしたいとき
            $('#shadow').css('display', 'block');
        }

        //新規の予定
        if(a_click_href == 0) {
            var td_a = $(this).find('a');
            var parameter = url + 'schedule.php' + $(td_a[0]).attr('href');//0は日付のaのhref
        } else {
            var parameter = url + 'schedule.php' + a_click_href;//代入したhref
        }
        a_click_href = 0;

        $.ajax({
            type: 'POST',
            url: parameter,
            //ajax通信が成功した場合
            success: function(data, dataType) {
                $('.popup').html(data);
                $('.popup').slideToggle();//ポップアップの表示
            },
            //ajax通信が失敗した場合
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                error('Error : ', + errorThrown);
            }
        });
    });

    //ポップアップ以外の画面をクリックするとポップアップ消える
    $('#shadow').click(function() {
        if ($('.popup').css('display') == 'block') {
            $('#shadow').click(function() {
                $('.popup').fadeOut();
                $('#shadow').fadeOut();
                click_flg = 0;
            });
        }
    });
    //空のままフォーカス変更するとエラー出す
    $('#schedule_title').blur(function(){
        if ( $('#schedule_title').val() == 0 ) {
            $('#alert_schedule_title').css('display', 'block');
        } else {
            $('#alert_schedule_title').css('display', 'none');
        }
    });
    $('#schedule_detail').blur(function(){
        if ( $('#schedule_detail').val() == 0 ) {
            $('#alert_schedule_detail').css('display', 'block');
        } else {
            $('#alert_schedule_detail').css('display', 'none');
        }
    });

    //登録、更新ボタンを押したとき
    $('#btn-regist, #btn-update').click(function() {
        var params = {};
        var ret = true;
        var click_flg = 0;

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

        //連打対策
        if (ret == true && click_flg == 0) {//入力にエラーないとき
            click_flg = 1;
            if (click_flg == 1) {
                $(this).attr('disabled', 'disabled');
            }
        }


        //submit押したとき
        //フォームから値をまとめて取得
        /*var data = $('#popup_regist_form').serializeArray();
        //配列の中のvalueを取得
        for (var i=3; i<=12; i++) {
            data = data[i][value];
        }
        alert(data);
        console.debug(data);
        $.ajax({
            type: 'POST',
            url: 'post.php',
            data: data,

            //ajax通信が成功した場合
            success: function(data, dataType) {
                // alert(data);
                alert('!!!!!!!!!');
                $(this).attr('disabled', 'disabled');
                // $('#btn-regist').removeAttr('disabled');
            },
            //ajax通信が失敗した場合
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                error('Error : ', + errorThrown);
            }
        });*/


        return ret;
    });
});

