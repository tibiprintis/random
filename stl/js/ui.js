window.UI = {
    init() {
        document.getElementById('controls').innerHTML = `
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
            </div>
            <div><button id="convert">Convert</button></div>
        `;
    },
    notify(obj) {
        const n = document.getElementById('notifications');
        const msg = Array.isArray(obj) ? obj : [obj];
        n.innerHTML = msg.map(m => `<div class="${m.type||'info'}">${m.message}</div>`).join('');
    }
};
