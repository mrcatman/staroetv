import Hls from 'hls.js';
import { EventEmitter } from "../utils";

class VKPlayer extends EventEmitter implements Promo.Player  {
    private instance;
    private isEnded: boolean = false;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<iframe src="${url}&js_api=1" frameborder="0" allowfullscreen allow="autoplay"></iframe>`;

            const iframe = container.querySelector('iframe');
            this.instance = VK.VideoPlayer(iframe);
            this.instance.on('inited', () => {
                resolve();
            })

            this.instance.on('started', () => this.emit('started'));
            this.instance.on('error', (e) => this.emit('error', e));
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

    seek(time: number) {
        this.instance.seek(time);
    }

    play() {
        this.instance.play();
    }

    stop() {
        this.instance.pause();
    }

    getCurrentTime() {
        return this.instance.getCurrentTime();
    }

    getDuration() {
        return this.instance.getDuration();
    }

    setVolume(volume: number) {
        this.instance.unmute();
        this.instance.setVolume(volume);
    }
}

// class RutubePlayer extends EventEmitter implements Promo.Player {
//     private contentWindow;
//     private started: boolean = false;
//
//     private duration: number = 0;
//     private currentTime: number = 0;
//
//     load(url: string, container: HTMLDivElement)  {
//         return new Promise<void>((resolve, reject) => {
//             container.innerHTML = `<iframe src="${url}" frameborder="0" allowfullscreen allow="clipboard-write; autoplay" ></iframe>`;
//
//             const iframe = container.querySelector('iframe');
//             this.contentWindow = iframe.contentWindow;
//             console.log(this.contentWindow);
//             this.contentWindow.addEventListener('message',  (event) => {
//                 const message = JSON.parse(event.data);
//                 console.log(message.command.type);
//                 switch (message.command.type) {
//                     case 'player:play':
//                         if (!this.started) {
//                             this.play();
//                             this.emit('started');
//                             resolve();
//                         }
//                         break
//                     case 'player:durationChange':
//                         this.duration = message.data.duration;
//                         break;
//                     case 'player:currentTime':
//                         this.currentTime = message.data.time;
//                         break
//                 }
//             });
//             resolve();
//         });
//     }
//
//     private doCommand(command) {
//         this.contentWindow?.postMessage(JSON.stringify({command}), '*');
//     }
//
//     seek(time: number) {
//         this.doCommand( {type:'player:setCurrentTime', data: {time}});
//     }
//
//     play() {
//         this.doCommand( {type:'player:play', data: {}});
//     }
//
//     stop() {
//         this.doCommand( {type:'player:pause', data: {}});
//     }
//
//     getCurrentTime() {
//         return this.currentTime;
//     }
//
//     getDuration() {
//         return this.duration;
//     }
//
//     setVolume(volume: number) {
//         this.doCommand({type: 'player:setVolume', data: {volume }});
//     }
// }

class YoutubePlayer extends EventEmitter implements Promo.Player {
    private instance;
    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = '';
            const player = document.createElement('div');
            container.appendChild(player);
            this.instance = new YT.Player(player, {
                videoId: url.split('/').pop().split('?').shift(),
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
                        this.emit('error', e);
                    }
                }
            });
        });
    }

    seek(time: number) {
        this.instance.seekTo(time);
    }

    play() {
        this.instance.playVideo();
    }

    stop() {
        this.instance?.stopVideo();
    }

    getCurrentTime() {
        return this.instance.getCurrentTime();
    }

    getDuration() {
        return this.instance.getDuration();
    }

    setVolume(volume: number) {
        return this.instance.setVolume(volume * 100);
    }
}

class HlsJsPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video playsinline></video>`;
            this.videoElement = container.querySelector('video');

            if (Hls.isSupported()) {
                const hls = new Hls();
                hls.loadSource(url);
                hls.attachMedia(this.videoElement);
                this.videoElement.addEventListener('playing', () => this.emit('started'));
                hls.on(Hls.Events.MANIFEST_PARSED, () =>  resolve());

                hls.on(Hls.Events.MEDIA_ENDED, () => this.emit('ended'));
                hls.on(Hls.Events.ERROR, (e, data) => {
                    console.log('hls error', data);
                    if (data.fatal) {
                        hls.destroy();
                        setTimeout(() => {
                            this.emit('error', e)
                        }, 1000);
                    }
                });
            } else if (this.videoElement.canPlayType('application/vnd.apple.mpegurl')) {
                this.videoElement.src = url;
                this.videoElement.addEventListener('loadedmetadata', () => resolve());

                this.videoElement.addEventListener('playing', () => this.emit('started'));
                this.videoElement.addEventListener('ended', () => this.emit('ended'));
                this.videoElement.addEventListener('error', (e) => this.emit('error', e));
            } else {
                reject();
            }
        });
    }

    seek(time: number) {
        this.videoElement.currentTime = time;
    }

    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }

    getCurrentTime() {
        return this.videoElement.currentTime;
    }

    getDuration() {
        return this.videoElement.duration;
    }

    setVolume(volume: number) {
        this.videoElement.volume = volume;
    }

}

class HTMLPlayer extends EventEmitter implements Promo.Player {
    private videoElement: HTMLVideoElement;

    load(url: string, container: HTMLDivElement)  {
        return new Promise<void>((resolve, reject) => {
            container.innerHTML = `<video playsinline><source src="${url}" type="video/mp4" /></video>`;
            this.videoElement = container.querySelector('video');
            this.videoElement.addEventListener('loadedmetadata', function() {
                resolve();
            });
            this.videoElement.addEventListener('playing', () => this.emit('started'));
            this.videoElement.addEventListener('ended', () => this.emit('ended'));
            this.videoElement.addEventListener('error', (e) => this.emit('error', e));
        });
    }

    seek(time: number) {
        this.videoElement.currentTime = time;
    }

    play() {
        this.videoElement?.play();
    }

    stop() {
        this.videoElement?.pause();
    }

    getCurrentTime() {
        return this.videoElement.currentTime;
    }

    getDuration() {
        return this.videoElement.duration;
    }

    setVolume(volume: number) {
        this.videoElement.volume = volume;
    }
}

export const createPlayer = (url: string): Promo.Player | null => {
    let player: Promo.Player;
    switch (true) {
        case url.includes('vk.com'):
            player = new VKPlayer();
            break;
        // case url.includes('rutube'):
        //     player = new RutubePlayer();
        //     break;
        case url.includes('youtu'):
            player = new YoutubePlayer();
            break;
        case url.includes('.m3u8'):
            player = new HlsJsPlayer();
            break;
        default:
            player = new HTMLPlayer();
            break;
    }
    return player;
}
