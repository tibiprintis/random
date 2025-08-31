window.addEventListener('DOMContentLoaded', () => {
    UI.init();
    Preview.init(document.getElementById('preview'));
    document.getElementById('convert').addEventListener('click', async () => {
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
                Preview.setRotationSpeed(Utils.toNumber(document.getElementById('rotation').value, 0.01));
                Preview.loadSTL(url);
                document.getElementById('download').innerHTML = `<a href="${url}">Download STL</a>`;
            } else {
                UI.notify({ type: 'error', message: data.error?.message || 'Conversion error' });
            }
        } catch (e) {
            UI.notify({ type: 'error', message: 'Server error' });
        }
    });
});
