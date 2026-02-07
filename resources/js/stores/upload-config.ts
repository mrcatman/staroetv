import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useUploadConfigStore = defineStore('upload-config', () => {

    const loaded = ref<boolean>(false);

    const uploadEndpoint = ref<string>();
    const canUpload = ref<boolean>(false);

    const load = () => {
        if (loaded.value) {
            return;
        }

        // @ts-ignore
        $.get(route('records.upload.config')).done(({data}) => {
            uploadEndpoint.value = data.upload_endpoint;
            canUpload.value = data.can_upload;

            loaded.value = true;
        })
    }


    return {
        load,

        uploadEndpoint,
        canUpload
    }
})
