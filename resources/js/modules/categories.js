const body = $('body');
window.execOnMounted.push(function() {
    $('.categories-list').each(function () {
        if ($(this).hasClass('categories-list--multiline') && !$(this).hasClass('categories-list--multiline-opened')) {
            initMultiline($(this));
            return;
        }
        initSlider($(this));
    });
});

const initMultiline = (el) => {
    const linesCount = 3;

    const linesTop = [];

    let isCollapsed = false;
    $(el).children().each(function () {
        const top = $(this).position().top;
        if (!linesTop.includes(top)) {
            linesTop.push(top);
        }

        if (linesTop.length > linesCount) {
            isCollapsed = true;

            $(el).addClass('categories-list--multiline-collapse');
            $(this).addClass('category--collapsed');
        }
    });

    if (isCollapsed) {
        $(el).append(`
            <a class="button categories-list__show-all">Показать все</a>
        `);
    }
}

const initSlider = (el) => {
    const slider = $(el)[0];

    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousewheel', (e) => {
        slider.scrollLeft = slider.scrollLeft + e.deltaY / 2;
        scrollLeft = slider.scrollLeft;
        e.preventDefault();
    });

    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.classList.add('active');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });
    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.classList.remove('active');
    });
    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.classList.remove('active');
    });
    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 1.25;
        slider.scrollLeft = scrollLeft - walk;
    });
}

$(body).on('click', '.categories-list__show-all', function () {
    $(this).parents('.categories-list').removeClass('categories-list--multiline-collapse');
    $(this).remove();
});
