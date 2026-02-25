import { replaceHTML } from './replace-html';
import { initPlayer } from './player'

window.playlistLastIds = [];
window.playlistRecordsCache = {};

const body = $('body');
let currentPlaylistId;

const initPlaylist = () => {
    const playlist = $('.playlist');
    if ($(playlist).length > 0) {

        if ($(window).width() <= 768) {
            $(playlist).insertBefore('.record-page__comments')
        } else {
            const height = $('.record-page__player').height();
            $(playlist).css('height', `${height}px`);
        }

        if (!currentPlaylistId) {
            currentPlaylistId = $(playlist).data('current-id');
        }

        const recordId = $(playlist).data('current-id');
        let playlistItem = $(playlist).find(`.playlist__item[data-id=${recordId}]`);
        $(playlistItem).addClass('playlist__item--active');

        $('.playlist .box__inner').animate({
            scrollTop: $(playlistItem)[0].offsetTop
        }, 250);

        window.onRecordEnded = () => {
            setTimeout(() => {
                const next = $('.playlist__item--active').nextAll('.playlist__item').first();
                if ($(next).length) {
                    $(next).find('a').click();
                }
            }, 500);
        }

    }
}

const initPlaylistItem = (data) => {
    replaceHTML(data.html);
    document.title = `${data.record.title} - Старый Телевизор`;

    setTimeout(() => {
        initPlayer();
    }, 50);

    history.pushState(null, null, data.record.url);
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
    e.preventDefault();
    window.playlistLastIds.push(currentPlaylistId);

    const playlistItem = $(this).parents('.playlist__item');
    const id = $(playlistItem).data('id');
    loadPlaylistItem(id);

    $('.playlist__item').removeClass('playlist__item--active');
    $(playlistItem).addClass('playlist__item--active');

    $('.playlist .box__inner').animate({
        scrollTop: $(playlistItem)[0].offsetTop
    }, 250);

    if ($(window).width() <= 768) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(".record-page__player").offset().top
        }, 500);
    }
});


$(window).on('popstate', function() {
    if (window.playlistLastIds?.length) {
        const lastId = window.playlistLastIds.pop();

        setTimeout(() => {
            loadPlaylistItem(lastId);
        }, 100)
    }
})

window.execOnMounted.push(initPlaylist)
