jQuery(document).ready(function ($) {
    $('.wpfm-content-group').hide();
    var wpfmActiveTab = '';
    if (typeof(localStorage) != 'undefined' ) {
        wpfmActiveTab = localStorage.getItem("wpfmActiveTab");
    }
    if (wpfmActiveTab != '' && $(wpfmActiveTab).length ) {
        $(wpfmActiveTab).fadeIn();
    } else {
        $('.wpfm-content-group:first').fadeIn();
    }
    $('.nav-tab').removeClass('nav-tab-active');
    if (wpfmActiveTab != '' && $(`[href="${wpfmActiveTab}"]`).length ) {
        $(`[href="${wpfmActiveTab}"]`).addClass('nav-tab-active');
    } else {
        $('.nav-tab-wrap a:first').addClass('nav-tab-active');
    }
    $('.nav-tab-wrap a').click(function(evt) {
        $('.nav-tab-wrap a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
        var clicked_group = $(this).attr('href');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("wpfmActiveTab", $(this).attr('href'));
        }
        $('.wpfm-content-group').hide();
        $(clicked_group).fadeIn();
        evt.preventDefault();
    });
});
