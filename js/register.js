$(function() {
    var ymd;
    var year;
    var month;
    var day;
    var years = [];
    var months = [];
    var click_flg = 0;
    //予定をクリックしたときはフラグを1に
    $('a.schedule').click(function() {
        click_flg = 1;
    });
    $('table td').click(function(e) {
        e.preventDefault();//「href="#"」は無効にしたいけれど、親にイベントをバブリングしたいとき
        // 日付が入っていないセルはid取得しない
        console.debug($(this).find('a'));
        if ($(this).find('a').length == 0) {
            // $('#shadow').css('display', 'none');
            return false;
        }
        //ポップアップ表示時、背景を暗くする
        $('#shadow').css('display', 'block');
        //ポップアップ出ているときは消さない
        if($('.popup').css('display') != 'none') {
            return false;
        }
        //現在のURL
        var url = window.location;
        //新規の予定
        if(click_flg != 1) {
            var parameter = url + 'schedule.php' + $(this).find('a').attr('href');
            click_flg = 0;
        }
        //カレンダーの予定をクリックしたとき
        if (click_flg == 1 && (0 < $(this).find('.schedule').attr('href').length)) {
            var parameter = url + 'schedule.php' + $(this).find('.schedule').attr('href');
        }
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

});
