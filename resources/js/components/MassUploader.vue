<template>
    <div class="mass-uploader">
        <input type="file" multiple :accept="isRadio ? 'audio/*' : 'video/*'" ref="fileInputRef" @change="onFileInputChange"
               style="display: none"/>
        <preloader v-if="loading"/>
        <div class="form__content">
            <response :data="response"/>
            <div class="mass-uploader__records" v-if="started">
                <mass-uploader-record-form
                    v-for="(record, index) in records"
                    :key="record.title"
                    :record="record"
                    :files="files"
                    :is-radio="isRadio"
                    @remove="() => records.splice(index, 1)"
                />
                <div class="horisontal-delimiter" v-if="records.length"></div>
            </div>
            <template v-if="!started && !isRadio">
                <div class="form__section-label">Загрузка из внешнего источника</div>
                <div class="row row--align-start">
                    <div class="col col--2-5">
                        <input-container vertical label="Ссылка на группу/страницу ВК или канал Youtube">
                            <input class="input" v-model="source"/>
                        </input-container>
                    </div>
                    <div class="col">
                        <div class="input-container input-container--vertical">
                            <input-container vertical label="Код для доступа к странице">
                                <input class="input" v-model="nextPageToken"/>
                                <template #description>Если не указан, будут загружены новейшие видео</template>
                            </input-container>
                        </div>
                    </div>
                </div>
                <button class="button mass-uploader__load-list" :disabled="!source.length" @click="load()">Загрузить список</button>
                <div class="horisontal-delimiter"></div>
                <div class="form__section-label">Индивидуальная загрузка</div>
            </template>


            <template v-if="!started || isIndividualUpload">
                <div class="row row--align-start">
                    <div class="col col--2-5" v-if="!isRadio">
                        <input-container vertical label="Ссылка или список ссылок на видео (ВК/Youtube)">
                            <textarea class="input" v-model="links"></textarea>
                        </input-container>
                    </div>
                    <div class="col">
                        <input-container vertical :label="isRadio ? 'Загрузить с устройства' : 'Или загрузить с устройства'">
                            <div>
                                <button class="button button--big" @click="fileInputRef.click()">Добавить файлы</button>
                            </div>
                        </input-container>
                    </div>
                </div>
                <button v-if="!isRadio" class="button" :disabled="!links.length" @click="loadLinks()">Добавить</button>
            </template>


            <div class="form__bottom">
                <template v-if="records.length && !isIndividualUpload && nextPageToken != ''">
                    <button class="button" @click="load()">Загрузить еще</button>
                    <div class="mass-uploader__next-token">Код для доступа к следующей странице:
                        <strong>{{ nextPageToken }}</strong>
                    </div>
                </template>

            </div>
        </div>
    </div>
</template>
<style lang="scss">
.mass-uploader {
    &__load-list {
        margin-top: -1.25em;
    }
    &__next-token {
        margin: 1em 0;
    }

    &__records {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: var(--col-margin);
    }

}
</style>
<script lang="ts" setup>
import { ref, useTemplateRef } from "vue";

import Preloader from "@/components/Preloader.vue";
import Response from "@/components/Response.vue";
import MassUploaderRecordForm from "@/components/mass-uploader/MassUploaderRecordForm.vue";
import { useChannelsStore } from "@/stores/channels";
import { getInfo } from "@/utils/records/get-info";
import { useCategoriesStore } from "@/stores/categories";
import InputContainer from "@/components/InputContainer.vue";

const props = defineProps< {
    isRadio: boolean,
}>();

export type MultipleRecordsResponseItem = {
    title: string,
    description?: string,
    player?: string,
    code?: string,
    thumbnails?: string[]
    file?: string,
    upload?: File,
}

const loading = ref<boolean>(false);
const response = ref<Forms.Response>();
const started = ref<boolean>(false);
const isIndividualUpload = ref<boolean>(false);
const links = ref<string>('');

const source = ref<string>('');
const nextPageToken = ref<string>('');

const files = ref<string[]>([]);
const records = ref<MultipleRecordsResponseItem[]>([]);

