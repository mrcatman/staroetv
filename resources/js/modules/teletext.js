let currentPage;
let pageSelect;
let pages = [];
let pushState = true;

const loadPage = (page) => {
    currentPage = page;

    $('.record-page__player-container').append(' <div class="form__preloader form__preloader--dark"><img src="/img/ajax.gif"></div>');
    const url = `/teletext/${$('input[name=teletext_id]').val()}/?page=${page}&ajax=1`;
    $.get(url).then(res => {
        $('.record-page__player-container').find('.form__preloader').remove();
        $('.teletext').html(res);

        const index = pages.indexOf(currentPage);

        const prevPage = index > 0 ? pages[index - 1] : pages[pages.length - 1];
        const nextPage = index < pages.length - 1 ? pages[index + 1] : pages[0];

        $('.teletext-controls__prev').attr('href', `?page=${prevPage}`);
        $('.teletext-controls__next').attr('href', `?page=${nextPage}`);

        initSubpages();

        if (pushState) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.history.pushState(null, '', url.toString());
        }

        pushState = true;
    })
}

const initSubpages = () => {
    const subpages = $('.record-page__player-container .subpage');
    $(subpages).hide();
    $(subpages).first().show();

    const container =  $('.teletext-controls__subpages');
    const list =  $('.teletext-controls__subpages__list');

    $(list).html('');
    if (subpages.length > 1) {
        for (let i = 1; i <= subpages.length; i++) {
            $(list).append(`<a class="teletext-controls__subpage ${i === 1 ? 'teletext-controls__subpage--active'  : ''}">${i}</a>`)
        }
        $(container).show();
    } else {
        $(container).hide();
    }
}

const onKeydown = (e) => {
    if ($(document.activeElement).is('input') || $(document.activeElement).is('textarea')) {
        return;
    }
    if (e.key === 'ArrowLeft' || e.key === 'a') {
            $('.teletext-controls__prev').click();
            e.preventDefault();
        }

    if (e.key === 'ArrowRight' || e.key === 'd') {
        $('.teletext-controls__next').click();
        e.preventDefault();
    }
}

const initTeletext = () => {
    $(document).off('keydown', onKeydown);

    if (!$('.teletext-controls').length) {
        return;
    }

    pageSelect = $('.teletext-controls__select');

    pages = [...$(pageSelect)[0].options].map(o => o.value);
    currentPage = $(pageSelect).val();
    initSubpages();

    $(pageSelect).select2().on('change', function() {
        loadPage($(this).val());
    });

    $('body').on('click', '.teletext-controls__subpage', function() {
        $('.teletext-controls__subpage').removeClass('teletext-controls__subpage--active');
        $(this).addClass('teletext-controls__subpage--active');

        const subpages = $('.record-page__player-container .subpage');
        $(subpages).hide();
        $(subpages).eq(parseInt($(this).text()) - 1).show();
    })

    $('.teletext-controls__prev, .teletext-controls__next, .teletext a').on('click', function(e) {
        e.preventDefault();

        const page = $(this).attr('href').split('?page=')[1];
        $(pageSelect).val(page).trigger('change')
    })

    $(document).on('keydown', onKeydown);

    const url = new URL(window.location.href);
    if (url.searchParams.get('update_cover') && !$('input[name=cover_id]').val()) {
        updateCover();
    }
}

$(window).on('popstate', function() {
    const url = new URL(window.location.href);
    if (url.pathname.startsWith('/teletext/') && url.searchParams.get('page')) {
        pushState = false;
        $(pageSelect).val(url.searchParams.get('page')).trigger('change')
    }
});

const updateCover = () => {
    const callback = () => {
        html2canvas($('.teletext')[0]).then(function(canvas) {
            canvas.toBlob((blob) => {

                const file = new File([blob], "screenshot.jpg", { type: "image/jpeg" });
                const fd = new FormData();
                fd.append('picture', file);
                fd.append('channel_id', $('input[name=channel_id]').val());
                $.ajax({
                    url: route('pictures.upload'),
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: ({ data }) => {
                       $.post(route('teletext.edit', $('input[name=teletext_id]').val()), {
                           cover_id: data.picture.id
                       })
                    },
                });
            }, 'image/jpeg');
        });
    }

    const script = document.createElement("script");
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    script.addEventListener('load', callback);
    document.getElementsByTagName("head")[0].appendChild(script);
}

window.execOnMounted.push(initTeletext);
