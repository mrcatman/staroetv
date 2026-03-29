
export const Loader = {
    rootEl: document.getElementById('loader')!,
    percent: 0,
    percentEl: document.getElementById('percent')!,
    increment(percent: number) {
        this.percent += percent;
        this.percentEl.innerHTML = `${this.percent}%`;

        if (this.percent >= 100) {
            this.rootEl.style.display = 'none';
        }
    }
}
