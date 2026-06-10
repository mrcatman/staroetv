import { ref } from 'vue';
import { defineStore } from 'pinia';

export const useUploadConfigStore = defineStore('upload-config', () => {

    const loading = ref<boolean>(false);
    const loaded = ref<boolean>(false);

    const uploadEndpoint = ref<string>();
    const canUpload = ref<boolean>(false);

    const load = () => {
        if (loaded.value || loading.value) {
            return;
        }
        loading.value = true;

        // @ts-ignore
        $.get(route('records.upload.config')).done(({data}) => {
            uploadEndpoint.value = data.upload_endpoint;
            canUpload.value = data.can_upload;

            loading.value = false;
            loaded.value = true;
        })
    }


    return {
        load,

        uploadEndpoint,
        canUpload
    }
})
