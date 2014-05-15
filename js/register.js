$(function() {
    $('table td').click(function() {
        $('.popup').slideToggle();
        e.preventDefault();//「href="#"」は無効にしたいけれど、親にイベントをバブリングしたいとき
    });
    $('.popup').click(function() {
        $(this).fadeOut();
    });
});

$(function() {
    $('table td a').click(function(data) {
        alert('hahaha');
        // $($(this).attr('href')).show();
        return false;

    });
    console.debug();
});