import * as tus from "tus-js-client";
import {ref} from "vue";
import { useUploadConfigStore } from "../stores/upload-config";

export const useTusUpload = () => {

    const uploadConfigStore = useUploadConfigStore();

    const file = ref<File>();
    const needUpload = ref<boolean>(false);


    const setFile = (_file: File) => {
        file.value = _file;
        needUpload.value = !!_file;
    }

    const isUploading = ref<boolean>(false);
    const percent = ref<number>(0);
    const uploadId = ref<string>();

    const upload = () => {
        uploadId.value = null;
        isUploading.value = true;
        return new Promise<void>((resolve, reject) => {
            const tusUpload = new tus.Upload(file.value, {
                endpoint: uploadConfigStore.uploadEndpoint,
                retryDelays: [0, 1000, 3000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000],
                chunkSize: 10 * 1048576,
                metadata: {
                    filename: file.value.name,
                },
                onError: (error) => {
                    console.log(error);
                    reject('Ошибка загрузки, попробуйте еще раз или напишите администратору');
                },
                onProgress: (bytesUploaded, bytesTotal) => {
                    percent.value = Math.floor((bytesUploaded / bytesTotal) * 10000) / 100;
                },
                onSuccess: async (e) => {
                    needUpload.value = false;
                    isUploading.value = false;

                    uploadId.value = tusUpload.url.split("/").pop();
                    resolve();
                }
            })
            tusUpload.start();
        })
    }

    return {
        file,
        setFile,

        needUpload,
        isUploading,
        percent,

        upload,
        uploadId
    }
}
