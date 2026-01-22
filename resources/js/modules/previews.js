const body = $('body');

const timeouts = {};
$(body).on('mouseenter', '.record-item--with-preview', function(e) {
    timeouts[$(this).data('id')] = setTimeout(() => {
        const src = $(this).data('src');
        const cover = $(this).find('.record-item__cover');
        $(cover).append(`<video class="record-item__preview" autoplay muted loop style="visibility: hidden"><source src="${src}" type="video/mp4" /></video>`);
        const preview = $(this).find('.record-item__preview');
        $(preview).on('loadedmetadata', function() {
            $(this).css('visibility', 'visible');
        })
    }, 500);
});

$(body).on('mouseleave', '.record-item--with-preview', function(e) {
    clearTimeout(timeouts[$(this).data('id')]);
    $(this).find('.record-item__preview').remove();
});
