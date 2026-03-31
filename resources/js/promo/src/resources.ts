import { Loader } from "./loader";

const FAIL_TIMEOUT_MS = 5000;
const YOUTUBE_IFRAME_API = 'https://www.youtube.com/iframe_api';
// todo save and recheck

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
        const pictures = ['/resources/images/promo/background.webp']
        return this.load(pictures, this.loadPicture);
    },
    loadScripts() {
        const scripts = ['https://vk.com/js/api/videoplayer.js', YOUTUBE_IFRAME_API];

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
        this.loadScripts().then(() => {Loader.increment(20)});
        this.loadPictures().then(() => {Loader.increment(20)});
    }
}
