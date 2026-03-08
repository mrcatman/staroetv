import { createPlayer } from "./players";

const videoParams: Promo.VideoParams = {};
let player: Promo.Player;

const scripts = ['https://vk.com/js/api/videoplayer.js', 'https://www.youtube.com/iframe_api'];
const loadScripts = async () => {
    let count = 0;

    return new Promise<void>((resolve) => {
        scripts.forEach((script) => {
            const el = document.createElement('script');
            el.src = script;
            el.onload = () => {
                count++;
                if (count === scripts.length) {
                    resolve();
                }
            }
            document.head.appendChild(el);
        });
    })
}

const playerRoot = document.getElementById('player') as HTMLDivElement;
const noise = document.getElementById('noise') as HTMLDivElement;
const overlay = document.getElementById('overlay') as HTMLDivElement;
const program = document.getElementById('program') as HTMLDivElement;
const channel = document.getElementById('channel') as HTMLDivElement;

const getRandomVideo = async(): Promise<Promo.Video> => {
    try {
        const response = await fetch(route('promo.video', videoParams));
        return await response.json() as Promo.Video;
    } catch (error) {
        console.error(error);
    }
}

const loadVideo = async () => {
    console.log('loadVideo');
    overlay.style.display = 'none';
    noise.style.display = 'block';

    const video = await getRandomVideo();
    player?.stop();

    player = await createPlayer(video, playerRoot);
    player.seekToRandomTime();
    player.play();

    player.on('error', () => loadVideo);
    player.on('ended', () => loadVideo);
    player.on('started', () => {
        updateTitle(video);
        noise.style.display = 'none';
    });


}

let hideTitleOverlayTimeout: Timeout;
const updateTitle = (video: Promo.Video) => {
    clearTimeout(hideTitleOverlayTimeout);
    channel.innerHTML = video.channel_name;
    program.innerHTML = video.title;
    overlay.style.display = '';
    hideTitleOverlayTimeout = setTimeout(() => {
        overlay.style.display = 'none';
    }, 5000)
}

const initChannels = () => {
    Array.from(document.querySelectorAll('.remote__channel')).forEach((channel: HTMLDivElement) => {
        channel.addEventListener('click', () => {
            videoParams.channel_id = parseInt(channel.dataset!.id);
            loadVideo();
        })
    })
}

document.addEventListener('DOMContentLoaded', async() => {
    await loadScripts();
    initChannels();

    loadVideo();
})
