const YOUTUBE_REGEX = /^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/;
const VK_REGEX = /(.*?)\/video(.*?)([0-9-_]+)(.*?)/;
const VK_REGEX_EMBED = /(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&(.*?)/;

export const getInfo = async (url: string, recordId?: string): Promise<{id: string, duration: number, player: string, code: string, title: string, thumbnails: string[]}> => {
    return new Promise((resolve, reject) => {
        let parsedId: string;
        let videoType: 'youtube' | 'vk';

        const youtubeData = url.match(YOUTUBE_REGEX);
        if (youtubeData?.length === 4 && youtubeData[3].length === 11) {
            parsedId = youtubeData[3];
            videoType = 'youtube';
        } else {
            const vkData = url.match(VK_REGEX);
            if (vkData && vkData[3]?.length > 1) {
                parsedId = vkData[3];
                videoType = 'vk';
            } else {
                const vkData = url.match(VK_REGEX_EMBED);
                if (vkData) {
                    parsedId = vkData[2] + '_' + vkData[3];
                    videoType = 'vk';
                }
            }
        }
        if (!parsedId) {
            return reject({text: 'Некорректный ID видео'});
        }

        $.post(route('records.get-info'), {video_id: parsedId, video_type: videoType, record_id: recordId}).done(async res => {
            if (res.status) {
                resolve(res.data);
            } else {
                reject(res);
            }
        }).catch(() => {
            reject({text: 'Ошибка сети, повторите позже'});
        });
    })
}

