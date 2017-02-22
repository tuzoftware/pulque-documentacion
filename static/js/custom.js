$(function () {
    $('.aj-nav').click(function (e) {
        e.preventDefault();
        $(this).parent().siblings().find('ul').slideUp();
        $(this).next().slideToggle();
    });
});

//Fix GitHub Ribbon overlapping Scrollbar
var t = $('#github-ribbon');
var a = $('article');
if (t[0] && a[0] && a[0].scrollHeight > $('.right-column').height()) t[0].style.right = '16px';

//Toggle Code Block Visibility
function toggleCodeBlocks() {
    var t = localStorage.getItem("toggleCodeStats")
    t = (t + 1) % 3;
    localStorage.setItem("toggleCodeStats", t);
    var a = $('.content-page article');
    var c = a.children().filter('pre');
    var d = $('.right-column');
    if (d.hasClass('float-view')) {
        d.removeClass('float-view');
        $('#toggleCodeBlockBtn')[0].innerHTML = "Hide Code Blocks";
    } else {
        if (c.hasClass('hidden')) {
            d.addClass('float-view');
            c.removeClass('hidden');
            $('#toggleCodeBlockBtn')[0].innerHTML = "Show Code Blocks Inline";
        } else {
            c.addClass('hidden');
            $('#toggleCodeBlockBtn')[0].innerHTML = "Show Code Blocks";
        }
    }
}

if (localStorage.getItem("toggleCodeStats") >= 0) {
    var t = localStorage.getItem("toggleCodeStats");
    if (t == 1) {
        toggleCodeBlocks();
        localStorage.setItem("toggleCodeStats", 1);
    }
    if (t == 2) {
        toggleCodeBlocks();
        toggleCodeBlocks();
        localStorage.setItem("toggleCodeStats", 2);
    }
} else {
    localStorage.setItem("toggleCodeStats", 0);
}
