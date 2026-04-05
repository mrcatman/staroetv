export const About = {
    el: document.getElementById('about'),
    closeEl: document.getElementById('about_close'),
    show() {
        this.el.style.display = '';
    },
    close() {
        localStorage.setItem('about_shown', '1');
        this.el.style.display = 'none';
    },
    init() {
        this.closeEl.addEventListener('click', () => this.close());
        if (!localStorage.getItem('about_shown')) {
            this.show();
        }
    }
}
