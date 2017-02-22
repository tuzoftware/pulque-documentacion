// barra de menu siempre visible

var sticky_navigation = function(){
    var scroll_top = $(window).scrollTop();
     
    if (scroll_top > 80) { 
        $('.navbar').css({ 'position': 'fixed', 'top': 0 }).addClass('fadeo');
        $('body').css({ 'margin-top': '73px' });
    } else {
        $('.navbar').css({ 'position': 'relative' }).removeClass('fadeo'); 
        $('body').css({ 'margin-top': 0 });
    }
};

// check de barra cada que se mueve el scroll
 
$(window).scroll(function() {
     sticky_navigation();
});

$(document).ready(function() {
    sticky_navigation();

    $('.sube').children('ul').hide();

    $('.docs-show').on('click', function()
    {
        $('#documentation').toggleClass('nav-expanded');
    });
});