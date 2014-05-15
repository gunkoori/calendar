$(function() {
    $('table td').click(function(e) {
        if (!$(this).children('span')) {// 日付が入っていないセルはid取得しない
            return false;
        }
        $('.popup').slideToggle().show();
        console.debug($(this).find('span').attr('id'));// id取得
        e.preventDefault();//「href="#"」は無効にしたいけれど、親にイベントをバブリングしたいとき
    });
    $('.popup').click(function() {
        $(this).fadeOut();
    });
});
