import { Database } from "./database";
import { Loader } from "./loader";
import { Resources } from "./resources";
import { Playback } from "./playback";

//const reloadProgramsButton = document.getElementById('reload_programs') as HTMLButtonElement;
//reloadProgramsButton.addEventListener('click', loadPrograms);

export const Controls = {
    async initChannels() {
        const channelsRootEl = document.querySelector('#channels');
        channelsRootEl.innerHTML = '';

        const channels = Database.channels.getFederal();
        channels.forEach(channel => {
            const channelEl = document.createElement('div');
            channelEl.classList.add('remote__channel');
            channelEl.innerHTML = `
            <div class="remote__channel__logo" style="background-image:url(${channel[2]}"></div>
            <span class="tooltip">${channel[1]}</span>
            `;
            channelsRootEl.appendChild(channelEl);
            channelEl.addEventListener('click', () => {
                document.querySelector('.remote__channel--active')?.classList.remove('remote__channel--active');
                channelEl.classList.add('remote__channel--active');
                Playback.setParamsAndStart({
                    channel_id: channel[0],
                })
            });
        })
        Loader.increment(5);

        const logos = channels.map(channel => channel[2]);
        await Resources.load(logos, Resources.loadPicture, true);
        Loader.increment(10);
    },

    async initPrograms() {
        const programsRootEl = document.querySelector('#tapes');
        programsRootEl.innerHTML = '';

        const programs = Database.programs.getRandomList(12);
        programs.forEach(program => {
            const programEl = document.createElement('div');
            programEl.classList.add('tape');
            programEl.innerHTML = `
             <div class="tape__sticker">
                    <div class="tape__sticker__content">
                        <div class="tape__sticker__cover" style="background-image:url('${program[2]}')"></div>
                        ${program[1]}
                    </div>
                </div>
            `;
            programsRootEl.appendChild(programEl);
            programEl.addEventListener('click', () => {
                Playback.setParamsAndStart({
                    program_id: program[0],
                })
            });
        })
        Loader.increment(5);

        const logos = programs.map(program => program[2]);
        await Resources.load(logos, Resources.loadPicture, true);
        Loader.increment(10);

        const reloadProgramsButton = document.getElementById('reload_programs') as HTMLButtonElement;
        reloadProgramsButton.addEventListener('click', this.initPrograms);
    },

    initAll() {
        this.initChannels();
        this.initPrograms();
    }
}