const load = () => {
    loading.value = true;

    response.value = null;
    $.post(route('mass-upload.list'), {
        source: source.value,
        next_page_token: nextPageToken.value,
    }).then((res) => {
        loading.value = false;
        if (res.status) {
            nextPageToken.value = res.data.next_page_token;

            files.value = res.data.files;
            records.value = [...records.value, ...res.data.items];

            isIndividualUpload.value = false;
            started.value = true;
        } else {
            response.value = res;
        }
    }).catch((e: JQuery.jqXHR) => {
        response.value = e.responseJSON.message ? {status: 0, text: e.responseJSON.message} : {
            status: 0,
            text: 'Ошибка, повторите позже'
        };
        loading.value = false;
    });
}

const loadLinks = async () => {
    const _links = links.value;
    links.value = '';
    loading.value = true;
    isIndividualUpload.value = true;

    for (const link of (_links.match(/(https?:\/\/[^\s]+)/g) ?? [])) {
        try {
            const {
                id, title, description, player, code, thumbnails
            } = await getInfo(link);
            if (!id) {
                return;
            }

            records.value.push({
                title,
                description,
                player,
                code,
                thumbnails,
            })
            started.value = true;
        } catch (e) {

        }
    }
    loading.value = false;
}

const channelsStore = useChannelsStore();
channelsStore.load();

const fileInputRef = useTemplateRef<HTMLInputElement>('fileInputRef');
const onFileInputChange = () => {
    Array.from(fileInputRef.value.files).forEach(file => {
        records.value.push({
            title: file.name.split('.').slice(0, -1).join('.'),
            upload: file,
        })
    });
    isIndividualUpload.value = true;
    started.value = true;
}

const categoriesStore = useCategoriesStore();
categoriesStore.load();

