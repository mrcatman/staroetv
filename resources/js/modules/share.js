window.initShare = () => {
    if ($('.share-buttons').length > 0) {
        $('.share-buttons').each(function() {
            let content = {};
            if ($(this).data('title')) {
                content.title = $(this).data('title');
            }
            if ($(this).data('url')) {
                content.url = $(this).data('url');
            }
            Ya.share2($(this)[0], {
                content
            });
        })
    }
};

window.execOnMounted.push(() => {
    window.initShare();
});
