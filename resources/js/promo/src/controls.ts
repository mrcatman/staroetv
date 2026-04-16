import { Database } from "./database";
import { Loader } from "./loader";
import { Resources } from "./resources";
import { Playback } from "./playback";
import { About } from "./about";

//const reloadProgramsButton = document.getElementById('reload_programs') as HTMLButtonElement;
//reloadProgramsButton.addEventListener('click', loadPrograms);

export const Controls = {
    allChannels: false,
    main: document.getElementById('main'),
    remote: document.getElementById('remote'),
    remoteChannels: document.getElementById('remote_channels'),
    closeRemoteContainer: document.getElementById('close_remote_container'),
    remoteMain: document.getElementById('remote_main'),
    remoteAll: document.getElementById('remote_all'),
    remoteAllChannels: document.getElementById('remote_all_channels'),

    programsReloadCount: 0,

    buttons: {
        nextChannel: document.getElementById('control_next_channel'),
        prevChannel: document.getElementById('control_prev_channel'),
        refresh: document.getElementById('control_refresh'),

        mobileNextChannel: document.getElementById('mobile_control_next_channel'),
        mobilePrevChannel: document.getElementById('mobile_control_prev_channel'),
        mobileRefresh: document.getElementById('mobile_control_refresh'),

        goToRecord: document.getElementById('control_go_to_record'),
        about: document.getElementById('control_about'),
        onOff: document.getElementById('control_on_off'),

        year: document.getElementById('control_year'),
        yearsList: document.getElementById('control_year_list'),

        reloadPrograms: document.getElementById('reload_programs'),
        programsBack: document.getElementById('programs_back'),

        genre: document.getElementById('control_genre'),
        genresList: document.getElementById('control_genre_list'),

        random: document.getElementById('remote_random'),
        commercials: document.getElementById('remote_commercials'),

        volume: document.getElementById('control_volume'),

        togglePrograms: document.getElementById('toggle_programs'),
        toggleRemote: document.getElementById('toggle_remote'),
        closeRemote: document.getElementById('close_remote'),

        showAllChannels: document.getElementById('remote_show_all'),
        showAllChannelsBack: document.getElementById('remote_back'),
    },

    async initChannels() {
        this.remoteChannels.innerHTML = '';
        const year = Playback.params.year;
        const channels = Database.channels.getFederal();
        channels.forEach(channel => {
            if (year && !channel[7].includes(year)) {
                return;
            }
            const [name, logo] = Database.channels.getNameAndLogo(channel, year);

            const channelEl = document.createElement('button');
            channelEl.dataset.id = channel[0].toString();
            channelEl.classList.add('remote__button', 'remote__channel');
            channelEl.innerHTML = `
            <div class="remote__channel__logo" style="background-image:url(${logo}"></div>
            <span class="tooltip">${name}</span>
            `;
            this.remoteChannels.appendChild(channelEl);
            channelEl.addEventListener('click', () => {
                this.updateActiveChannel(channel[0]);
                Playback.setParamsAndStart({
                    channel_id: channel[0],
                    program_id: undefined,
                    commercials: false,
                })
                this.initPrograms();
                this.closeRemote();
            });
        })

        this.remoteAllChannels.innerHTML = '';
        const allChannels = Database.channels.list;
        allChannels.forEach(channel => {
            if (year && !channel[7].includes(year)) {
                return;
            }
            const [name, logo] = Database.channels.getNameAndLogo(channel, year);

            const channelEl = document.createElement('button');
            channelEl.dataset.id = channel[0].toString();
            channelEl.classList.add('remote__button', 'remote__channel', 'remote__channel--wide');
            channelEl.innerHTML = `
            <div class="remote__channel__logo" style="background-image:url(${logo}"></div>
            <div class="remote__channel__number">${channel[5]}</div>
            <div class="remote__channel__texts">
                <div class="remote__channel__name">${name}</div>
                <div class="remote__channel__about">${channel[4] ?? ''}</div>
            </div>
            `;
            this.remoteAllChannels.appendChild(channelEl);
            channelEl.addEventListener('click', () => {
                this.updateActiveChannel(channel[0]);
                Playback.setParamsAndStart({
                    channel_id: channel[0],
                    program_id: undefined,
                    commercials: false,
                })
                this.initPrograms();
                this.closeRemote();
            });
        })

        Loader.increment(5);

        const logos = channels.map(channel => channel[2]);
        await Resources.load(logos, Resources.loadPicture, true);
        Loader.increment(10);
    },
    setActiveChannelButtons(buttonEls?: HTMLButtonElement[]) {
        Array.from(document.querySelectorAll('.remote__button--active')).forEach(el => {
            el.classList.remove('remote__button--active');
        });
        buttonEls && Array.from(buttonEls).forEach(el => {
            el.classList.add('remote__button--active');
        });
    },
    updateActiveChannel(id: number) {
        const activeChannel = id ?
            document.querySelectorAll(`.remote__channel[data-id="${id}"]`) :
            [this.buttons.commercials]
        this.setActiveChannelButtons(activeChannel);
    },
    async initPrograms() {
        this.programsReloadCount++;
        if (this.programsReloadCount > 2) {
            Database.records.loadAll();
        }

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
                this.programsBack();
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
    },
    programsBack() {
        this.main.classList.remove('main--programs');
    },
    randomChannel() {
        this.setActiveChannelButtons([this.buttons.random]);
        Playback.setParamsAndStart({
            channel_id: undefined,
            program_id: undefined,
            commercials: false,
        });
        this.initPrograms();
        this.closeRemote();
    },
    commercials() {
        this.setActiveChannelButtons([this.buttons.commercials]);
        Playback.setParamsAndStart({
            channel_id: undefined,
            program_id: undefined,
            genre_id: undefined,
            commercials: true,
        });
        this.closeRemote();
    },
    onOff() {
        Playback.onOff();
    },
    initMain() {
        this.buttons.prevChannel.addEventListener('click', () => this.changeChannel(-1));
        this.buttons.nextChannel.addEventListener('click', () => this.changeChannel(1));
        //this.buttons.refresh.addEventListener('click', () => Playback.start(true));

        this.buttons.mobilePrevChannel.addEventListener('click', () => this.changeChannel(-1));
        this.buttons.mobileNextChannel.addEventListener('click', () => this.changeChannel(1));
        //this.buttons.mobileRefresh.addEventListener('click', () => Playback.start(true));

        this.buttons.about.addEventListener('click', () => About.show());

        this.buttons.year.addEventListener('click', () => this.showYears());
        this.buttons.genre.addEventListener('click', () => this.showGenres());

        this.buttons.random.addEventListener('click', () => this.randomChannel());
        this.buttons.commercials.addEventListener('click', () => this.commercials());
        this.buttons.onOff.addEventListener('click', () => this.onOff());

        this.buttons.showAllChannels.addEventListener('click', () => this.showAllChannels());
        this.buttons.showAllChannelsBack.addEventListener('click', () => this.showMainChannels());
        this.buttons.reloadPrograms.addEventListener('click', () => this.initPrograms());
        this.buttons.programsBack.addEventListener('click', () => this.programsBack());
    },
    changeChannel(delta: number) {
        Playback.changeChannel(delta);
        this.initPrograms();
    },
    setActiveRecord(record: Promo.Record) {
        if (record) {
            this.buttons.goToRecord.classList.remove('tv__control--disabled');
            this.buttons.goToRecord.setAttribute('href', `/video/${record[0]}`);
            this.buttons.goToRecord.setAttribute('target', '_blank');
        } else {
            this.buttons.goToRecord.classList.add('tv__control--disabled');
            this.buttons.goToRecord.setAttribute('href', undefined);
            this.buttons.goToRecord.setAttribute('target', undefined);
        }
    },
    showYears() {
        if (!this.buttons.yearsList.children.length) {
            const setYear = (el: HTMLDivElement, year: number) => {
                this.buttons.yearsList.querySelectorAll('.tv__category__list__item').forEach(_el => {
                    _el.classList.remove('tv__category__list__item--active');
                });
                el.classList.add('tv__category__list__item--active');

                this.buttons.yearsList.style.display = 'none';

                Database.records.clearNowPlaying();
                Playback.setParamsAndStart({
                    program_id: undefined,
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
                el.classList.add('tv__category__list__item--active');

                this.buttons.genresList.style.display = 'none';

                Database.records.clearNowPlaying();
                Playback.setParamsAndStart({
                    program_id: undefined,
                    commercials: false,
                    genre_id: genre ? genre[0] : undefined,
                });
                this.initPrograms();
                this.buttons.genre.innerHTML = genre ? genre[1] : 'любой';

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
    initMobileToggles() {
        this.buttons.togglePrograms.addEventListener('click', () => {
            this.main.classList.toggle('main--programs');
        });
        this.buttons.toggleRemote.addEventListener('click', () => {
            this.remote.classList.toggle('remote--visible');
            this.closeRemoteContainer.style.display = this.remote.classList.contains('remote--visible') ? '' : 'none';
            this.buttons.toggleRemote.style.display = this.remote.classList.contains('remote--visible') ? 'none' : '';
        });
        this.buttons.closeRemote.addEventListener('click', () => this.closeRemote());
    },
    closeRemote() {
        this.remote.classList.remove('remote--visible');
        this.closeRemoteContainer.style.display = 'none';
        this.buttons.toggleRemote.style.display = '';
    },
    showAllChannels() {
        this.allChannels = true;
        this.remoteMain.style.display = 'none';
        this.remoteAll.style.display = '';
    },
    showMainChannels() {
        this.allChannels = false;
        this.remoteMain.style.display = '';
        this.remoteAll.style.display = 'none';
    },

    initMedia() {
        this.initChannels();
        this.initPrograms();
    },
    initButtons() {
        this.initMain();
        this.initVolume();
        this.initClickOutside();
        this.initMobileToggles();
        document.body.scrollLeft = 0;
    }
}