//     data() {
//         return {
//             source: '',
//             records: [],
//             files: [],
//             programs: {},
//             interprogramPackages: {},
//             parsedRecords: [],
//             channelsList: [],
//             currentPage: 1,
//             perPage: 30,
//             programsLoading: {},
//             interprogramPackagesLoading: {},
//             loading: false,
//             nextPageToken: '',
//             response: null,
//         }
//     },
//     mounted() {
//         this.loadCategories();
//         this.loadChannels();
//     },
//     computed: {
//         interprogramOptions() {
//             let options = {};
//             Object.keys(this.interprogramPackages).forEach(channelId => {
//                 options[channelId] = [
//                     {id: -1, text: '-'}
//                 ];
//                 this.interprogramPackages[channelId].forEach(packageItem => {
//                     options[channelId].push({id: packageItem.id, text: packageItem.name || packageItem.years_range})
//                 })
//             })
//             return options;
//         },
//         programsOptions() {
//             let options = {};
//             Object.keys(this.programs).forEach(channelId => {
//                 options[channelId] = [
//                     {id: -1, text: '-'}
//                 ];
//                 this.programs[channelId].forEach(program => {
//                     options[channelId].push({id: program.id, text: program.name})
//                 })
//             })
//             return options;
//         },
//         storageFiles() {
//             const files = [
//                 {id: '', text: '...'},
//             ]
//             this.files.forEach(file => {
//                 files.push({
//                     id: file,
//                     text: file
//                 })
//             });
//             return files;
//         },
//         interprogramTypes() {
//             let categories = this.categories;
//             if (!categories || categories.length === 0) {
//                 return [];
//             }
//             categories = categories.filter(category => category.type === 'interprogram').map(category => {
//                 return {id: category.id, text: category.name}
//             });
//             categories.unshift({
//                 id: -1,
//                 text: '-'
//             });
//             return categories;
//         },
//         advertisingTypes() {
//             let categories = this.categories;
//             if (!categories || categories.length === 0) {
//                 return [];
//             }
//             categories = categories.filter(category => category.type === 'advertising').map(category => {
//                 return {id: category.id, text: category.name}
//             });
//             categories.unshift({
//                 id: -1,
//                 text: '-'
//             });
//             return categories;
//         },
//         visibleRecords() {
//             //console.log((this.currentPage - 1) * this.perPage, this.perPage);
//             return this.parsedRecords.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage);
//         },
//         channelOptions() {
//             let data = [];
//             this.channelsList.forEach(channel => {
//                 data.push({id: channel.id, text: channel.name});
//             });
//             return data;
//         },
//         allProgramNames() {
//             let names = {};
//             this.programs.forEach(program => {
//                 names[program.name] = program.id;
//             });
//             return names;
//         },
//         allChannelNames() {
//             let names = {};
//             this.channelsList.forEach(channel => {
//                 let name = channel.name;
//                 if (channel.city) {
//                     name = `${name} (${channel.city})`;
//                 }
//                 names[name] = channel.id;
//                 if (channel.names) {
//                     channel.names.forEach(additionalNameData => {
//                         names[additionalNameData.name] = channel.id;
//                     })
//                 }
//             });
//             return names;
//         },
//     },
//     methods: {
//         async reloadPrograms(record) {
//             await this.loadPrograms(record, true)
//             await this.findProgram(record);
//         },
//         async findProgram(record) {
//             console.log(record.channel.id);
//             if (!record.channel.id) {
//                 return;
//             }
//             await this.loadPrograms(record);
//             let programName = record._parsed[4];
//             if (!programName) return;
//             if (programName.indexOf('Д/с') !== -1 || programName.indexOf('Д/ф') !== -1 || programName.indexOf('Д.ф') !== -1 || programName.indexOf('Д.с') !== -1 || programName.indexOf('Д. ф') !== -1 || programName.indexOf('Д. с') !== -1) {
//                 programName = 'Документальные фильмы';
//             }
//             if (programName.indexOf('Концерт') !== -1 || programName.indexOf('концерт') !== -1) {
//                 programName = 'Концерты';
//             }
//             let program = this.programs[record.channel.id] ? this.programs[record.channel.id].filter(program => program.name === programName)[0] : null;
//             if (!program) {
//                 program = this.programs[record.channel.id] ? this.programs[record.channel.id].filter(program => program.name.indexOf(programName) !== -1)[0] : null;
//             }
//             if (program) {
//                 record.program.id = program.id;
//             } else {
//                 console.log(`Program not found: ${programName} (channel id: ${record.channel.id}, count: ${this.programs[record.channel.id] ? this.programs[record.channel.id].length : '-'})`);
//             }
//         },
//         save(record) {
//             this.$set(record, 'loading', true);
//
//             const data = JSON.parse(JSON.stringify(record));
//             data.is_radio = false;
//
//             if (data.channel.id <= 0) {
//                 data.channel.unknown = true;
//             }
//
//             if (data.program.id <= 0) {
//                 data.program.unknown = true;
//             }
//
//             $.post(route('records.video.add'), data).done(res => {
//                 this.$set(record, 'loading', false);
//                 this.$refs.snackbar.show(res);
//                 if (res.status) {
//                     this.parsedRecords = this.parsedRecords.filter(recordItem => recordItem.player !== record.player);
//                     this.currentPage = 1;
//                 }
//             });
//
//         },
//         deleteRecord(record) {
//             this.parsedRecords = this.parsedRecords.filter(recordItem => recordItem.player !== record.player);
//         },
//         async parseInfo() {
//             for (let recordItem of this.records) {
//                 if (!recordItem._ready) {
//                     recordItem._ready = true;
//                     let title = recordItem.title;
//                     let embedCode = `<iframe frameborder="0" src="${recordItem.player}" allowfullscreen width="100%" height="100%"></iframe>`;
//                     let record = {
//                         _parsed: [],
//                         player: recordItem.player,
//                         description: recordItem.description,
//                         channel: {},
//                         program: {},
//                         record: {
//                             title: recordItem.title,
//                             code: embedCode
//                         },
//                         date: {}
//                     };
//                     if (recordItem.image.length > 0) {
//                         if (recordItem.image[5]) {
//                             record.record.cover = recordItem.image[5].url;
//                         } else {
//                             if (recordItem.image[2]) {
//                                 record.record.cover = recordItem.image[2].url;
//                             } else {
//                                 record.record.cover = recordItem.image[0].url;
//                             }
//                         }
//                     }
//                     let parsed = title.match(/((.*?){0,1}staroetv.su(.*?){0,1})?[\])\\/ ]{0,2}(.*?)\((.*?), (.*?)\)(.*)/);
//                     if (!parsed) {
//                         let newParsed = title.match(/(.*?){0,1}staroetv.su(.*?){0,1} (.*?) - (.*?) \((.*?)\)(.*?)/);
//                         if (newParsed && newParsed.length === 7) {
//                             parsed = [
//                                 '', '', '', '', newParsed[4], newParsed[3], newParsed[5], newParsed[6]
//                             ]
//                         }
//                     }
//                     if (parsed && parsed.length === 8) {
//                         parsed = parsed.map(string => {
//                             if (string) {
//                                 return string.trim();
//                             }
//                         });
//                         let interprogram_keys = ["анонс", "вещани", "заставк", "ролик", "программа передач", "эфира", "спонсор", "часы"];
//                         if (parsed[4]) {
//                             let program_lower = parsed[4].toLowerCase();
//                             if (program_lower.indexOf("реклам") !== -1) {
//                                 record.is_interprogram = true;
//                                 record.interprogram_type = 22;
//                             } else {
//                                 interprogram_keys.forEach(interprogram_key => {
//                                     if (program_lower.indexOf(interprogram_key) !== -1) {
//                                         record.is_interprogram = true;
//                                     }
//                                 })
//                             }
//                         }
//                         record._parsed = parsed;
//                         if (this.allChannelNames[parsed[5]]) {
//                             record.channel.id = this.allChannelNames[parsed[5]];
//                         } else {
//                             console.log(`Channel not found: ${parsed[5]} (count: ${Object.keys(this.allChannelNames).length})`);
//                         }
//                         if (record.is_interprogram) {
//                             record.short_description = parsed[4];
//                         } else {
//                             record.short_description = parsed[7];
//                         }
//                         if (record.is_interprogram && record.channel.id) {
//                             this.loadInterprogramPackages(record);
//                         }
//                         let date = parsed[6];
//                         let year_end;
//                         let year;
//                         let month;
//                         let day;
//                         if (date && date !== "") {
//                             date = date.split(";")[0];
//                             date = date.replace("–", "-");
//                             date = date.replace("~", "");
//                             let splitted_min = date.split("-");
//                             if (splitted_min.length === 2) {
//                                 let splitted_min_end = splitted_min[1].split(".");
//                                 if (splitted_min_end.length === 3) {
//                                     if (splitted_min_end[2] !== "") {
//                                         year_end = splitted_min_end[2];
//                                     }
//                                 } else {
//                                     splitted_min[1] = parseInt(splitted_min[1]);
//                                     if (splitted_min[1]) {
//                                         year_end = splitted_min[1];
//                                     }
//                                 }
//                                 year = parseInt(splitted_min[0]);
//                                 date = splitted_min[1];
//                             }
//                             if (typeof date === "number") {
//                                 date = "" + date;
//                             }
//                             if (date && date.split(".").length !== 3 && date.split(" ").length !== 3) {
//                                 let splitted = date.split(" ");
//                                 if (splitted.length === 1) {
//                                     year = parseInt(splitted[0]);
//                                 } else if (splitted.length === 2) {
//                                     year = parseInt(splitted[1]);
//                                     month = splitted[0].toLowerCase();
//                                     if (monthNames[month]) {
//                                         month = monthNames[month];
//                                     }
//                                 }
//                             } else {
//                                 if (date && date.split(".").length !== 3) {
//                                     let splitted = date.split(" ");
//
//                                     day = splitted[0];
//                                     if (monthNames[splitted[1]]) {
//                                         month = monthNames[splitted[1]];
//                                     }
//                                     year = splitted[2];
//                                 } else {
//                                     date = date.trim();
//                                     date = date.replace('/[^0-9.]+/', '');
//                                     let splitted = date.split(".");
//                                     day = splitted[0];
//                                     month = splitted[1];
//                                     year = splitted[2];
//                                 }
//                             }
//                             record.date = {};
//                             if (month) {
//                                 record.date.month = parseInt(month);
//                             }
//                             if (year) {
//                                 record.date.year = parseInt(year);
//                             }
//                             if (day) {
//                                 record.date.day = parseInt(day);
//                             }
//                         }
//                     }
//                     if (recordItem.file) {
//                         record.storage_file = recordItem.file;
//                     } else {
//                         const file = this.files.map(file => file.replace('.mp4', '')).filter(file => file === record.record.title)[0];
//                         if (file) {
//                             record.storage_file = `${file}.mp4`;
//                         }
//                     }
//                     if (!record.channel.id) {
//                         let names = Object.keys(this.allChannelNames);
//                         let name = names.filter(name => name.length > 0 && title.indexOf(name) !== -1)[0];
//                         if (name) {
//                             record.channel.id = this.allChannelNames[name];
//                         }
//                     }
//                     record.collapsed = record.date.year && record.date.year >= 2011;
//                     this.parsedRecords.push(record);
//                 }
//                 for (let record of this.parsedRecords) {
//                     await this.findProgram(record);
//                 }
//             }
//         },
//         loadMore() {
//             this.loading = true;
//             $.post(route('mass-upload.index'), {source: this.source, next_page_token: this.nextPageToken}).then(res => {
//                 this.loading = false;
//                 if (res.status) {
//                     if (res.data.next_page_token) {
//                         this.nextPageToken = res.data.next_page_token;
//                     }
//                     this.records = [...this.records, ...res.data.items];
//                     this.files = res.data.files;
//                     this.parseInfo();
//                 } else {
//                     this.$refs.snackbar.show(res);
//                 }
//             })
//         },
//         load() {
//             this.loading = true;
//             let data = {source: this.source};
//             if (this.nextPageToken !== '') {
//                 data.next_page_token = this.nextPageToken;
//             }
//             this.response = null;
//             $.post(route('mass-upload.index'), data).then(res => {
//                 this.loading = false;
//                 if (res.status) {
//                     if (res.data.next_page_token) {
//                         this.nextPageToken = res.data.next_page_token;
//                     }
//                     this.records = res.data.items;
//                     this.files = res.data.files;
//                     this.parseInfo();
//                 } else {
//                     this.response = res;
//                 }
//             }).catch((e) => {
//                 console.log(e);
//                 this.response = e.responseJSON || {status: 0, text: 'Ошибка, повторите позже'};
//                 this.loading = false;
//             })
//         },
//         loadInterprogramPackages(record, forceReload = false) {
//             if (!forceReload && (this.interprogramPackagesLoading[record.channel.id] || this.interprogramPackages[record.channel.id])) {
//                 return;
//             }
//             this.interprogramPackagesLoading[record.channel.id] = true;
//             $.get('/channels/' + record.channel.id + '/graphics/ajax').done(res => {
//                 this.$set(this.interprogramPackages, record.channel.id, res.data.graphics);
//                 this.interprogramPackagesLoading[record.channel.id] = false;
//             })
//         },
//         loadCategories() {
//             $.get('/records/categories').done(res => {
//                 this.categories = res.data.categories;
//             })
//         },
//         loadChannels() {
//             $.get('/channels/ajax').then(res => {
//                 this.channelsList = res.data.channels;
//             })
//         },
//         loadPrograms(record, forceReload = false) {
//             return new Promise(resolve => {
//                 if (!forceReload && (this.programsLoading[record.channel.id] || this.programs[record.channel.id])) {
//                     resolve();
//                     return;
//                 }
//                 this.programsLoading[record.channel.id] = true;
//                 $.get('/channels/' + record.channel.id + '/programs').done(res => {
//                     this.$set(this.programs, record.channel.id, res.data.programs);
//                     this.programsLoading[record.channel.id] = false;
//                     resolve();
//                 })
//             })
//
//         },
//         setAdvertising(record) {
//             this.$set(record, 'is_advertising', !record.is_advertising);
//             if (record.is_advertising) {
//                 record.is_clip = false;
//                 record.is_interprogram = false;
//             }
//         },
//         setIsProgramDesign(record) {
//             this.$set(record, 'is_program_design', !record.is_program_design);
//             this.$set(record, 'is_interprogram', true);
//         },
//         setInterprogram(record) {
//             this.$set(record, 'is_interprogram', !record.is_interprogram);
//             if (record.is_interprogram) {
//                 record.is_advertising = false;
//                 record.is_clip = false;
//                 if (record.channel.id) {
//                     this.loadInterprogramPackages(record);
//                 }
//             }
//         },
//     }
// }
</script>
