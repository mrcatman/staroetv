import { computed, ref, watch } from 'vue';

import { interprogramNames } from "@/consts";
import {defaultDate, parseDate} from "@/utils/dates";

import { getInfo } from "@/utils/records/get-info";
import { guessType } from "@/utils/records/guess-type";

import { useChannelsStore } from "../stores/channels";
import { useProgramsStore } from "../stores/programs";
import { useDesignPackagesStore } from "../stores/design-packages";
import { useCategoriesStore } from "../stores/categories";
import { useTusUpload } from "./tus-upload";
import { findByName as findChannelByName, getNameByDate } from "../utils/channels";
import {generateInterprogramTitle, generateTitle} from "../utils/records/generate-title";
import { useUploadConfigStore } from "../stores/upload-config";
import { storeToRefs } from "pinia";

export type RecordsUploadRelationData = {
    name: string,
    id: number,
    unknown: boolean,
}


export type RecordsUploadData = {
    id?: number,
    title: string,
    short_description: string,
    description: string,
    source?: string,
    is_radio: boolean,

    type: Records.Type,
    original_added_at?: string,
    author_id?: number,

    record: {
        url?: string,
        code?: string,
        original_id?: string,
        own_code: boolean,
        thumbnail_id?: string,
        thumbnail_url?: string,
        thumbnails: string[],

        upload: boolean,
        uploaded_file_url?: string,
        storage_file?: string,
        move_to_storage?: boolean,
    }
    date: Common.Date,
    channel: RecordsUploadRelationData,
    program: RecordsUploadRelationData,
    interprogram: {
        type: number | string | null,
        package_id: number | null,
    },
    other: {
        category_id: number | null,
    },
    advertising: {
        type?: number,
        category?: string,
        brand?: string,
        region?: string,
        country?: string,
    },


}

const defaultData: RecordsUploadData = {
    title: '',
    short_description: '',
    description: '',
    is_radio: false,

    type: 'programs',
    source: '',
    record: {
        url: '',
        code: '',
        original_id: '',
        own_code: false,
        thumbnail_id: null,
        thumbnail_url: null,
        thumbnails: [],
        upload: false,
        uploaded_file_url: null,
        move_to_storage: false,
    },
    channel: {
        name: '',
        id: null,
        unknown: false,
    },
    program: {
        name: '',
        id: null,
        unknown: false,
    },
    advertising: {
        type: -1,
        category: '',
        brand: '',
        region: '',
        country: '',
    },
    interprogram: {
        type: -1,
        package_id: -1,
    },
    other: {
        category_id: -1,
    },

    date: defaultDate()
};

