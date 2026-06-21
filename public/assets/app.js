(function () {
    const form = document.getElementById('form');
    const submitBtn = document.getElementById('submit');
    const done = document.getElementById('done');
    const doneText = document.getElementById('doneText');
    const again = document.getElementById('again');

    const state = { titulni: [], fotky: [], soubory: [] };
    const single = { titulni: true, fotky: false, soubory: false };

    const previewCover = document.getElementById('previewCover');
    const previewName = document.getElementById('previewName');
    const previewAuthor = document.getElementById('previewAuthor');
    const previewPhotos = document.getElementById('previewPhotos');
    const previewFiles = document.getElementById('previewFiles');

    let coverUrl = null;

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return Math.round(bytes / 1024) + ' kB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function plural(n, one, few, many) {
        if (n === 1) return n + ' ' + one;
        if (n >= 2 && n <= 4) return n + ' ' + few;
        return n + ' ' + many;
    }

    function clearError(key) {
        const el = document.querySelector('[data-err="' + key + '"]');
        if (el) el.textContent = '';
        const field = el ? el.closest('.field') : null;
        if (field) field.classList.remove('invalid');
    }

    function setError(key, msg) {
        const el = document.querySelector('[data-err="' + key + '"]');
        if (el) el.textContent = msg;
        const field = el ? el.closest('.field') : null;
        if (field) field.classList.add('invalid');
    }

    function renderImages(key) {
        const wrap = document.querySelector('[data-thumbs="' + key + '"]');
        wrap.innerHTML = '';
        state[key].forEach(function (file, i) {
            const url = URL.createObjectURL(file);
            const div = document.createElement('div');
            div.className = 'thumb';
            div.style.backgroundImage = 'url(' + url + ')';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.setAttribute('aria-label', 'Odebrat');
            btn.textContent = '\u00d7';
            btn.addEventListener('click', function () {
                state[key].splice(i, 1);
                renderImages(key);
                updatePreview();
                clearError(key);
            });
            div.appendChild(btn);
            wrap.appendChild(div);
        });
    }

    function renderFiles(key) {
        const list = document.querySelector('[data-files="' + key + '"]');
        list.innerHTML = '';
        state[key].forEach(function (file, i) {
            const li = document.createElement('li');
            const name = document.createElement('span');
            name.className = 'fname';
            name.textContent = file.name;
            const size = document.createElement('span');
            size.className = 'fsize';
            size.textContent = formatSize(file.size);
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'fdel';
            del.setAttribute('aria-label', 'Odebrat');
            del.textContent = '\u00d7';
            del.addEventListener('click', function () {
                state[key].splice(i, 1);
                renderFiles(key);
                updatePreview();
            });
            li.appendChild(name);
            li.appendChild(size);
            li.appendChild(del);
            list.appendChild(li);
        });
    }

    function updatePreview() {
        const nazev = form.nazev.value.trim();
        const autor = form.autor.value.trim();
        previewName.textContent = nazev || 'Název práce';
        previewAuthor.textContent = autor || 'Autor';
        previewPhotos.textContent = plural(state.fotky.length, 'fotka', 'fotky', 'fotek');
        previewFiles.textContent = plural(state.soubory.length, 'soubor', 'soubory', 'souborů');

        if (state.titulni.length > 0) {
            if (coverUrl) URL.revokeObjectURL(coverUrl);
            coverUrl = URL.createObjectURL(state.titulni[0]);
            previewCover.style.backgroundImage = 'url(' + coverUrl + ')';
            previewCover.classList.add('has-image');
        } else {
            previewCover.style.backgroundImage = '';
            previewCover.classList.remove('has-image');
        }
    }

    function addFiles(key, fileList) {
        const files = Array.prototype.slice.call(fileList);
        if (single[key]) {
            state[key] = files.slice(0, 1);
        } else {
            state[key] = state[key].concat(files);
        }
        clearError(key);
        if (key === 'soubory') renderFiles(key); else renderImages(key);
        updatePreview();
    }

    document.querySelectorAll('.drop').forEach(function (drop) {
        const key = drop.getAttribute('data-drop');
        const input = drop.querySelector('input[type="file"]');

        drop.addEventListener('click', function () { input.click(); });
        drop.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
        });
        input.addEventListener('change', function () {
            if (input.files.length) addFiles(key, input.files);
            input.value = '';
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                e.preventDefault();
                drop.classList.add('dragging');
            });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            drop.addEventListener(ev, function (e) {
                e.preventDefault();
                drop.classList.remove('dragging');
            });
        });
        drop.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length) addFiles(key, e.dataTransfer.files);
        });
    });

    form.sender_email.addEventListener('input', function () { clearError('sender_email'); });
    form.nazev.addEventListener('input', function () { clearError('nazev'); updatePreview(); });
    form.autor.addEventListener('input', function () { clearError('autor'); updatePreview(); });
    form.popis.addEventListener('input', function () { clearError('popis'); });
    form.recipient_email.addEventListener('input', function () { clearError('recipient_email'); });

    var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validate() {
        let ok = true;
        if (!emailRe.test(form.sender_email.value.trim())) { setError('sender_email', 'Zadej platný e-mail.'); ok = false; }
        if (form.nazev.value.trim() === '') { setError('nazev', 'Zadej název práce.'); ok = false; }
        if (form.autor.value.trim() === '') { setError('autor', 'Zadej jméno autora.'); ok = false; }
        if (form.popis.value.trim() === '') { setError('popis', 'Zadej popis práce.'); ok = false; }
        if (state.titulni.length === 0) { setError('titulni', 'Nahraj titulní fotku.'); ok = false; }
        if (!emailRe.test(form.recipient_email.value.trim())) { setError('recipient_email', 'Zadej platný e-mail příjemce.'); ok = false; }
        return ok;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        ['sender_email', 'recipient_email', 'email', 'form'].forEach(clearError);
        if (!validate()) return;

        const data = new FormData();
        data.append('sender_email', form.sender_email.value.trim());
        data.append('nazev', form.nazev.value.trim());
        data.append('autor', form.autor.value.trim());
        data.append('popis', form.popis.value.trim());
        data.append('recipient_email', form.recipient_email.value.trim());
        if (state.titulni[0]) data.append('titulni', state.titulni[0]);
        state.fotky.forEach(function (f) { data.append('fotky[]', f); });
        state.soubory.forEach(function (f) { data.append('soubory[]', f); });

        submitBtn.disabled = true;
        submitBtn.classList.add('loading');

        try {
            const res = await fetch('submit.php', { method: 'POST', body: data });
            const json = await res.json();
            if (json.ok) {
                doneText.textContent = json.message || '';
                form.querySelectorAll('.group, .actions').forEach(function (n) { n.style.display = 'none'; });
                done.hidden = false;
            } else {
                const errs = json.errors || {};
                Object.keys(errs).forEach(function (k) { setError(k, errs[k]); });
                const first = document.querySelector('.field.invalid, .err-global:not(:empty)');
                if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (err) {
            setError('form', 'Spojení se serverem selhalo. Zkus to znovu.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        }
    });

    again.addEventListener('click', function () {
        form.reset();
        state.titulni = []; state.fotky = []; state.soubory = [];
        ['titulni', 'fotky'].forEach(renderImages);
        renderFiles('soubory');
        ['sender_email', 'nazev', 'autor', 'popis', 'titulni', 'fotky', 'soubory', 'recipient_email'].forEach(clearError);
        updatePreview();
        done.hidden = true;
        form.querySelectorAll('.group, .actions').forEach(function (n) { n.style.display = ''; });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    updatePreview();
})();
