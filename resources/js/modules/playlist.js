let body = $('body');
import replaceDom from './replaceDom';

window.playlistLastIds = [];
window.playlistRecordsCache = {};

let currentPlaylistId = $('.playlist').data('current-id');
function initPlaylistItem(data) {
    $('.record-page__player').html(data.player_code);
    document.title = data.record.title.replace('<br>', ' ') + " - Старый Телевизор";
    $('.inner-page__header__title').html(data.record.title);
    $('.record-page__bottom').html(data.record_info);
    $('.record-page__description').html(data.record.description);
    $('.box--comments').replaceWith(data.comments);
    setTimeout(() => {
        window.initOwnPlayer();
    }, 50);
    let url = data.record.is_radio ? "/radio/" + data.record.id : "/video/" + data.record.id;
    history.pushState(null, null, url);
    window.initShare();

    currentPlaylistId = data.record.id;
}
function loadPlaylistItem(id) {
    if (window.playlistRecordsCache[id]) {
        initPlaylistItem(window.playlistRecordsCache[id]);
    } else {
        $.get('/records/playlist-ajax/' + id).done(res => {
            if (res.status) {
                window.playlistRecordsCache[id] = res.data;
                initPlaylistItem(res.data);
            } else {
                alert(res.text);
            }
        })
    }
}

$(body).on('click', '.playlist__item a', function(e) {
    window.playlistLastIds.push(currentPlaylistId);
    let playlistItem = $(this).parents('.playlist__item');
    let id = $(playlistItem).data('id');
    loadPlaylistItem(id);
    e.preventDefault();

    $('.playlist__item').removeClass('playlist__item--active');
    $(playlistItem).addClass('playlist__item--active');
    $('.playlist .box__inner').animate({
        scrollTop: $(playlistItem).offsetTop
    }, 250);
    if ($(window).width() <= 768) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(".record-page__player").offset().top
        }, 500);
    }
});

window.onpopstate = () => {
    if (window.playlistLastIds && window.playlistLastIds.length > 0) {
        let lastId = window.playlistLastIds.pop();
        setTimeout(() => {
            loadPlaylistItem(lastId);
        }, 100)
    }
};

window.execOnMounted.push(() => {
    let playlist = $('.playlist');
    if ($(playlist).length > 0) {
        if ($(window).width() <= 768) {
            $('.playlist').insertBefore('.record-page__comments')
        } else {
            let height = $('.inner-page__content').height() + $('.record-page__comments').height() + 16;
            $(playlist).css('height', height + 'px');
        }
        let recordId = $(playlist).data('current-id');
        let playlistItem = $(playlist).find('.playlist__item[data-id='+recordId+']');
        $(playlistItem).addClass('playlist__item--active');
        $('.playlist .box__inner').animate({
            scrollTop: $(playlistItem).offsetTop
        }, 250);

        window.onRecordEnded = () => {
            setTimeout(() => {
                let playlistItem = $('.playlist__item--active');
                let next = $($(playlistItem).nextAll('.playlist__item')[0]);
                if (next) {
                    $(next).find('a').click();
                }
            }, 500);
        }

    }
})
