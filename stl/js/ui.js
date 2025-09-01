window.UI = {
    init() {
        const c = document.getElementById('controls');
        c.innerHTML = `
            <div><input type="file" id="file"></div>
            <div>
                <input type="number" id="width" placeholder="Width mm">
                <input type="number" id="height" placeholder="Height mm">
            </div>
            <div>
                <input type="number" id="segments" placeholder="Segments" value="32" min="3">
                <input type="number" id="offset" placeholder="Offset mm" value="0">
            </div>
            <div>
                <input type="number" id="rotation" placeholder="Rotation speed" value="0.01" step="0.01">
                <label>Mesh color <input type="color" id="color" value="#6699ff"></label>
                <label><input type="checkbox" id="autorotate" checked> Auto rotate</label>
            </div>
            <div>
                <button id="convert">Convert</button>
                <button id="themeToggle">Toggle Theme</button>
            </div>
        `;
        const savedTheme = localStorage.getItem('theme') || 'dark';
        this.applyTheme(savedTheme);
    },
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
    },
    toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        this.applyTheme(current);
    },
    notify(obj) {
        const n = document.getElementById('notifications');
        const msg = Array.isArray(obj) ? obj : [obj];
        n.innerHTML = msg.map(m => `<div class="${m.type||'info'}">${m.message}</div>`).join('');
    }
};
