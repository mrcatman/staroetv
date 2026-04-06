import { Loader } from "./loader";

import background from '/resources/images/promo/background.webp'

const FAIL_TIMEOUT_MS = 5000;
const YOUTUBE_IFRAME_API = 'https://www.youtube.com/iframe_api';

export const Resources = {
    loadedList: [] as string[],

    isYoutubeAvailable(): boolean {
        return this.loadedList.includes(YOUTUBE_IFRAME_API);
    },

    load (resources: string[], load: (string) => Promise<void>, failAfterTimeout: boolean = false): Promise<void> {
        return new Promise<void>((resolve) => {
            let loadedCount = 0;

            resources.forEach((resource) => {
                if (!resource?.length) {
                    return resolve();
                }
                load(resource).then(() => {
                    loadedCount++;

                    this.loadedList.push(resource);
                    if (loadedCount === resources.length) {
                        resolve();
                    }
                })

                if (failAfterTimeout) {
                    setTimeout(() => {
                        resolve();
                    }, FAIL_TIMEOUT_MS);
                }
            })
        });
    },

    loadPicture(picture: string) {
        return new Promise<void>((resolve) => {
            const el = new Image();
            // @ts-ignore
            el.src = new URL(picture, import.meta.url).href;
            el.onload = () => resolve();
        });
    },

    loadPictures() {
        const pictures = [background]
        return this.load(pictures, this.loadPicture);
    },
    loadScripts() {
        const scripts = ['https://vk.com/js/api/videoplayer.js'];
        const youtubeLastDateCheck = parseInt(localStorage.getItem('youtube_last_date_check'));
        if (!youtubeLastDateCheck || new Date().getTime() - youtubeLastDateCheck > 1000 * 60 * 10) {
            scripts.push(YOUTUBE_IFRAME_API);
        }

        return this.load(scripts, (script: string) => {
            return new Promise<void>((resolve) => {
                const el = document.createElement('script');
                el.src = script;
                el.onload = () => resolve();

                document.head.appendChild(el);
            });
        }, true);
    },
    loadAll() {
        this.loadScripts().then(() => {
            if (!this.isYoutubeAvailable()) {
                localStorage.setItem('youtube_last_date_check', new Date().getTime().toString());
            }
            Loader.increment(20)
        });
        this.loadPictures().then(() => {
            Loader.increment(20)});
    }
}
