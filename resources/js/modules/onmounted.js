const onVueMounted = () => {
    if ($('#editor').length > 0){
        CKEDITOR.replace('editor');
    }
    let pathname = window.location.pathname;
    $('a').removeClass('link--active');
    $('a[href="'+pathname+'"]').addClass('link--active');
    let infoContainer = $('.user-page__info-container');
    if ($(infoContainer).length > 0) {
        let avatar = $('.user-page__avatar');
        if ($(avatar).length > 0) {
            let height =  $(infoContainer).height();
            $(avatar).css('height', height + 'px');
            let img = new Image();
            img.src = $(avatar).attr('src');
            img.onload = () => {
                $(infoContainer).hide();
                $(infoContainer).css('flex', 'auto').css('width', 'calc(100% - '+($(avatar).width())+'px)')
                $(infoContainer).show();
            };
        }
    }
    $('.comment__video-player, .forum-message__video-player').each(function() {
        let params = $(this).data('params');
        params = params.replace(/'/g, '"');
        params = JSON.parse(params);
        _uVideoPlayer(params, $(this).data('element'));
    });

    let usersAutocomplete = $('#users_autocomplete');
    if ($(usersAutocomplete).length > 0) {
        $(usersAutocomplete).select2({
            ajax: {
                method: 'POST',
                url: '/users/autocomplete',
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.data.users.map(user => {
                            return {
                                id: user.id,
                                text: user.username,
                            }
                        }),
                        pagination: {
                            more: data.data.users.length > 0
                        }
                    };
                },
            }
        });
    }
};

module.exports = onVueMounted;