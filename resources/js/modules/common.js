window.execOnMounted.push(function() {
    if ($('#editor').length > 0){
        CKEDITOR.config.allowedContent = true;
        CKEDITOR.replace('editor');
    }
    let pathname = window.location.pathname;
    $('a').removeClass('link--active');
    $('a[href="'+pathname+'"]').addClass('link--active');
})
