let body = $('body');


window.execOnMounted.push(function() {
   if ($('.programs-list--auto-hide').length > 0) {
       let programsCount = $('.programs-list--auto-hide').find('.program').length;
       if (programsCount > 15) {
           $('.programs-list--auto-hide').css('height', $('.program').height() * 3 + 16).addClass('programs-list--hidden');
           $('.programs-list--auto-hide').append('<div class="programs-list__show-all"><a class="button">Показать все</a></div>');
       }
   }
});

$(body).on('click', '.programs-list__show-all .button', function() {
    $(this).parents('.programs-list__show-all').hide();
    $(this).parents('.programs-list--auto-hide').removeClass('programs-list--hidden').css('height', 'auto');
})
