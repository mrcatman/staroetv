import {showModal} from './modals';
let body = $('body');
$(body).on('click', '.button--delete-page', function() {
    if ($(this).data('id')) {
        $('input[name="page_id"]').val($(this).data('id'));
    }
    showModal('#delete_page');
});
