import * as tus from "tus-js-client";
import {ref} from "vue";
import { getError, getErrorMessage } from "../utils/errors";

export const useTusUpload = (isRadio?: boolean) => {

    const file = ref<File>();
    const needUpload = ref<boolean>(false);
    const setFile = (_file: File) => {
        file.value = _file;
        needUpload.value = !!_file;
    }

    const endpoint = ref<string>();
    const setEndpoint = (_endpoint: string) => {
        endpoint.value = _endpoint;
    }

    const isUploading = ref<boolean>(false);
    let uploadError = ref<string>();

    let percent = ref<number>(0);
    let url = ref<string>();
    let thumbnail = ref<string>();

    const upload = () => {
        isUploading.value = true;
        return new Promise((resolve, reject) => {
            const tusUpload = new tus.Upload(file.value, {
                endpoint: endpoint.value,
                retryDelays: [0, 1000, 3000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000, 5000],
                chunkSize: 10 * 1048576,
                metadata: {
                    //filename: record.name,
                },
                onError: (error) => {
                    uploadError.value = 'Ошибка загрузки, попробуйте еще раз или напишите администратору'
                    reject(error);
                },
                onProgress: (bytesUploaded, bytesTotal) => {
                    percent.value = Math.floor((bytesUploaded / bytesTotal) * 10000) / 100;
                },
                onSuccess: async (e) => {
                    const uploadId = tusUpload.url.split("/");
                    $.post(route('records.upload.process'), {
                        server_upload_id: uploadId[uploadId.length - 1],
                        is_radio: isRadio
                    }).done(res => {
                        isUploading.value = false;
                        if (!res.status) {
                            uploadError.value = res.text;
                            reject();
                            return;
                        }

                        needUpload.value = false;
                        resolve(res.data);

                        url.value = res.data.url;
                        thumbnail.value = res.data.thumbnail;
                    }).fail(e => {
                        isUploading.value = false;
                        uploadError.value = getErrorMessage(e);

                        reject();
                    });
                }
            })
            tusUpload.start();
            console.log(tusUpload);
        })
    }

    return {
        file,
        setFile,
        setEndpoint,

        needUpload,
        isUploading,
        percent,

        upload,
        uploadError,
        url,
        thumbnail
    }
}
