const body = $('body')

$(body).on('click', '.actions-logs__item__show-changes', function () {
    $(this).hide();
    $(this).parent().find('.actions-logs__item__changes').show();
});
