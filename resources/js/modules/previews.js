const body = $('body');

$(body).on('mouseenter', '.record-item--with-preview', function(e) {
    const src = $(this).data('src');
    const cover = $(this).find('.record-item__cover');
    $(cover).append(`<video class="record-item__preview" autoplay muted loop style="visibility: hidden"><source src="${src}" type="video/mp4" /></video>`);
    const preview = $(this).find('.record-item__preview');
    $(preview).on('loadedmetadata', function() {
        $(this).css('visibility', 'visible');
    })
});

$(body).on('mouseleave', '.record-item--with-preview', function(e) {
    $(this).find('.record-item__preview').remove();
});
