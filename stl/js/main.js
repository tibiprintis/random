function init() {
    UI.init();
    Preview.init(document.getElementById('preview'));
    const controls = document.getElementById('controls');

    controls.addEventListener('click', async e => {
        if (e.target.id === 'convert') {
            UI.notify({ type: 'info', message: 'Converting...' });
            const file = document.getElementById('file').files[0];
            const form = new FormData();
            if (file) form.append('file', file);
            form.append('width_mm', document.getElementById('width').value);
            form.append('height_mm', document.getElementById('height').value);
            form.append('offset_mm', document.getElementById('offset').value);
            form.append('segments', Utils.toNumber(document.getElementById('segments').value, 32));
            form.append('csrf', APP_CONFIG.csrf);
            try {
                const res = await fetch(APP_CONFIG.api.convert, { method: 'POST', body: form });
                const data = await res.json();
                if (data.ok) {
                    UI.notify({ type: 'success', message: 'Conversion complete' });
                    const url = data.download_url || `${APP_CONFIG.api.download}?job_id=${data.job_id}`;
                    Preview.loadSTL(url);
                    document.getElementById('download').innerHTML = `<a href="${url}">Download STL</a>`;
                } else {
                    UI.notify({ type: 'error', message: data.error?.message || 'Conversion error' });
                }
            } catch (e) {
                UI.notify({ type: 'error', message: 'Server error' });
            }
        } else if (e.target.id === 'themeToggle') {
            UI.toggleTheme();
        }
    });

    controls.addEventListener('input', e => {
        if (e.target.id === 'rotation') {
            Preview.setRotationSpeed(Utils.toNumber(e.target.value, 0.01));
        } else if (e.target.id === 'color') {
            Preview.setMeshColor(e.target.value);
        }
    });

    controls.addEventListener('change', e => {
        if (e.target.id === 'autorotate') {
            Preview.setAutorotate(e.target.checked);
        }
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    window.addEventListener('DOMContentLoaded', init);
}
