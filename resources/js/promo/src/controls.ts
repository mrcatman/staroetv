import { Database } from "./database";
import { Loader } from "./loader";
import { Resources } from "./resources";
import { Playback } from "./playback";
import { About } from "./about";

//const reloadProgramsButton = document.getElementById('reload_programs') as HTMLButtonElement;
//reloadProgramsButton.addEventListener('click', loadPrograms);

export const Controls = {
    buttons: {
        nextChannel: document.getElementById('control_next_channel'),
        prevChannel: document.getElementById('control_prev_channel'),
        refresh: document.getElementById('control_refresh'),
        about: document.getElementById('control_about'),
        onOff: document.getElementById('control_on_off'),

        year: document.getElementById('control_year'),
        yearsList: document.getElementById('control_year_list'),

        genre: document.getElementById('control_genre'),
        genresList: document.getElementById('control_genre_list'),

        random: document.getElementById('remote_random'),
        commercials: document.getElementById('remote_commercials'),

        volume: document.getElementById('control_volume'),
    },
    setActiveButton(buttonEl: HTMLButtonElement) {
        document.querySelector('.remote__button--active')?.classList.remove('remote__button--active');
        buttonEl.classList.add('remote__button--active');
    },
    async initChannels() {
        const channelsRootEl = document.querySelector('#channels');
        channelsRootEl.innerHTML = '';

        const year = Playback.params.year;
        const channels = Database.channels.getFederal();
        channels.forEach(channel => {
            if (year && !channel[7].includes(year)) {
                return;
            }
            let name = channel[1];
            let logo = channel[2];
            if (year && channel[6]?.length) {
                const yearName = channel[6].find((channelName) => {
                    return new Date(channelName[2]) >= new Date(year, 1, 1);
                });
                if (yearName) {
                    if (yearName[0].length) {
                        name = yearName[0];
                    }
                    if (yearName[3]?.length) {
                        logo = yearName[3];
                    }
                }
            }

            const channelEl = document.createElement('button');
            channelEl.classList.add('remote__button', 'remote__channel');
            channelEl.innerHTML = `
            <div class="remote__channel__logo" style="background-image:url(${logo}"></div>
            <span class="tooltip">${name}</span>
            `;
            channelsRootEl.appendChild(channelEl);
            channelEl.addEventListener('click', () => {
                this.setActiveButton(channelEl);
                Playback.setParamsAndStart({
                    channel_id: channel[0],
                    commercials: false,
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

        const programs = Database.programs.getRandomList(12, Playback.params);
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
                // todo убрать здесь заставки передач (?)
                Playback.setParamsAndStart({
                    channel_id: undefined,
                    program_id: program[0],
                    commercials: false,
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
    randomChannel() {
        this.setActiveButton(this.buttons.random);
        Playback.setParamsAndStart({
            channel_id: undefined,
            program_id: undefined,
            commercials: false,
        });
    },
    commercials() {
        this.setActiveButton(this.buttons.commercials);
        Playback.setParamsAndStart({
            channel_id: undefined,
            program_id: undefined,
            genre_id: undefined,
            commercials: true,
        });
    },
    onOff() {
        Playback.onOff();
    },
    initButtons() {
        this.buttons.prevChannel.addEventListener('click', () => Playback.changeChannel(-1));
        this.buttons.nextChannel.addEventListener('click', () => Playback.changeChannel(1));
        this.buttons.refresh.addEventListener('click', () => Playback.start(true));
        this.buttons.about.addEventListener('click', () => About.show());

        this.buttons.year.addEventListener('click', () => this.showYears());
        this.buttons.genre.addEventListener('click', () => this.showGenres());

        this.buttons.random.addEventListener('click', () => this.randomChannel());
        this.buttons.commercials.addEventListener('click', () => this.commercials());
        this.buttons.onOff.addEventListener('click', () => this.onOff());
    },
    showYears() { // todo скрытие, вариант "Все"
        if (!this.buttons.yearsList.children.length) {
            const setYear = (el: HTMLDivElement, year: number) => {
                this.buttons.yearsList.querySelectorAll('.tv__category__list__item').forEach(_el => {
                    _el.classList.remove('tv__category__list__item--active');
                });
                el.classList.add('tv__category__list__item--active');

                this.buttons.yearsList.style.display = 'none';

                Playback.setParamsAndStart({
                    year,
                });
                this.initChannels();
                this.initPrograms();
                this.buttons.year.innerHTML = `${year ?? 'любой'}`;


                const reset = this.buttons.yearsList.querySelector('.tv__category__list__item--reset');
                reset.style.display = year ? '' : 'none';
            }
            for (let i = 1970; i <= 2010; i++) {
                const el = document.createElement('div');
                el.innerHTML = `${i}`;
                el.classList.add('tv__category__list__item');
                el.addEventListener('click', () => setYear(el, i));
                this.buttons.yearsList.appendChild(el);
            }
            const el = document.createElement('div');
            el.innerHTML = `Сброс`;
            el.classList.add('tv__category__list__item', 'tv__category__list__item--reset');
            el.addEventListener('click', () => setYear(el, null));
            el.style.display = 'none';
            this.buttons.yearsList.appendChild(el);
        }
        this.buttons.yearsList.style.display = '';
    },
    showGenres() {
        if (!this.buttons.genresList.children.length) {
            const setGenre = (el: HTMLDivElement, genre: Promo.Genre) => {
                this.buttons.genresList.querySelectorAll('.tv__category__list__item').forEach(_el => {
                    _el.classList.remove('tv__category__list__item--active');
                });
                el.classList.add('tv__category__list__item--active'); // todo обновить список передач

                this.buttons.genresList.style.display = 'none';

                Playback.setParamsAndStart({
                    commercials: false,
                    genre_id: genre ? genre[0] : undefined,
                });
                this.initPrograms();
                this.buttons.genre.innerHTML = genre[1];

                const reset = this.buttons.genresList.querySelector('.tv__category__list__item--reset');
                reset.style.display = genre ? '' : 'none';
            }

            Database.genres.forEach(genre => {
                const el = document.createElement('div');
                el.innerHTML = genre[1];
                el.classList.add('tv__category__list__item');
                el.addEventListener('click', (e) => setGenre(el, genre));
                this.buttons.genresList.appendChild(el);
            });
            const el = document.createElement('div');
            el.innerHTML = `Сброс`;
            el.classList.add('tv__category__list__item', 'tv__category__list__item--reset');
            el.addEventListener('click', () => setGenre(el, null));
            el.style.display = 'none';
            this.buttons.genresList.appendChild(el);
        }
        this.buttons.genresList.style.display = '';
    },
    initVolume() {
        const savedVolume = parseFloat(localStorage.getItem('volume'));
        let volume = savedVolume >= 0 ? savedVolume : 1;
        const setVolume = (_volume: number) => {
            volume = _volume;
            this.buttons.volume.style.transform = `rotate(${volume * 270 + 45}deg)`;

            Playback.setVolume(volume);
            localStorage.setItem('volume', volume.toString());
        }

        setVolume(volume);

        [['mousedown', 'mousemove', 'mouseup'], ['touchstart', 'touchmove', 'touchend']].forEach((events) => {
            this.buttons.volume.addEventListener(events[0], (e) => {
                const x = e.clientX ?? e.touches[0].clientX;
                let startVolume = volume;
                const onMouseMove = (e: MouseEvent) => {
                    const delta = ((e.clientX ?? e.touches[0].clientX) - x) / 200;
                    setVolume(Math.max(0, Math.min(1, startVolume + delta)));
                }
                document.addEventListener(events[1], onMouseMove);
                document.addEventListener(events[2], () => {
                    document.removeEventListener(events[1], onMouseMove);
                });
            });
        })
    },
    initClickOutside() {
        document.addEventListener('click', (e) => {
            const els = [[this.buttons.year, this.buttons.yearsList], [this.buttons.genre, this.buttons.genresList]];
            els.forEach(elGroup => {
                if (elGroup[1].style.display !== 'none' && !e.composedPath().includes(elGroup[0])) {
                    elGroup[1].style.display = 'none';
                }
            });
        });
    },
    initAll() {
        this.initChannels();
        this.initPrograms();
        this.initButtons();
        this.initVolume();
        this.initClickOutside();
    }
}
