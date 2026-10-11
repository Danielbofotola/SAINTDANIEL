@if (! empty($messages))
    <div id="flash-global" class="flash-global" role="status" aria-live="polite">
        @foreach ($messages as $message)
            <div class="flash-global__item flash-global__item--{{ $message['type'] }}">
                <span class="flash-global__icone">{{ $message['type'] === 'success' ? '✔' : '⚠' }}</span>
                <span class="flash-global__texte">{{ $message['texte'] }}</span>
                <button type="button" class="flash-global__fermer" aria-label="Fermer">&times;</button>
            </div>
        @endforeach
    </div>

    <style>
        .flash-global { position: fixed; top: 16px; right: 16px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; max-width: min(420px, 92vw); }
        .flash-global__item { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 10px; color: #fff;
            box-shadow: 0 6px 22px rgba(15, 23, 42, .22); font: 600 13px/1.35 'Segoe UI', system-ui, sans-serif; animation: flash-apparait .28s ease-out; }
        .flash-global__item--success { background: #107c41; }
        .flash-global__item--error { background: #a80000; }
        .flash-global__icone { font-size: 14px; }
        .flash-global__texte { flex: 1; }
        .flash-global__fermer { background: none; border: 0; color: #fff; font-size: 18px; line-height: 1; cursor: pointer; opacity: .85; }
        .flash-global__fermer:hover { opacity: 1; }
        .flash-global__item--sortie { opacity: 0; transform: translateY(-6px); transition: opacity .3s, transform .3s; }
        @keyframes flash-apparait { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
    </style>

    <script>
        (function () {
            var zone = document.getElementById('flash-global');
            if (!zone) { return; }

            function fermer(item) {
                item.classList.add('flash-global__item--sortie');
                setTimeout(function () { item.remove(); }, 320);
            }

            Array.prototype.forEach.call(zone.querySelectorAll('.flash-global__fermer'), function (bouton) {
                bouton.addEventListener('click', function () { fermer(bouton.parentNode); });
            });

            // Disparition automatique après 6 secondes.
            setTimeout(function () {
                Array.prototype.forEach.call(zone.querySelectorAll('.flash-global__item'), fermer);
            }, 6000);
        })();
    </script>
@endif
