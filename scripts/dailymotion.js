let axios = require('axios');
const sleep = (ms) => {
    return new Promise(resolve => setTimeout(resolve, ms));
}
(async() => {
    let ids = (await axios.get('http://staroetv.mrcatmann.ru/records/dailymotion')).data;
    for (let index in ids) {
        let id = ids[index];
        console.log('ID: '+id);
        try {
            let status = (await axios.post('http://staroetv.mrcatmann.ru/records/download', {record_id: id})).data;
            console.log(status);
        } catch (e) {
            console.log('Error: '+e.toString())
        }
        await sleep(250);
    }
})();
