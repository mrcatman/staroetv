import { ref } from "vue";

export const useFFmpegClient = () => {

    let ffmpegInstance;

    const statusText = ref<string>('');
    const ready = ref<boolean>(false);

    const init = async (url: string) => {
        const script = document.createElement('script');
        statusText.value = `Загрузка ffmpeg...`;

        script.onload = async () => {
            const FFmpeg = (window as any).FFmpeg;

            ffmpegInstance = FFmpeg.createFFmpeg({
                log: true,
                progress: ({ ratio }: { ratio: number }) => {
                    console.log('Progress', ratio);
                },
            });

            statusText.value = `Инициализация ffmpeg...`;
            await ffmpegInstance.load();

            statusText.value = `Загрузка файла видео...`;
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);

            xhr.responseType = 'arraybuffer';
            xhr.onload = async () => {
                if (xhr.status === 200) {
                    const videoData = new Uint8Array(xhr.response);
                    await ffmpegInstance.write("source.mp4", videoData);

                    ready.value = true;
                    statusText.value = `Готово к запуску`;
                }
            };
            xhr.send();
        };

        script.src = "https://unpkg.com/@ffmpeg/ffmpeg@0.8.3/dist/ffmpeg.min.js";
        document.head.appendChild(script);
    };

    return {
        init,
        statusText,
        ready
    }

}
