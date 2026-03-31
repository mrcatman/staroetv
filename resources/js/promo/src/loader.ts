
export const Loader = {
    rootEl: document.getElementById('loader')!,
    percent: 0,
    percentEl: document.getElementById('loader_percent')!,
    lineEl: document.getElementById('loader_line')!,
    increment(percent: number) {
        this.percent += percent;
        this.percentEl.innerHTML = `${this.percent}%`;
        this.lineEl.style.top = `${100 - this.percent}%`;

        if (this.percent >= 100) {
            this.rootEl.style.display = 'none';
        }
    }
}
