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

        var url = window.location;//現在のURL
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
    $('#btn-regist').click(function() {
        //フォームから値をまとめて取得
        var data = $('#popup_regist_form').serializeArray();
        //配列の中のvalueを取得
        for (var i=3; i<=12; i++) {
            data = data[i][value];
        }
        $.ajax({
            type: 'POST',
            url: 'database.php',
            data: data,

            //ajax通信が成功した場合
            success: function(data, dataType) {
                alert(data);
            },
            //ajax通信が失敗した場合
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                error('Error : ', + errorThrown);
            }
        });
        //サブミット後ページをリロードしない
        return false;
    });

    //何ヶ月分表示するか変更
    $('#btn_change_month').click(function() {
        var change_month = $("select[name='change_month']").val();
        $.ajax({
            type: 'POST',
            url: 'function.php',
            data: change_month,

            //ajax通信が成功した場合
            success: function(data, dataType) {
                alert(data);
            },
            //ajax通信が失敗した場合
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                error('Error : ', + errorThrown);
            }
        });
    });

});
