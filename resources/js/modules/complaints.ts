const body = $('body');

const formId = 'record_complaint';
const typeSelector = `#${formId} input[name=type]`;
const contactSelector = `#${formId} input[name=contact]`;

const onTypeChange = function () {
    const isPlayerNotWorkingIssue = $(`${typeSelector}:checked`).val() == 'player-not-working';
    const contact = $(contactSelector).closest('.input-container');
    isPlayerNotWorkingIssue ? $(contact).hide() : $(contact).show();
}

$(body).on('change', typeSelector, onTypeChange);
$(body).on('reset', `#${formId} form`, () => {
    setTimeout(() => {
        onTypeChange();
    }, 1);
});
