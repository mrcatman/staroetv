import { Playback } from "./playback";

export const About = {
    el: document.getElementById('about'),
    closeEl: document.getElementById('about_close'),
    firstTime: false,
    show() {
        this.el.style.display = '';
    },
    close() {
        localStorage.setItem('about_shown', '1');
        this.el.style.display = 'none';
        if (this.firstTime) {
            this.firstTime = false;
            Playback.start();
        }
    },
    init() {
        this.closeEl.addEventListener('click', () => this.close());
        if (!localStorage.getItem('about_shown')) {
            this.firstTime = true;
            this.show();
        }
    }
}
