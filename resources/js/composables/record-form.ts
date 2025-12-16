import { ref, watch } from 'vue';

import { interprogramNames } from "@/consts";
import { parseDate } from "@/utils/dates";

import { getInfo } from "@/utils/records/get-info";
import { guessType } from "@/utils/records/guess-type";

import { useChannelsStore } from "../stores/channels";
import { useProgramsStore } from "../stores/programs";
import { useDesignPackagesStore } from "../stores/design-packages";
import { useCategoriesStore } from "../stores/categories";
import { useTusUpload } from "./tus-upload";

export type RecordsUploadChannelData = {
    name: string,
    id: number,
    unknown: boolean,
}

export type RecordsUploadData = {
    id?: number,
    title: string,
    short_description: string,
    description: string,

    is_radio: boolean,

    thumbnail?: string,
    type: Records.Type,
    original_added_at?: Date,
    author_id?: number,

    record: {
        url?: string,
        code?: string,
        original_id?: string,
        own_code: boolean,
        source?: string,
        thumbnails: string[],

        upload: boolean,
        uploaded_file_path?: string,
    }
    date: {
        year: number,
        month: number,
        day: number,
        year_start: number,
        year_end: number,
    },
    channel: RecordsUploadChannelData,
    program: {
        name: string,
        id: number,
        unknown: boolean,
    },
    interprogram: {
        type: number | string | null,
        package_id: number | null,
    },
    other: {
        category_id: number | null,
    },
    advertising: {
        type?: number,
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
    thumbnail: null,
    type: 'programs',

    record: {
        url: '',
        code: '',
        original_id: '',
        own_code: false,
        source: '',
        thumbnails: [],
        upload: false,
        uploaded_file_path: null,
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
        type: null,
        brand: '',
        region: '',
        country: '',
    },
    interprogram: {
        type: null,
        package_id: null,
    },
    other: {
        category_id: null,
    },

    date: {
        year: -1,
        month: -1,
        day: -1,
        year_start: -1,
        year_end: -1,
    },
};

export const useRecordForm = (startParams?: Partial<RecordsUploadData>, record?: Models.Record) => {

    const categoriesStore = useCategoriesStore();
    const channelsStore = useChannelsStore();
    const programsStore = useProgramsStore();
    const designPackagesStore = useDesignPackagesStore();
    const tusUpload = useTusUpload(startParams?.is_radio);

    const loading = ref<boolean>(false);
    const saving = ref<boolean>(false);
    const loadingInfo = ref<boolean>(false);
    const response = ref<Forms.Response>();
    const errors = ref<Forms.Errors>({});

    const setDefaultData = () => {
        data.value = {
            ...JSON.parse(JSON.stringify(defaultData)),
            ...startParams
        } as RecordsUploadData;
    }

    const load = async () => {
        categoriesStore.load();
        channelsStore.load();

        if (record) {
            data.value = {
                id: record.id,
                title: record.title,
                is_radio: record.is_radio,
                type: guessType(record),
                original_added_at: new Date(record.original_added_at_ts * 1000),
                author_id: record.author_id,
                thumbnail: record.original_cover,
                record: {
                    url: record.original_url,
                    code: record.embed_code,
                    thumbnails: [],
                    own_code: false,
                    upload: record.use_own_player,
                },
                short_description: record.short_description,
                description: record.description,
                date: {
                    year: record.year,
                    month: record.month,
                    day: record.day,
                    year_start: record.year_start,
                    year_end: record.year_end
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
                    type: record.advertising_type,
                    brand: record.advertising_brand,
                    region: record.region,
                    country: record.country,
                }
            }

            if (record.channel?.id) {
                programsStore.load(record.channel.id);
                designPackagesStore.load(record.channel.id);
            }
        } else {
            setDefaultData();
        }
    }

    const data = ref<RecordsUploadData>();
    const reset = async () => {
        loading.value = true;
        load();

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

    const loadRecordInfo = async() => {
        loadingInfo.value = true;

        const {id, title, code, thumbnails} = await getInfo(data.value.record.url);
        if (id) {
            data.value.title = title;
            data.value.record.original_id = id;
            data.value.record.code = code;
            data.value.record.thumbnails = thumbnails;

            await parseTitle();
        }

        loadingInfo.value = false;
    }

    const parseTitle = async () => {
        const title = data.value.title;

        let parsed: string[] = title.match(/((.*?){0,1}staroetv.su(.*?){0,1})?[\])\\/ ]{0,2}(.*?)\((.*?), (.*?)\)(.*)/);
        if (!parsed) {
            let newParsed = title.match(/(.*?){0,1}staroetv.su(.*?){0,1} (.*?) - (.*?) \((.*?)\)(.*?)/);
            if (newParsed && newParsed.length === 7) {
                parsed = [
                    '', '', '', '', newParsed[4], newParsed[3], newParsed[5], newParsed[6]
                ]
            }
        }
        parsed = parsed.map(string => (string ? string.trim() : ''));
        if (parsed?.length === 8) {

            const program = parsed[4].toLowerCase();
            if (program.includes("реклам") && program.includes("блок")) {
                data.value.type = 'interprogram';
                data.value.interprogram.type = 22; // рекламный блок
            } else if (!!interprogramNames.find(name => program.includes(name))) {
                data.value.type = 'interprogram';
            }
            if (!data.value.channel.name.length) {
                data.value.channel.name = parsed[5];

                const channel = channelsStore.findByName(parsed[5]);
                if (channel) {
                    data.value.channel.id = channel.id;
                }
            }

            if (data.value.type !== 'interprogram' && !data.value.program.name?.length) {
                data.value.program.name = parsed[4];

                const foundProgram = programsStore.findByNameAndChannelId(data.value.program.name, data.value.channel.id);
                data.value.program.id = foundProgram?.id;
            }

            data.value.short_description = data.value.type == 'interprogram' ? parsed[4] : parsed[7];

            if (data.value.type == 'interprogram' && data.value.channel.id) {
                designPackagesStore.load(data.value.channel.id);
            }
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
    }

    watch(() => data.value.channel.id, () => {
        programsStore.load(data.value.channel.id);
    })


    let saveCallback: (record: Models.Record) => void;
    const setSaveCallback = (callback: (record: Models.Record) => void) => {
        saveCallback = callback;
    }

    const save = async () => {
        try {
            await tusUpload.upload();
            tusUpload.setFile(null);
        } catch (e) {
            console.log(e, tusUpload.uploadError);
            return;
        }

        saving.value = true;

        if (tusUpload.url.value) {
            data.value.record.uploaded_file_path = tusUpload.url.value;
        }

        if (tusUpload.thumbnail.value) {
            data.value.thumbnail = tusUpload.thumbnail.value;
        }

        $.post(record ? route('records.edit', record.id) : route('records.save'), data).done(res => {
            saving.value = false;
            response.value = res;
            errors.value = res.errors || {};

            if (res.status) {
                saveCallback && saveCallback(res.data.record as Models.Record);

                if (!record) {
                    setDefaultData();
                }
            }
        });
    }


    return {
        loading,
        saving,
        loadingInfo,
        save,

        data,
        response,
        errors,
        setSaveCallback,

        setUploadEndpoint: tusUpload.setEndpoint,
        setUploadFile: tusUpload.setFile,
        isUploadingFile: tusUpload.isUploading,
        uploadPercent: tusUpload.percent,
    }
}
