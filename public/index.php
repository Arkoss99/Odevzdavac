<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="cs">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Odevzdávač!</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="shell">
    <aside class="rail">
        <p class="eyebrow">Odevzdání práce</p>
        <h1 class="rail-title">Nahraj svou<br>práci</h1>
        <p class="rail-lead">Odesílač prací/projektů, zadej údaje + soubory a odešli své projekty.</p>

        <div class="preview" id="preview">
            <div class="preview-cover" id="previewCover">
                <span class="preview-cover-empty">Titulní fotka</span>
            </div>
            <div class="preview-body">
                <p class="preview-name" id="previewName">Název práce</p>
                <p class="preview-author" id="previewAuthor">Autor</p>
                <div class="preview-meta">
                    <span id="previewPhotos">0 fotek</span>
                    <span class="dot"></span>
                    <span id="previewFiles">0 souborů</span>
                </div>
            </div>
        </div>
    </aside>

    <section class="panel">
        <form id="form" novalidate>
            <fieldset class="group">
                <legend><span class="ix">A</span> Základní údaje</legend>

                <div class="field">
                    <label for="sender_email">Váš e-mail</label>
                    <input type="email" id="sender_email" name="sender_email" maxlength="254" autocomplete="email" required>
                    <p class="err" data-err="sender_email"></p>
                </div>

                <div class="field">
                    <label for="nazev">Název práce</label>
                    <input type="text" id="nazev" name="nazev" maxlength="200" autocomplete="off" required>
                    <p class="err" data-err="nazev"></p>
                </div>

                <div class="field">
                    <label for="autor">Autor</label>
                    <input type="text" id="autor" name="autor" maxlength="120" autocomplete="name" required>
                    <p class="err" data-err="autor"></p>
                </div>

                <div class="field">
                    <label for="popis">Popis práce</label>
                    <textarea id="popis" name="popis" rows="5" maxlength="5000" required></textarea>
                    <p class="err" data-err="popis"></p>
                </div>
            </fieldset>

            <fieldset class="group">
                <legend><span class="ix">B</span> Vizuály</legend>

                <div class="field">
                    <label>Titulní fotka</label>
                    <div class="drop drop-cover" data-drop="titulni" tabindex="0" role="button" aria-label="Nahrát titulní fotku">
                        <input type="file" name="titulni" accept="image/*" hidden>
                        <div class="drop-inner">
                            <span class="drop-title">Přetáhni sem fotku nebo klikni</span>
                            <span class="drop-hint">JPG, PNG, WEBP nebo GIF</span>
                        </div>
                    </div>
                    <div class="thumbs" data-thumbs="titulni"></div>
                    <p class="err" data-err="titulni"></p>
                </div>

                <div class="field">
                    <label>Galerie fotek <span class="opt">volitelné</span></label>
                    <div class="drop" data-drop="fotky" tabindex="0" role="button" aria-label="Nahrát fotky do galerie">
                        <input type="file" name="fotky[]" accept="image/*" multiple hidden>
                        <div class="drop-inner">
                            <span class="drop-title">Přidat fotky</span>
                            <span class="drop-hint">Můžeš vybrat víc najednou</span>
                        </div>
                    </div>
                    <div class="thumbs" data-thumbs="fotky"></div>
                    <p class="err" data-err="fotky"></p>
                </div>
            </fieldset>

            <fieldset class="group">
                <legend><span class="ix">C</span> Soubory</legend>

                <div class="field">
                    <label>Přílohy <span class="opt">volitelné</span></label>
                    <div class="drop" data-drop="soubory" tabindex="0" role="button" aria-label="Nahrát soubory">
                        <input type="file" name="soubory[]" multiple hidden>
                        <div class="drop-inner">
                            <span class="drop-title">Přidat soubory</span>
                            <span class="drop-hint">Dokumenty, archivy, cokoliv</span>
                        </div>
                    </div>
                    <ul class="filelist" data-files="soubory"></ul>
                    <p class="err" data-err="soubory"></p>
                </div>
            </fieldset>

            <fieldset class="group">
                <legend><span class="ix">D</span> Příjemce</legend>

                <div class="field">
                    <label for="recipient_email">E-mail příjemce</label>
                    <input type="email" id="recipient_email" name="recipient_email" maxlength="254" autocomplete="off" required>
                    <p class="err" data-err="recipient_email"></p>
                </div>
            </fieldset>

            <div class="actions">
                <button type="submit" class="submit" id="submit">
                    <span class="submit-label">Odeslat práci</span>
                    <span class="spinner" aria-hidden="true"></span>
                </button>
                <p class="err err-global" data-err="email"></p>
                <p class="err err-global" data-err="form"></p>
            </div>

            <div class="done" id="done" hidden>
                <div class="done-mark" aria-hidden="true"></div>
                <p class="done-title">Odesláno</p>
                <p class="done-text" id="doneText"></p>
                <button type="button" class="ghost" id="again">Odeslat další práci</button>
            </div>
        </form>
    </section>
</main>
<script src="assets/app.js"></script>
</body>
</html>
