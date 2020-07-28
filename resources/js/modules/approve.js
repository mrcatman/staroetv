let body = $('body');
$(body).on('click', '*[data-approve]', function () {
   $.post('/' + $(this).data('approve') + '/approve', {id: $(this).data('approve-id')}).then(res => {
       if (res.status) {
           $(this).html(res.data.approved ? "Скрыть" : "Одобрить");
       } else {
           alert(res.text);
       }
   })
});
