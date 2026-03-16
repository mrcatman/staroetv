import Hls from 'hls.js';
import { EventEmitter, getRandomDurationPoint } from "./utils";

class VKPlayer extends EventEmitter implements Promo.Player  {
    private instance;
    private isEnded: boolean = false;

    load(video: Promo.Video, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<iframe src="${video.url}&js_api=1" frameborder="0" allowfullscreen></iframe>`;

            const iframe = container.querySelector('iframe');
            this.instance = VK.VideoPlayer(iframe);
            this.instance.on('inited', () => {
                resolve();
            })

            this.instance.on('started', () => this.emit('started'));
            this.instance.on('error', () => this.emit('error'));
            this.instance.on('timeupdate', () => {
                if (this.isEnded) {
                    return;
                }
                const percent = this.instance.getCurrentTime() / this.instance.getDuration();
                if (percent > .999) {
                    this.isEnded = true;
                    this.emit('ended');
                }
            });
        });
    }

    seekToRandomTime() {
        this.instance.seek(getRandomDurationPoint(this.instance.getDuration()));
    }

    play() {
        this.instance?.play();
    }

    stop() {
        this.instance?.pause();
    }
}

class RutubePlayer extends EventEmitter implements Promo.Player {
    private contentWindow;

    load(video: Promo.Video, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<iframe src="${video.url} frameborder="0" allowfullscreen allow="clipboard-write; autoplay" ></iframe>`;

            const iframe = container.querySelector('iframe');
            this.contentWindow = iframe.contentWindow;
            this.contentWindow.addEventListener('message',  (event) => {
                console.log(event);
            });
            resolve();
        });
    }

    private doCommand(command) {
        this.contentWindow?.postMessage(JSON.stringify({command}), '*');
    }

    seekToRandomTime() {
        console.log( this.contentWindow);
    }

    play() {
        this.doCommand( {type:'player:play', data: {}});
    }

    stop() {
        this.doCommand( {type:'player:pause', data: {}});
    }
}

class YoutubePlayer extends EventEmitter implements Promo.Player {
    private instance;
    load(video: Promo.Video, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = '';
            const player = document.createElement('div');
            container.appendChild(player);

            this.instance = new YT.Player(player, {
                videoId: video.url.split('/').pop(),
                playerVars: {
                    origin: 'https://www.youtube.com',
                    enablejsapi: 1,
                    rel: 0,
                    disablekb: 1,
                    controls: 0,
                    autoplay: 0
                },
                events: {
                    onReady: () => {
                        resolve();
                    },
                    onStateChange: ({data}) => {
                        switch (data) {
                            case 0:
                                this.emit('ended');
                                break;
                            case 1:
                                this.emit('started');
                                break;
                            default:
                                break;
                        }
                    },
                    onError: (e) => {
                        this.emit('error');
                    }
                }
            });
        });
    }

    seekToRandomTime() {
        this.instance.seekTo(getRandomDurationPoint(this.instance.getDuration()));
    }

    play() {
        this.instance?.playVideo();
    }

    stop() {
        this.instance?.stopVideo();
    }
}

class HlsJsPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(video: Promo.Video, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video></video>`;
            this.videoElement = container.querySelector('video');

            const addListeners = () => {

            }
            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(video.url);
                hls.attachMedia(this.videoElement);
                this.videoElement.addEventListener('playing', () => this.emit('started'));
                hls.on(Hls.Events.MANIFEST_PARSED, () =>  resolve());

                hls.on(Hls.Events.MEDIA_ENDED, () => this.emit('ended'));
                hls.on(Hls.Events.ERROR, () => this.emit('error'));
            } else if (this.videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                this.videoElement.src = video.url;
                this.videoElement.addEventListener('loadedmetadata', () => resolve());

                this.videoElement.addEventListener('playing', () => this.emit('started'));
                this.videoElement.addEventListener('ended', () => this.emit('ended'));
                this.videoElement.addEventListener('error', () => this.emit('error'));
            } else {
                reject();
            }
        });
    }

    seekToRandomTime() {
        this.videoElement.currentTime = getRandomDurationPoint(this.videoElement.duration);
    }

    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }
}

class HTMLPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(video: Promo.Video, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video><source src="${video.url}" type="video/mp4" /></video>`;
            this.videoElement = container.querySelector('video');
            this.videoElement.addEventListener('loadedmetadata', function() {
                resolve();
            });
            this.videoElement.addEventListener('playing', () => this.emit('started'));
            this.videoElement.addEventListener('ended', () => this.emit('ended'));
            this.videoElement.addEventListener('error', () => this.emit('error'));
        });
    }

    seekToRandomTime() {
        this.videoElement.currentTime = getRandomDurationPoint(this.videoElement.duration);
    }


    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }
}

export const createPlayer = async (video: Promo.Video, container: HTMLDivElement): Promise<Promo.Player | null> => {
    let player: Promo.Player;
    switch (true) {
        case video.url.includes('vk.com'):
            player = new VKPlayer();
            break;
        case video.url.includes('rutube'):
            player = new RutubePlayer();
            break;
        case video.url.includes('youtu'):
            player = new YoutubePlayer();
            break;
        case video.url.includes('.m3u8'):
            player = new HlsJsPlayer();
            break;
        default:
            player = new HTMLPlayer();
            break;
    }
    await player?.load(video, container);
    return player;
}
