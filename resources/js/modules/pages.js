import {showModal} from './modals';
let body = $('body');
$(body).on('click', '.button--delete-page', function() {
    if ($(this).data('id')) {
        $('#delete_page input[name="id"]').val($(this).data('id'));
    }
    showModal('#delete_page');
});
