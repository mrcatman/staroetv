import { Resources } from "./src/resources";
import { Database } from "./src/database";


document.addEventListener('DOMContentLoaded', async () => {
    Resources.loadAll();
    Database.loadRequired();
})
