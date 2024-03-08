let body = $('body');

$(document).ready(function() {
    return;
    if (localStorage['survey_sep_2020'] !== "1") {
      showModal('#survey');
    }
    $(body).on('click', '#survey a, #survey button, .modal-window[data-name="survey"] a, .modal-window[data-name="survey"] button, .modal-window[data-name="survey"] .modal-window__close', function() {
        localStorage['survey_sep_2020'] = "1";
        $('.modal-window[data-name=survey]').hide();
    })
});
