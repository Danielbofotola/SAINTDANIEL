@php
    /**
     * Appels audio / vidéo (WebRTC) entre les deux participants d'une
     * conversation. Signalisation par interrogation courte des endpoints
     * `appels.*` : aucun serveur temps réel requis.
     *
     * Variable attendue : $nomCorrespondant (nom affiché pendant l'appel).
     */
    $nomCorrespondant = $nomCorrespondant ?? null;
@endphp

@once
<style>
    .appel-banniere { position: fixed; top: 16px; left: 50%; transform: translateX(-50%); z-index: 1200;
        display: flex; align-items: center; gap: 12px; min-width: 300px; max-width: 92vw; padding: 12px 16px;
        background: #242526; border: 1px solid #3a3b3c; border-radius: 14px; box-shadow: 0 12px 34px rgba(0,0,0,.6); color: #e4e6eb; }
    .appel-banniere__nom { font-weight: 700; color: #fff; font-size: .95rem; }
    .appel-banniere__type { color: #b0b3b8; font-size: .75rem; }
    .appel-rond { width: 42px; height: 42px; border-radius: 50%; border: none; cursor: pointer; color: #fff;
        display: inline-flex; align-items: center; justify-content: center; font-size: 1.05rem; flex: none; }
    .appel-rond--ok { background: #22c55e; }
    .appel-rond--non { background: #e41e3f; }
    .appel-rond--off { background: rgba(255,255,255,.16); }

    .appel-overlay { position: fixed; inset: 0; z-index: 1300; background: #0b0b0c; display: none; flex-direction: column; }
    .appel-overlay.on { display: flex; }
    .appel-distant { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; background: #000; }
    .appel-local { position: absolute; right: 16px; bottom: 120px; width: 120px; height: 160px; object-fit: cover;
        border-radius: 12px; border: 2px solid rgba(255,255,255,.25); background: #000; z-index: 2; }
    .appel-infos { position: relative; z-index: 3; margin-top: 24px; text-align: center; color: #fff; }
    .appel-infos__nom { font-size: 1.4rem; font-weight: 800; text-shadow: 0 2px 10px rgba(0,0,0,.8); }
    .appel-infos__statut { color: #d1d5db; font-size: .85rem; margin-top: 4px; }
    .appel-infos__duree { color: #9ca3af; font-size: .85rem; margin-top: 8px; font-variant-numeric: tabular-nums; }
    .appel-actions { position: relative; z-index: 3; margin-top: auto; display: flex; justify-content: center; gap: 18px;
        padding: 26px 16px 34px; }
    .appel-actions .appel-rond { width: 56px; height: 56px; font-size: 1.25rem; }
</style>
@endonce

<div class="appel-banniere" id="appelBanniere" style="display:none">
    <span class="appel-rond appel-rond--off"><i class="fa-solid fa-phone-volume"></i></span>
    <div style="flex:1; min-width:0">
        <div class="appel-banniere__nom" id="appelEntrantNom">Correspondant</div>
        <div class="appel-banniere__type" id="appelEntrantType">Appel entrant</div>
    </div>
    <button type="button" class="appel-rond appel-rond--non" onclick="appelRefuser()" title="Refuser"><i class="fa-solid fa-phone-slash"></i></button>
    <button type="button" class="appel-rond appel-rond--ok" onclick="appelAccepter()" title="Accepter"><i class="fa-solid fa-phone"></i></button>
</div>

<div class="appel-overlay" id="appelOverlay">
    <video class="appel-distant" id="appelDistant" autoplay playsinline></video>
    <video class="appel-local" id="appelLocal" autoplay playsinline muted></video>
    <div class="appel-infos">
        <div class="appel-infos__nom" id="appelNom">{{ $nomCorrespondant ?: 'Correspondant' }}</div>
        <div class="appel-infos__statut" id="appelStatut">Connexion…</div>
        <div class="appel-infos__duree" id="appelDuree">0:00</div>
    </div>
    <div class="appel-actions">
        <button type="button" class="appel-rond appel-rond--off" onclick="appelMuet(this)" title="Couper le micro"><i class="fa-solid fa-microphone"></i></button>
        <button type="button" class="appel-rond appel-rond--off" onclick="appelCamera(this)" title="Couper la caméra"><i class="fa-solid fa-video"></i></button>
        <button type="button" class="appel-rond appel-rond--non" onclick="appelRaccrocher()" title="Raccrocher"><i class="fa-solid fa-phone-slash"></i></button>
    </div>
</div>

@once
<script>
(function () {
    const CSRF_A = document.querySelector('meta[name="csrf-token"]').content;
    const CONV = @json($active?->id);
    const URLS = {
        creer: @json(route('appels.store')),
        entrant: @json(route('appels.entrant')),
        show: @json(route('appels.show', ['appel' => '__ID__'])),
        reponse: @json(route('appels.reponse', ['appel' => '__ID__'])),
        refuser: @json(route('appels.refuser', ['appel' => '__ID__'])),
        terminer: @json(route('appels.terminer', ['appel' => '__ID__'])),
        ice: @json(route('appels.ice', ['appel' => '__ID__'])),
    };
    const SERVERS = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };

    let actif = null;        // appel en cours
    let entrant = null;      // sonnerie reçue
    let boucleAppel = null;
    let boucleEntrant = null;

    const u = (base, id) => base.replace('__ID__', id);

    async function poster(url, donnees) {
        const corps = new FormData();
        corps.append('_token', CSRF_A);
        Object.entries(donnees || {}).forEach(([k, v]) => corps.append(k, v));
        const rep = await fetch(url, { method: 'POST', credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: corps });
        const json = await rep.json().catch(() => ({}));
        if (!rep.ok) throw json;
        return json;
    }

    async function lire(url) {
        const rep = await fetch(url, { credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
        const json = await rep.json().catch(() => ({}));
        if (!rep.ok) throw json;
        return json;
    }

    const $ = id => document.getElementById(id);

    /* ---------- Sonnerie (Web Audio, sans fichier audio) ---------- */
    let sonnerie = null;

    function arreterSonnerie() {
        if (!sonnerie) return;
        clearInterval(sonnerie.minuterie);
        try { sonnerie.ctx.close(); } catch (e) {}
        sonnerie = null;
    }

    function demarrerSonnerie(genre) {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            arreterSonnerie();
            const ctx = new Ctx();
            const bip = () => {
                const o = ctx.createOscillator(), g = ctx.createGain();
                o.type = 'sine';
                o.frequency.value = genre === 'entrant' ? 540 : 430;
                g.gain.value = 0.05;
                o.connect(g); g.connect(ctx.destination);
                o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.35);
            };
            bip();
            sonnerie = { ctx: ctx, minuterie: setInterval(bip, genre === 'entrant' ? 1300 : 2600) };
        } catch (e) { /* audio indisponible */ }
    }

    function ecran(statut) { if ($('appelStatut')) $('appelStatut').textContent = statut; }

    function ouvrirEcran(type) {
        $('appelOverlay').classList.add('on');
        $('appelLocal').style.display = type === 'video' ? 'block' : 'none';
        $('appelDistant').style.display = type === 'video' ? 'block' : 'none';
        ecran('Connexion…');
        if (actif) actif.debut = Date.now();
    }

    function fermerEcran() { $('appelOverlay').classList.remove('on'); }

    function lancerMinuteur() {
        if (!boucleAppel) return;
        clearInterval(window.__appelMinuteur);
        window.__appelMinuteur = setInterval(function () {
            if (!actif) return;
            const s = Math.floor((Date.now() - actif.debut) / 1000);
            $('appelDuree').textContent = Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
        }, 1000);
    }

    function brancherPc(pc, flux, type, idAppelRef) {
        flux.getTracks().forEach(t => pc.addTrack(t, flux));
        pc.ontrack = e => { $('appelDistant').srcObject = e.streams[0]; };
        pc.onconnectionstatechange = () => {
            if (!actif) return;
            if (['connected'].includes(pc.connectionState)) { ecran('En communication'); lancerMinuteur(); }
            if (['failed', 'disconnected'].includes(pc.connectionState)) ecran('Connexion perdue…');
        };
        pc.onicecandidate = e => {
            if (!e.candidate) return;
            const cible = idAppelRef();
            if (cible) poster(u(URLS.ice, cible), { candidat: JSON.stringify(e.candidate) }).catch(() => {});
        };
    }

    /* ---------- Appel sortant ---------- */
    window.appelDemarrer = async function (type) {
        if (!CONV) { alert('Ouvrez une conversation avant d’appeler.'); return; }
        if (!navigator.mediaDevices || !window.RTCPeerConnection) { alert('Les appels ne sont pas pris en charge par ce navigateur.'); return; }
        if (actif) { alert('Un appel est déjà en cours.'); return; }

        let flux;
        try { flux = await navigator.mediaDevices.getUserMedia({ audio: true, video: type === 'video' }); }
        catch (e) { alert('Micro/caméra indisponibles (HTTPS ou localhost requis).'); return; }

        const pc = new RTCPeerConnection(SERVERS);
        let idAppel = null;
        const enAttente = [];
        brancherPc(pc, flux, type, () => idAppel);
        pc.onicecandidate = e => {
            if (!e.candidate) return;
            if (idAppel) poster(u(URLS.ice, idAppel), { candidat: JSON.stringify(e.candidate) }).catch(() => {});
            else enAttente.push(e.candidate);
        };

        const offre = await pc.createOffer();
        await pc.setLocalDescription(offre);

        let r;
        try { r = await poster(URLS.creer, { conversation_id: CONV, type: type, offre: JSON.stringify(pc.localDescription) }); }
        catch (err) { flux.getTracks().forEach(t => t.stop()); alert('Appel impossible.'); return; }

        idAppel = r.id;
        enAttente.splice(0).forEach(c => poster(u(URLS.ice, idAppel), { candidat: JSON.stringify(c) }).catch(() => {}));

        actif = { id: idAppel, role: 'appelant', type: type, pc: pc, flux: flux, iceVus: 0, reponseFaite: false, debut: Date.now() };
        $('appelLocal').srcObject = flux;
        ouvrirEcran(type);
        $('appelStatut').textContent = 'Appel en cours…';
        demarrerSonnerie('sortant');
        suivreAppel();
    };

    /* ---------- Appel entrant ---------- */
    window.appelAccepter = async function () {
        if (!entrant) return;
        const type = entrant.type;

        let flux;
        try { flux = await navigator.mediaDevices.getUserMedia({ audio: true, video: type === 'video' }); }
        catch (e) { alert('Micro/caméra indisponibles (HTTPS ou localhost requis).'); return; }

        const pc = new RTCPeerConnection(SERVERS);
        const idAppel = entrant.id;
        brancherPc(pc, flux, type, () => idAppel);

        try {
            await pc.setRemoteDescription(JSON.parse(entrant.offre));
            const reponse = await pc.createAnswer();
            await pc.setLocalDescription(reponse);
            await poster(u(URLS.reponse, idAppel), { reponse: JSON.stringify(pc.localDescription) });
        } catch (e) { alert('Réponse impossible.'); return; }

        actif = { id: idAppel, role: 'destinataire', type: type, pc: pc, flux: flux, iceVus: 0, reponseFaite: true, debut: Date.now() };
        $('appelLocal').srcObject = flux;
        masquerBanniere();
        entrant = null;
        ouvrirEcran(type);
        ecran('En communication');
        suivreAppel();
    };

    window.appelRefuser = function () {
        arreterSonnerie();
        if (entrant) poster(u(URLS.refuser, entrant.id), {}).catch(() => {});
        entrant = null;
        masquerBanniere();
    };

    window.appelRaccrocher = function (silencieux) {
        arreterSonnerie();
        if (actif) {
            poster(u(URLS.terminer, actif.id), {}).catch(() => {});
            try { actif.pc.close(); } catch (e) {}
            try { actif.flux.getTracks().forEach(t => t.stop()); } catch (e) {}
        }
        actif = null;
        clearInterval(boucleAppel); boucleAppel = null;
        clearInterval(window.__appelMinuteur);
        // Libérer réellement les vidéos (sinon la dernière image reste figée)
        // et remettre les commandes de l'appel à leur état initial.
        try { $('appelLocal').srcObject = null; } catch (e) {}
        try { $('appelDistant').srcObject = null; } catch (e) {}
        try {
            document.querySelector('button[onclick="appelMuet(this)"]').innerHTML = '<i class="fa-solid fa-microphone"></i>';
            document.querySelector('button[onclick="appelCamera(this)"]').innerHTML = '<i class="fa-solid fa-video"></i>';
        } catch (e) {}
        $('appelDuree').textContent = '0:00';
        fermerEcran();
        if (!silencieux) ecran('Appel terminé');
    };

    window.appelMuet = function (bouton) {
        if (!actif || !actif.flux) return;
        const pistes = actif.flux.getAudioTracks();
        if (!pistes.length) return;
        const actifMicro = pistes[0].enabled = !pistes[0].enabled;
        bouton.classList.toggle('appel-rond--off', !pistes[0].enabled);
        bouton.classList.toggle('appel-rond--non', pistes[0].enabled && false);
        bouton.innerHTML = '<i class="fa-solid ' + (pistes[0].enabled ? 'fa-microphone' : 'fa-microphone-slash') + '"></i>';
    };

    window.appelCamera = function (bouton) {
        if (!actif || !actif.flux) return;
        const pistes = actif.flux.getVideoTracks();
        if (!pistes.length) { alert('Cet appel est audio : pas de caméra.'); return; }
        pistes[0].enabled = !pistes[0].enabled;
        bouton.innerHTML = '<i class="fa-solid ' + (pistes[0].enabled ? 'fa-video' : 'fa-video-slash') + '"></i>';
    };

    /* ---------- Suivi de l'appel en cours ---------- */
    function suivreAppel() {
        clearInterval(boucleAppel);
        boucleAppel = setInterval(async function () {
            if (!actif) return;
            let etat;
            try { etat = await lire(u(URLS.show, actif.id)); } catch (e) { return; }

            if (actif.role === 'appelant' && etat.reponse && !actif.reponseFaite) {
                try {
                    await actif.pc.setRemoteDescription(JSON.parse(etat.reponse));
                    actif.reponseFaite = true;
                    ecran('En communication');
                    lancerMinuteur();
                } catch (e) { /* on réessaiera */ }
            }

            const ice = etat.ice || [];
            while (actif && actif.iceVus < ice.length) {
                try { await actif.pc.addIceCandidate(JSON.parse(ice[actif.iceVus])); } catch (e) {}
                actif.iceVus++;
            }

            if (etat.statut === 'refuse' || etat.statut === 'termine') {
                window.appelRaccrocher(true);
            }
        }, 1000);
    }

    /* ---------- Sonnerie entrante (interrogation courte) ---------- */
    function afficherBanniere(appel) {
        $('appelEntrantNom').textContent = appel.appelant || 'Correspondant';
        $('appelEntrantType').textContent = (appel.type === 'video' ? 'Appel vidéo' : 'Appel vocal') + ' entrant…';
        $('appelBanniere').style.display = 'flex';
        demarrerSonnerie('entrant');
    }
    function masquerBanniere() { $('appelBanniere').style.display = 'none'; arreterSonnerie(); }

    boucleEntrant = setInterval(async function () {
        if (actif || document.hidden) return;
        let r;
        try { r = await lire(URLS.entrant); } catch (e) { return; }
        if (r.appel) { entrant = r.appel; afficherBanniere(r.appel); }
        else if (entrant) { entrant = null; masquerBanniere(); }
    }, 2000);
})();
</script>
@endonce
