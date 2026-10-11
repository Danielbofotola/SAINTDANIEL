@extends('site::layout')

@section('titre', 'Connexion par code PIN')
@section('description', 'Connexion rapide au système de ' . $systemSettings->nom_etablissement . ' avec un code PIN à 4 chiffres.')

@section('contenu')
    <section class="py-5 bg-light">
        <div class="container px-4 px-lg-5 py-4">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <div class="card border-0 rounded-4 form-panel">
                        <div class="card-body p-4 p-lg-5 text-center">
                            <div class="small text-uppercase text-muted fw-bolder">{{ $systemSettings->nom_court }}</div>
                            <h1 class="h4 fw-bolder mt-2 mb-1">Connexion par code PIN</h1>
                            <p class="text-muted small mb-4">Saisissez votre code à 4 chiffres — la connexion se lance automatiquement.</p>

                            <form method="POST" action="{{ route('login.pin.store') }}">
                                @csrf
                                <input id="pin_code" type="hidden" name="pin_code" value="">

                                <div class="pin-stars" id="pin-display" aria-label="Code PIN saisi">
                                    <span id="star-1">&bull;</span>
                                    <span id="star-2">&bull;</span>
                                    <span id="star-3">&bull;</span>
                                    <span id="star-4">&bull;</span>
                                </div>

                                @error('pin_code')
                                    <div class="alert alert-danger rounded-4 py-2 small" role="alert">{{ $message }}</div>
                                @enderror
                                <div id="pin-error" class="alert alert-danger rounded-4 py-2 small" role="alert" hidden></div>

                                <div class="keypad" aria-label="Pavé numérique">
                                    <button type="button" class="key-btn" data-key="1">1</button>
                                    <button type="button" class="key-btn" data-key="2">2</button>
                                    <button type="button" class="key-btn" data-key="3">3</button>

                                    <button type="button" class="key-btn" data-key="4">4</button>
                                    <button type="button" class="key-btn" data-key="5">5</button>
                                    <button type="button" class="key-btn" data-key="6">6</button>

                                    <button type="button" class="key-btn" data-key="7">7</button>
                                    <button type="button" class="key-btn" data-key="8">8</button>
                                    <button type="button" class="key-btn" data-key="9">9</button>

                                    <button type="button" class="key-btn" data-action="clear" title="Effacer tout">C</button>
                                    <button type="button" class="key-btn" data-key="0">0</button>
                                    <button type="button" class="key-btn" data-action="delete" title="Effacer le dernier chiffre">&larr;</button>
                                </div>

                                <div class="form-check d-inline-block mb-4">
                                    <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember-pin">
                                    <label class="form-check-label small" for="remember-pin">Se souvenir de moi</label>
                                </div>
                            </form>

                            <div class="d-grid gap-2">
                                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill fw-bolder">
                                    <i class="bi bi-key me-1"></i> Connexion par mot de passe
                                </a>
                                <a href="{{ route('site.accueil') }}" class="btn btn-outline-secondary rounded-pill fw-bolder">
                                    <i class="bi bi-arrow-left me-1"></i> Retour à l’accueil du site
                                </a>
                            </div>

                            <p class="small text-muted mt-4 mb-0">
                                Code oublié&nbsp;? L’administration peut le réinitialiser depuis la fiche de votre compte.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .pin-stars { display: flex; justify-content: center; align-items: center; gap: 14px; margin-bottom: 22px; min-height: 46px; }
            .pin-stars span { color: #cbd5e1; font-size: 2.4rem; line-height: 1; transition: color .15s ease, transform .15s ease; }
            .keypad { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; max-width: 260px; margin: 0 auto 22px; }
            .key-btn { height: 64px; width: 64px; margin: 0 auto; padding: 0; display: flex; align-items: center; justify-content: center;
                background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 50%; color: #1e293b; font-size: 1.5rem;
                font-weight: 500; line-height: 1; cursor: pointer; transition: background .15s ease, border-color .15s ease, transform .1s ease; }
            .key-btn:hover, .key-btn:focus { background: #eef2ff; border-color: #1e30f3; color: #1e30f3; outline: none; }
            .key-btn:active { transform: scale(.94); }
            .key-btn[data-action] { color: #64748b; font-size: 1.05rem; }
        </style>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pinInput = document.getElementById('pin_code');
            const stars = [
                document.getElementById('star-1'),
                document.getElementById('star-2'),
                document.getElementById('star-3'),
                document.getElementById('star-4'),
            ];
            const form = pinInput.closest('form');
            const errorMessage = document.getElementById('pin-error');

            function refreshDisplay() {
                const len = pinInput.value.length;
                stars.forEach((star, index) => {
                    star.style.opacity = index < len ? '1' : '0.35';
                    star.style.color = index < len ? '#1e30f3' : '#cbd5e1';
                });
            }

            function addDigit(digit) {
                if (pinInput.value.length >= 4) return;
                pinInput.value += digit;
                refreshDisplay();
                if (pinInput.value.length === 4) {
                    setTimeout(submitPin, 180);
                }
            }

            async function submitPin() {
                const formData = new FormData(form);
                errorMessage.hidden = true;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await response.json();

                    if (response.ok && data.redirect) {
                        window.location.assign(data.redirect);
                        return;
                    }

                    pinInput.value = '';
                    refreshDisplay();
                    errorMessage.textContent = data.message || 'Code PIN invalide.';
                    errorMessage.hidden = false;
                } catch (error) {
                    pinInput.value = '';
                    refreshDisplay();
                    errorMessage.textContent = 'Une erreur est survenue. Veuillez réessayer.';
                    errorMessage.hidden = false;
                }
            }

            document.querySelectorAll('[data-key]').forEach(button => {
                button.addEventListener('click', () => addDigit(button.dataset.key));
            });

            document.querySelector('[data-action="clear"]').addEventListener('click', () => {
                pinInput.value = '';
                refreshDisplay();
            });

            document.querySelector('[data-action="delete"]').addEventListener('click', () => {
                pinInput.value = pinInput.value.slice(0, -1);
                refreshDisplay();
            });

            document.addEventListener('keydown', event => {
                if (/^[0-9]$/.test(event.key)) {
                    addDigit(event.key);
                } else if (event.key === 'Backspace') {
                    pinInput.value = pinInput.value.slice(0, -1);
                    refreshDisplay();
                } else if (event.key === 'Escape') {
                    pinInput.value = '';
                    refreshDisplay();
                }
            });

            refreshDisplay();
        });
    </script>
@endsection
