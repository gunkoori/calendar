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


    //submit押したとき
    /*$('#btn-regist').click(function() {
        // $(this).attr('disabled', 'true');
        // alert('!!!!');
        //フォームから値をまとめて取得
        var data = $('#popup_regist_form').serializeArray();
        //配列の中のvalueを取得
        for (var i=3; i<=12; i++) {
            data = data[i][value];
        }
        alert(data);
        console.debug(data);
        $.ajax({
            type: 'POST',
            url: 'test.php',
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
        });
        //サブミット後ページをリロードしない
        return false;
    });*/

});
