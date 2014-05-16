$(function() {
    var ymd;
    var year;
    var month;
    var day;
    var years = [];
    var months = [];
    $('table td').click(function(e) {
        e.preventDefault();//「href="#"」は無効にしたいけれど、親にイベントをバブリングしたいとき
        // 日付が入っていないセルはid取得しない
        if (!$(this).children('span')) {
            return false;
        }
        //ポップアップ表示時、背景を暗くする
        $('#shadow').css('display', 'block');

        //ポップアップ出ているときは消さない
        if($('.popup').css('display') != 'none') {
            return false;
        }

        $.ajax({
            type: 'POST',
            url: 'http://kensyu.aucfan.com/schedule.php'+$(this).find('a').attr('href'),

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

        //子要素の取得
        ymd = $(this).find('span').attr('id').split('-');// id取得
        year = ymd[0];
        month = ymd[1];
        day = ymd[2];
    });

    //ポップアップ以外の画面をクリックするとポップアップ消える
    $('#shadow').click(function() {
        if ($('.popup').css('display') == 'block') {
            $('#shadow').click(function() {
                $('.popup').fadeOut();
                $('#shadow').fadeOut();
            });
        }
    });

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
