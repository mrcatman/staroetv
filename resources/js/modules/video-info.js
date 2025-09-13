export const getVideoInfo = async (url) => {
    let id = null, code = null, title = null, covers = [];
    return new Promise(resolve => {
        if (url?.trim().length) {
            const youtubeData = url.match(/^.*((m\.)?youtu\.be\/|vi?\/|u\/\w\/|embed\/|\?vi?=|\&vi?=)([^#\&\?]*).*/);
            if (youtubeData && youtubeData.length === 4) {
                const id = youtubeData[3];
                const code = `<iframe width="560" height="315" src="https://www.youtube.com/embed/${id}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                const covers = ['0', '1', '2', '3', 'hqdefault'].map(frame => `https://img.youtube.com/vi/${id}/${frame}.jpg`);

                $.post('/records/getinfo', {youtube_video_id: id}).done(async res => {
                    if (res.data?.youtube_response?.items?.length) {
                        const record = res.data.youtube_response.items[0].snippet;
                        title = record.title;
                        resolve({id, code, title, covers});
                    }
                });
            } else {
                const vkData = url.match(/(.*?)\/video(.*?)([0-9-_]+)(.*?)/);
                if (vkData && vkData[3].length > 1) {
                    id = vkData[3];
                } else {
                    let vkData = url.match(/(.*?)video_ext.php\?oid=(.*?)&id=(.*?)&(.*?)/);
                    if (vkData) {
                        id = vkData[2] + '_' + vkData[3];
                    }
                }
                if (id?.length) {
                    $.post('/records/getinfo', {vk_video_id: id}).done(async res => {
                        if (res.data?.vk_response?.response?.items?.length) {
                            let record = res.data.vk_response.response.items[0];
                            code = `<iframe width="560" height="315" src=${record.player} frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
                            covers = [record.image[record.image.length - 1].url];
                            title = record.title;
                            resolve({id, code, title, covers});
                        }
                    })
                }
            }
        }
        resolve({id, code, title, covers});

    })
}
