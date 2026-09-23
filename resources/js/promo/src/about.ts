import { Playback } from "./playback";

const LATEST_VERSION = 1;

export const About = {
    el: document.getElementById('about'),
    closeEl: document.getElementById('about_close'),
    baseEl: document.getElementById('about_base'),
    releaseNotes: document.querySelectorAll('#about .about__release-notes'),
    firstTime: false,
    show() {
        this.el.style.display = '';
    },
    close() {
        localStorage.setItem('about_shown', '1');
        this.el.style.display = 'none';
        if (this.firstTime) {
            this.firstTime = false;
            Playback.start({});
        }
        localStorage.setItem('version', LATEST_VERSION.toString());

        this.baseEl.style.display = '';
        Array.from(this.releaseNotes).forEach(el => {
            el.style.display = '';
        });
    },
    init() {
        this.closeEl.addEventListener('click', () => this.close());
        if (!localStorage.getItem('about_shown')) {
            this.firstTime = true;
            this.show();
            return;
        }

        const latestVersion = localStorage.getItem('version') || 0;
        const hasUnreadReleaseNotes = Array.from(this.releaseNotes).some(el => el.dataset.version > latestVersion);
        if (hasUnreadReleaseNotes) {
            this.baseEl.style.display = 'none';
            Array.from(this.releaseNotes).forEach(el => {
                el.style.display = el.dataset.version > latestVersion ? '' : 'none';
            });
            this.show();
        }
    }
}
