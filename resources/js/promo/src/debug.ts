export const Debug = {
    el: document.getElementById('debug'),
    active: (new URLSearchParams(window.location.search)).get('debug'),
    init() {
        this.el.style.display = this.active ? 'block' : 'none';
    },
    log(...args: any[]) {
        for (let arg of args) {
            const message = typeof arg === 'string' ? arg : JSON.stringify(arg);
            console.log(`[DEBUG] ${message}`);
            if (!this.active) {
                continue;
            }
            this.el.innerHTML += `${message}<br>`;
        }

        if (!this.active) {
            return;
        }

        this.el.innerHTML += `<hr>`;
        setTimeout(() => this.el.scrollTo({
            top: this.el.scrollHeight,
        }), 1);
    }
}
