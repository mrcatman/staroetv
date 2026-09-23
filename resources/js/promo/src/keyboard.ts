import { Controls } from "./controls";

export const Keyboard = {
    init() {
        document.addEventListener('keydown', (e) => {
            switch (e.code) {
                case 'Space':
                    Controls.onOff();
                    break;
                case 'ArrowLeft':
                    Controls.changeChannel(-1);
                    break;
                case 'ArrowRight':
                    Controls.changeChannel(1);
                    break;
                case 'ArrowUp':
                    Controls.changeVolume(1);
                    break;
                case 'ArrowDown':
                    Controls.changeVolume(-1);
                    break;
                case 'KeyR':
                    Controls.randomChannel();
                    break;
                case 'KeyA':
                    Controls.commercials();
                    break;
                case 'KeyF':
                    Controls.toggleFullscreen();
                    break;
                default:
                    if (e.code.startsWith('Digit')) {
                        Controls.inputNumber(parseInt(e.key));
                    }
                    break;
            }
        });
    }
}
