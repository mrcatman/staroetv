const YOUTUBE_REGEX = /^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/;
const VK_REGEX = /(.*?)\/video(.*?)([0-9-_]+)(.*?)/;
const VK_REGEX_EMBED = /(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&(.*?)/;
const RUTUBE_REGEX = /rutube\.ru\/(?:video|play\/embed)\/(?:private\/)?([0-9a-f]{32})/i;

const getVideoTypeAndId = (url: string): [string, string] => {
    const youtubeData = url.match(YOUTUBE_REGEX);
    if (youtubeData?.length === 4 && youtubeData[3].length === 11) {
        return ['youtube', youtubeData[3]];
    }

    const vkData = url.match(VK_REGEX);
    if (vkData?.[3]?.length > 1) {
        return ['vk', vkData[3]];
    }
    const vkEmbedData = url.match(VK_REGEX_EMBED);
    if (vkEmbedData?.[3]?.length) {
        return ['vk', `${vkData[2]}_${vkData[3]}`];
    }

    const rutubeData = url.match(RUTUBE_REGEX);
    if (rutubeData?.[1]?.length) {
        return ['rutube', rutubeData[1]];
    }
    return [null, null];
}

export const getInfo = async (url: string, recordId?: string): Promise<{id: string, duration: number, player: string, code: string, title: string, thumbnails: string[]}> => {
    return new Promise((resolve, reject) => {
        let [videoType, videoId] = getVideoTypeAndId(url);
        if (!videoId) {
            return reject({text: 'Некорректный URL видео'});
        }

        $.post(route('records.get-info'), {video_id: videoId, video_type: videoType, record_id: recordId}).done(async res => {
            if (res.status) {
                resolve(res.data);
            } else {
                reject(res);
            }
        }).catch(() => {
            reject({text: 'Видео не распознано'});
        });
    })
}

