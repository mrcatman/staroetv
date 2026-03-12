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

const getRandomVideo = async (): Promise<Promo.Video> => {

    try {
        const response = await fetch(route('promo.video', videoParams));
        return await response.json() as Promo.Video;
    } catch (error) {
        console.error(error);
    }
}

const loadVideo = async () => {
    console.log('loadVideo');
    //{
    //     "id": 12468,
    //     "title": "Shit-\u041f\u0430\u0440\u0430\u0434 V.Z.O.P.A. (MTV, 2003) \u041a\u0440\u0430\u0441\u043a\u0438, \u041f\u043b\u0430\u043d\u043a\u0430, \u041e\u043b\u0435\u0433 \u041b\u0430\u0432\u0440\u043e\u0432, \u0413\u0430\u0440\u0440\u0438 \u041f\u043e\u0442\u0442\u042d\u0440, Blue feat. Elton John",
    //     "channel_name": "MTV \u0420\u043e\u0441\u0441\u0438\u044f",
    //     "url": "\/\/vk.com\/video_ext.php?oid=200690671&id=171090248&hash=68d3b038e2e1f452&hd=2&wmode=transparent"
    // }
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

const loadPrograms = async () => {
    const tapes = document.getElementById('tapes') as HTMLDivElement;
    const response = await fetch(route('promo.programs'));
    const programs = await response.json();

    programs.forEach((program) => {
        console.log(program);
        tapes.innerHTML += `<div class="tape">
                <div class="tape__sticker">
                    <div class="tape__sticker__content">
                        <div class="tape__sticker__cover" style="background-image:url('${program.cover}')"></div>
                        ${program.name}
                    </div>
                </div>
            </div>`;
    })
}
const reloadProgramsButton = document.getElementById('reload_programs') as HTMLButtonElement;
reloadProgramsButton.addEventListener('click', loadPrograms);

document.addEventListener('DOMContentLoaded', async () => {
    await loadScripts();
    await loadPrograms();
    initChannels();

    loadVideo();
})
