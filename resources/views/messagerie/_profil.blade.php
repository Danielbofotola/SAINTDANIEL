@once
<style>
    .profil-fond { position: fixed; inset: 0; background: rgba(0,0,0,.6); z-index: 1250; display: none;
        align-items: center; justify-content: center; padding: 16px; }
    .profil-fond.on { display: flex; }
    .profil-carte { background: #242526; border: 1px solid #3a3b3c; border-radius: 16px; width: 100%; max-width: 380px;
        padding: 22px 18px; text-align: center; color: #e4e6eb; position: relative; }
    .profil-carte__fermer { position: absolute; top: 10px; right: 12px; background: transparent; border: none;
        color: #b0b3b8; cursor: pointer; font-size: 1.05rem; }
    .profil-carte__avatar { width: 96px; height: 96px; border-radius: 50%; background: #0084ff; color: #fff;
        display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800;
        overflow: hidden; margin-bottom: 10px; }
    .profil-carte__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profil-carte__nom { font-size: 1.15rem; font-weight: 800; color: #fff; }
    .profil-carte__role { color: #b0b3b8; font-size: .8rem; margin-top: 2px; }
    .profil-carte__ligne { display: flex; align-items: center; gap: 10px; text-align: left; padding: 8px 0;
        border-bottom: 1px solid #2f3031; font-size: .88rem; }
    .profil-carte__ligne i { width: 20px; text-align: center; color: #b0b3b8; }
    .profil-carte__actions { display: flex; gap: 8px; margin-top: 16px; }
    .profil-carte__actions button { flex: 1; border: none; border-radius: 10px; padding: 10px; font-weight: 700;
        font-size: .85rem; cursor: pointer; color: #fff; background: #3a3b3c; }
    .profil-carte__actions button.primaire { background: #0084ff; }
    .profil-carte__actions button:hover { filter: brightness(1.15); }
</style>
@endonce

<div class="profil-fond" id="profilFond" onclick="if (event.target === this) fermerProfil()">
    <div class="profil-carte">
        <button type="button" class="profil-carte__fermer" onclick="fermerProfil()" title="Fermer"><i class="fa-solid fa-xmark"></i></button>
        <div class="profil-carte__avatar" id="profilAvatar"></div>
        <div class="profil-carte__nom" id="profilNom">…</div>
        <div class="profil-carte__role" id="profilRole"></div>
        <div style="margin-top:14px">
            <div class="profil-carte__ligne" id="profilTelLigne" style="display:none"><i class="fa-solid fa-phone"></i><span id="profilTel"></span></div>
            <div class="profil-carte__ligne" id="profilMailLigne" style="display:none"><i class="fa-solid fa-envelope"></i><span id="profilMail"></span></div>
        </div>
        <div class="profil-carte__actions">
            <button type="button" class="primaire" id="profilEcrire"><i class="fa-solid fa-comment-dots"></i> Écrire</button>
            <button type="button" id="profilAudio"><i class="fa-solid fa-phone"></i> Appeler</button>
            <button type="button" id="profilVideo"><i class="fa-solid fa-video"></i> Vidéo</button>
        </div>
    </div>
</div>

@once
<script>
(function () {
    const URL = @json(route('utilisateurs.apercu', ['user' => '__ID__']));
    const CORRESPONDANT = @json($correspondantId ?? null);
    const $ = id => document.getElementById(id);

    window.ouvrirProfil = async function (id) {
        if (!id) return;
        try {
            const rep = await fetch(URL.replace('__ID__', id), {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!rep.ok) throw new Error('profil');
            const p = await rep.json();

            $('profilAvatar').innerHTML = p.photo ? '<img src="' + p.photo + '" alt="">' : (p.initiales || '?');
            $('profilNom').textContent = p.nom;
            $('profilRole').textContent = p.role;

            $('profilTelLigne').style.display = p.telephone ? 'flex' : 'none';
            $('profilTel').textContent = p.telephone || '';
            $('profilMailLigne').style.display = p.email ? 'flex' : 'none';
            $('profilMail').textContent = p.email || '';

            // « Écrire » ouvre (ou retrouve) la discussion avec cette personne.
            $('profilEcrire').onclick = function () {
                window.fermerProfil();
                if (typeof window.ouvrirDiscussion === 'function') window.ouvrirDiscussion(p.id);
            };

            // Appeler / vidéo : seulement si c'est le correspondant du fil ouvert
            // (l'appel se fait dans une conversation).
            const memeFil = CORRESPONDANT && String(CORRESPONDANT) === String(p.id);
            $('profilAudio').style.display = memeFil ? '' : 'none';
            $('profilVideo').style.display = memeFil ? '' : 'none';
            $('profilAudio').onclick = function () { window.fermerProfil(); if (typeof window.appelDemarrer === 'function') window.appelDemarrer('audio'); };
            $('profilVideo').onclick = function () { window.fermerProfil(); if (typeof window.appelDemarrer === 'function') window.appelDemarrer('video'); };

            $('profilFond').classList.add('on');
        } catch (e) {
            alert('Profil indisponible.');
        }
    };

    window.fermerProfil = function () { $('profilFond').classList.remove('on'); };

    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') window.fermerProfil(); });
})();
</script>
@endonce