export const useRecordForm = (startParams?: Partial<RecordsUploadData>, record?: Models.Record, _titleLocked: boolean = false) => {

    const categoriesStore = useCategoriesStore();
    const channelsStore = useChannelsStore();
    const programsStore = useProgramsStore();
    const uploadConfigStore = useUploadConfigStore();
    //const designPackagesStore = useDesignPackagesStore();
    const tusUpload = useTusUpload(startParams?.is_radio);

    const externalVideoError = ref<string>(null);
    const loading = ref<boolean>(false);
    const saving = ref<boolean>(false);
    const loadingInfo = ref<boolean>(false);
    const response = ref<Forms.Response>();
    const errors = ref<Forms.Errors>({});

    const similar = ref<Models.Record[]>([]);
    const duplicates = ref<Models.Record[]>([]);
    const similarChecked = ref<boolean>(false);

    const setDefaultData = () => {
        data.value = {
            ...JSON.parse(JSON.stringify(defaultData)),
            ...startParams
        } as RecordsUploadData;
    }

    const load = async () => {
        const promises = [
            uploadConfigStore.load(),
            categoriesStore.load(),
            channelsStore.load()
        ];

        if (record) {
            data.value = {
                id: record.id,
                title: record.title,
                is_radio: record.is_radio,
                type: guessType(record),
                original_added_at: new Date(record.original_added_at_ts * 1000).toISOString().slice(0, 10),
                author_id: record.author_id,

                source: record.source,
                record: {
                    url: record.original_url ?? '',
                    code: record.embed_code,
                    thumbnail_url: record.cover_picture?.url,
                    thumbnail_id: record.cover_id,
                    thumbnails: [],
                    own_code: false, // todo check youtube/vk
                    upload: record.use_own_player,
                },
                short_description: record.short_description,
                description: record.description,
                date: {
                    year: record.year ?? -1,
                    month: record.month ?? -1,
                    day: record.day ?? -1,
                    day_start: record.day_start ?? -1,
                    day_end: record.day_end ?? -1,
                    month_start: record.month_start ?? -1,
                    month_end: record.month_end ?? -1,
                    year_start: record.year_start ?? -1,
                    year_end: record.year_end ?? -1,
                    range: !!record.year_start
                },
                program: {
                    name: record.program ? record.program.name : '',
                    id: record.program ? record.program.id : null,
                    unknown: !(record.program_id > 0) && !record.is_interprogram && !record.is_clip && !record.is_advertising,
                },
                channel: {
                    name: record.channel ? record.channel.name : '',
                    id: record.channel ? record.channel.id : null,
                    unknown: !(record.channel_id > 0),
                },
                interprogram: {
                    type: record.interprogram_type,
                    package_id: record.interprogram_package_id
                },
                other: {
                    category_id: record.other_category_id,
                },
                advertising: {
                    type: record.advertising_type || -1,
                    category: record.advertising_category,
                    brand: record.advertising_brand,
                    region: record.region,
                    country: record.country,
                }
            }

            if (record.channel?.id > 0) {
                promises.push(programsStore.load(record.channel.id));
                //designPackagesStore.load(record.channel.id);
            }
            await Promise.all(promises);
        } else {
            setDefaultData();
        }
    }

    const data = ref<RecordsUploadData>();
    const reset = async () => {
        loading.value = true;
        await load();
        loading.value = false;
    }
    reset();

    let changeUrlTimeout: number;
    watch(() => data.value.record.url, () => {
        clearTimeout(changeUrlTimeout);
        changeUrlTimeout = setTimeout(() => {
            if (!loading.value) {
                loadRecordInfo();
            }
        }, 500)
    });

    watch(() => data.value.type, (type) => {
        if (!['advertising', 'interprogram', 'program-design'].includes(type)) {
            data.value.date.range = false;
        }
    })

    const loadRecordInfo = async () => {
        similar.value = [];
        duplicates.value = [];

        externalVideoError.value = null;

        if (!data.value.record.url?.trim().length) {
            return;
        }

        loadingInfo.value = true;
        try {
            const {
                id, title, description, code, thumbnails
            } = await getInfo(data.value.record.url, data.value.id);
            if (id) {
                data.value.title = title;
                data.value.description = description;
                data.value.record.original_id = id;
                data.value.record.code = code;
                data.value.record.thumbnails = thumbnails;
                if (thumbnails?.length) {
                    data.value.record.thumbnail_url = thumbnails[0];
                }
                tusUpload.setFile(null);
                await parseTitle();
            }
        } catch (e) {
            externalVideoError.value = e.text;
            if (e?.list) {
                duplicates.value = e.list;
            }
        }

        loadingInfo.value = false;
    }

    const codeValid  = computed(() => {
        if (!data.value.record.own_code) {
            return true;
        }
        return !data.value.record.code?.length || /<iframe.*?\<\/iframe>/gi.test(data.value.record.code);
    })

    const titleLoading = ref<boolean>(false);
    const parseTitle = async () => {
        const title = data.value.title;
        if (!title.trim().length) {
            return;
        }
        titleLoading.value = true;

        let parsed: string[] = title.match(/((.*?){0,1}staroetv.su(.*?){0,1})?[\])\\/ ]{0,2}(.*?)\((.*?), (.*?)\)(.*)/);
        if (!parsed) {
            let newParsed = title.match(/(.*?){0,1}staroetv.su(.*?){0,1} (.*?) - (.*?) \((.*?)\)(.*?)/);
            if (newParsed && newParsed.length === 7) {
                parsed = [
                    '', '', '', '', newParsed[4], newParsed[3], newParsed[5], newParsed[6]
                ]
            }
        }

        if (parsed?.length === 8) {
            parsed = parsed.map(string => (string ? string.trim() : ''));
            let program = parsed[4].toLowerCase();
            if (program.startsWith("реклам")) {
                data.value.type = 'interprogram';
                data.value.interprogram.type = 22; // рекламный блок
            } else if (program.includes("заставка пр")) {
                data.value.type = 'program-design';
            } else if (!!interprogramNames.find(name => program.includes(name))) {
                data.value.type = 'interprogram';
                const interprogramType = categoriesStore.interprogramTypes.find(type => program.includes(type.text.toLocaleLowerCase()))
                if (interprogramType) {
                    data.value.interprogram.type = interprogramType.id;
                }
            } else {
                data.value.type = 'programs';
            }
            data.value.channel.name = parsed[5];

            const channels = data.value.is_radio ? channelsStore.radioStations : channelsStore.channels;
            const { channel, name} = findChannelByName(parsed[5], channels);
            if (channel) {
                data.value.channel.name = name;
                data.value.channel.id = channel.id;
            }

            if (data.value.type !== 'interprogram') {
                data.value.program.name = parsed[4];

                if (data.value.channel.id > 0) {
                    await programsStore.load(data.value.channel.id);
                    const foundProgram = programsStore.findByNameAndChannelId(data.value.program.name, data.value.channel.id);
                    data.value.program.id = foundProgram?.id;
                }
            }

            data.value.short_description = data.value.type == 'interprogram' ? parsed[4] : parsed[7];

            // if (data.value.type == 'interprogram' && data.value.channel.id) {
            //     designPackagesStore.load(data.value.channel.id);
            // }
            const {month, year, day} = parseDate(parsed[6]);

            if (month) {
                data.value.date.month = parseInt(month);
            }
            if (year) {
                data.value.date.year = parseInt(year);
            }
            if (day) {
                data.value.date.day = parseInt(day);
            }
        }
        updateChannelName();
        titleLoading.value = false;
    }

    watch(() => data.value.channel.id, (channelId) => {
        channelId ? (channelId > 0 && programsStore.load(channelId)) : (data.value.program.id = null);
    })

    const titleLocked = ref<boolean>(_titleLocked);
    const updateTitle = () => {
        if (!titleLocked.value || titleLoading.value) {
            return;
        }
        if (data.value.type === 'interprogram') {
            const category = categoriesStore.categories.find(category => category.id === parseInt(data.value.interprogram.type));
            data.value.title = generateInterprogramTitle(data.value, category);
        } else {
            data.value.title = generateTitle(data.value);
        }
    }

    const updateChannelName = () => {
        if (!titleLocked.value || titleLoading.value || !data.value.channel.id || data.value.channel.unknown) {
            return;
        }
        const channels = data.value.is_radio ? channelsStore.radioStations : channelsStore.channels;
        const channel = channels.find(channel => channel.id === data.value.channel.id);
        const name = getNameByDate(channel, data.value.date);
        if (!name) {
            return;
        }
        data.value.channel.name = name;
    }

    watch(() => [
        titleLocked.value,
        data.value.type,
        data.value.channel.name, data.value.channel.unknown,
        data.value.program.name, data.value.program.unknown,
        data.value.short_description, data.value.interprogram.type,
        data.value.advertising.category, data.value.advertising.brand, data.value.advertising.type,
        data.value.date.year_start, data.value.date.year_end,
        data.value.date.month_start, data.value.date.month_end,
        data.value.date.day_start, data.value.date.day_end,
        data.value.date.range,  data.value.date.year, data.value.date.month, data.value.date.day
    ], updateTitle)

    watch(() => [
        data.value.date.range,
        data.value.date.year_start, data.value.date.year_end,
        data.value.date.month_start, data.value.date.month_end,
        data.value.date.day_start, data.value.date.day_end,
        data.value.date.year, data.value.date.month, data.value.date.day
    ], updateChannelName)

    watch(() => data.value.type, (type) => {
       if (type === 'other') {
           titleLocked.value = false;
       }
    });


    let saveCallback: (record: Models.Record, errors: Forms.Errors) => void;
    const setSaveCallback = (callback: (record: Models.Record, errors: Forms.Errors) => void) => {
        saveCallback = callback;
    }


    const getSimilar = (): Promise<Models.Record[]> => {
        return new Promise((resolve, reject) => {
            $.get(route('records.similar'), data.value).done(({data}) => {
                resolve(data);
            }).catch(() => resolve([]));
        })
    }

    const save = async () => {
        if (similarChecked.value) {
            return update();
        }
        try {
            similar.value = await getSimilar();
            if (!similar.value.length) {
                return update();
            }
        } catch (e) {
            similar.value = [];
        }
    }


    const markSimilarAsChecked = () => {
        similarChecked.value = true;
        return update();
    }

    const update = async () => {
        saving.value = true;
        if (tusUpload.needUpload.value) {
            tusUpload.setEndpoint(uploadConfigStore.uploadEndpoint);
            try {
                await tusUpload.upload();
            } catch (errorText) {
                response.value = {
                    status: 0,
                    text: errorText
                }
                return;
            }
        }


        if (tusUpload.url.value) {
            data.value.record.uploaded_file_url = tusUpload.url.value;
            if (tusUpload.thumbnail.value) {
                data.value.record.thumbnail_url = tusUpload.thumbnail.value;
            }
        }
        $.post(record ? route('records.update', record.id) : route('records.save'), data.value).done(res => {
            saving.value = false;
            response.value = res;
            errors.value = res.errors || {};
            saveCallback && saveCallback(res.data?.record as Models.Record, Object.keys(errors.value).length);
            if (res.status) {

                if (!record) {
                    setDefaultData();
                }
            }
        }).catch(e => {
            saving.value = false;
            response.value = {status: 0, text: 'Неизвестная ошибка, попробуйте позже или отпишитесь на форуме'};
            saveCallback && saveCallback(null, true);
        });
    }

    const setUploadFile = (file: File) => {
        tusUpload.setFile(file);
        if (file) {
            data.value.record.upload = true;
            data.value.title = file.name.split('.').filter((s, index, arr) => {
                return index < arr.length - 1
            }).join('.');
            parseTitle();
        } else {
            data.value.record.upload = false;
            data.value.title = '';
            if (!record) {
                setDefaultData();
            }
        }
    }

    const {
        canUpload
    } = storeToRefs(uploadConfigStore);

    return {
        loading,
        saving,
        loadingInfo,
        save,
        update,

        externalVideoError,
        codeValid,
        titleLocked,
        parseTitle,

        data,
        response,
        errors,
        setSaveCallback,

        similar,
        duplicates,
        markSimilarAsChecked,

        setUploadFile,
        uploadFile: tusUpload.file,
        canUpload,

        isUploadingFile: tusUpload.isUploading,
        uploadPercent: tusUpload.percent,
    }
}
