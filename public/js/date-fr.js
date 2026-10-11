/*
 * Saisie manuelle des dates au format jj/mm/aaaa.
 *
 * Un champ [data-date-fr] est un champ texte que l'on tape au format français
 * (masque automatique jj/mm/aaaa). Le nom du champ est déplacé sur un champ
 * caché qui porte toujours la valeur ISO (aaaa-mm-jj) attendue par la validation
 * « date » du serveur — y compris pour les formulaires à soumission automatique.
 */
(function () {
    function masquer(valeur) {
        var chiffres = (valeur || '').replace(/\D/g, '').slice(0, 8);
        var sortie = chiffres.slice(0, 2);
        if (chiffres.length > 2) { sortie += '/' + chiffres.slice(2, 4); }
        if (chiffres.length > 4) { sortie += '/' + chiffres.slice(4, 8); }
        return sortie;
    }

    function isoVersFr(valeur) {
        var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(valeur || '');
        return m ? m[3] + '/' + m[2] + '/' + m[1] : valeur;
    }

    function frVersIso(valeur) {
        var m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec((valeur || '').trim());
        if (!m) { return null; }
        var jour = ('0' + m[1]).slice(-2);
        var mois = ('0' + m[2]).slice(-2);
        return m[3] + '-' + mois + '-' + jour;
    }

    document.querySelectorAll('input[data-date-fr]').forEach(function (champ) {
        champ.setAttribute('inputmode', 'numeric');
        champ.setAttribute('autocomplete', 'off');
        champ.setAttribute('maxlength', '10');
        if (!champ.getAttribute('placeholder')) { champ.setAttribute('placeholder', 'jj/mm/aaaa'); }

        // Valeur initiale du serveur (ISO) → format français.
        if (/^\d{4}-\d{2}-\d{2}/.test(champ.value)) { champ.value = isoVersFr(champ.value); }

        // Le champ caché porte le nom et la valeur ISO réellement transmise.
        var cache = document.createElement('input');
        cache.type = 'hidden';
        cache.name = champ.getAttribute('name') || '';
        champ.removeAttribute('name');
        champ.parentNode.insertBefore(cache, champ.nextSibling);

        function synchroniser() {
            if (champ.value.trim() === '') { cache.value = ''; return; }
            var iso = frVersIso(champ.value);
            // Valeur non reconnue : on transmet telle quelle, la validation serveur
            // renverra un message explicite.
            cache.value = iso || champ.value;
        }

        champ.addEventListener('input', function () {
            champ.value = masquer(champ.value);
            synchroniser();
        });
        champ.addEventListener('change', synchroniser);
        synchroniser();
    });
})();
