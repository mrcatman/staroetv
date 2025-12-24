const YOUTUBE_REGEX = /^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/;
const VK_REGEX = /(.*?)\/video(.*?)([0-9-_]+)(.*?)/;
const VK_REGEX_EMBED = /(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&(.*?)/;

const iframeCode = (url: string) => {
    return `<iframe width="560" height="315" src="${url}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
}

export const getInfo = async (url: string): Promise<{id: string, code: string, title: string, thumbnails: string[]}> => {
    let id: string = null, code: string = null, title: string = null, thumbnails: string[] = [];
    return new Promise((resolve, reject) => {
        if (!url?.trim().length) {
            return reject();
        }
        const _resolve = () => resolve({id, code, title, thumbnails});
        const youtubeData = url.match(YOUTUBE_REGEX);
        if (youtubeData && youtubeData.length === 4) {
            const id = youtubeData[3];
            const code = iframeCode(`https://www.youtube.com/embed/${id}`);
            const thumbnails = ['0', '1', '2', '3', 'hqdefault'].map(frame => `https://img.youtube.com/vi/${id}/${frame}.jpg`);

            $.post(route('records.get-info'), {youtube_video_id: id}).done(async res => {
                if (res.data?.youtube_response?.items?.length) {
                    const record = res.data.youtube_response.items[0].snippet;
                    title = record.title;
                    return _resolve();
                } else {
                    return reject();
                }
            }).catch(() => {
                return _resolve(); // даём добавить видео при ошибках сети
            });
        } else {
            const vkData = url.match(VK_REGEX);
            if (vkData[3]?.length > 1) {
                id = vkData[3];
            } else {
                const vkData = url.match(VK_REGEX_EMBED);
                if (vkData) {
                    id = vkData[2] + '_' + vkData[3];
                }
            }
            if (id?.length) {
                $.post(route('records.get-info'), {vk_video_id: id}).done(async res => {
                    if (res.data?.vk_response?.response?.items?.length) {
                        const record = res.data.vk_response.response.items[0];
                        code = iframeCode(record.player);
                        thumbnails = [record.image[record.image.length - 1].url];
                        title = record.title;
                        resolve({id, code, title, thumbnails});
                    } else {
                        return reject();
                    }
                }).catch(() => {
                    return _resolve();
                });
            } else {
                return reject();
            }
        }
    })
}

