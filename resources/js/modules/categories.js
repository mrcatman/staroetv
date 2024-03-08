window.execOnMounted.push(function() {
    $('.categories-list').each(function () {
        if ($(this).hasClass('categories-list--multiline')) {
            return;
        }
        let slider = $(this)[0];

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
    });
});
