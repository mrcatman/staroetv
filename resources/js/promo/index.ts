import { Resources } from "./src/resources";
import { Database } from "./src/database";
import { About } from "./src/about";
import { Playback } from "./src/playback";

document.addEventListener('DOMContentLoaded', async () => {
    Resources.loadAll();
    Database.loadRequired();
    About.init();
    Playback.init();
})
