let axios = require('axios');
const sleep = (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
}
(async() => {
    let ids = (await axios.get('https://staroetv.su/records/get-download-ids')).data;
    for (let index in ids) {
        let id = ids[index];
        console.log('ID: '+id);
        try {
           let status = (await axios.post('https://staroetv.su/records/download', {record_id: id})).data;
            console.log(status);
            let screenshotStatus = (await axios.post('https://staroetv.su/records/screenshot', {record_id: id})).data;
            console.log(screenshotStatus);
        } catch (e) {
            console.log('Error: '+e.toString())
        }
        await sleep(250);
    }
})();
