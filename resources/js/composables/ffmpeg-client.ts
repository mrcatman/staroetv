import { ref } from "vue";

export const useFFmpegClient = (onStatusTextChange: (text: string) => void) => {

    let ffmpegInstance;

    const ready = ref<boolean>(false);

    const init = async (url: string) => {
        const script = document.createElement('script');
        onStatusTextChange(`Загрузка ffmpeg...`);

        script.onload = async () => {
            const FFmpeg = (window as any).FFmpeg;

            ffmpegInstance = FFmpeg.createFFmpeg({
                log: true,
                progress: ({ ratio }: { ratio: number }) => {
                    console.log('Progress', ratio);
                },
            });

            onStatusTextChange(`Инициализация ffmpeg...`);
            await ffmpegInstance.load();

            onStatusTextChange(`Загрузка файла видео...`);
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);

            xhr.responseType = 'arraybuffer';
            xhr.onload = async () => {
                if (xhr.status === 200) {
                    const videoData = new Uint8Array(xhr.response);
                    await ffmpegInstance.write("source.mp4", videoData);

                    ready.value = true;
                    onStatusTextChange(`Готово к запуску`);
                }
            };
            xhr.send();
        };

        script.src = "https://unpkg.com/@ffmpeg/ffmpeg@0.8.3/dist/ffmpeg.min.js";
        document.head.appendChild(script);
    };

    const convert = async (from: number, to: number, index: number) => {
        await ffmpegInstance.run(`-i source.mp4 -vcodec libx264 -acodec copy -threads 5 -ss ${from} -to ${to} output_${index}.mp4`);
        return await ffmpegInstance.read(`output_${index}.mp4`);
    }

    return {
        init,
        convert,
        ready
    }

}
