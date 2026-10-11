<ul class="raccourcis-nav {{ ($variante ?? 'sombre') === 'clair' ? 'raccourcis-nav--clair' : '' }}">
    <li>
        <a class="raccourcis-nav__item" href="{{ $raccourcis['direct']['url'] }}">
            <i class="fa {{ $raccourcis['direct']['icone'] }}"></i>
            <span class="d-none d-lg-inline ml-1">{{ $raccourcis['direct']['libelle'] }}</span>
        </a>
    </li>
    <li class="dropdown">
        <a class="raccourcis-nav__item" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" aria-label="Rapports">
            <i class="fa fa-folder-open-o"></i>
            <span class="d-none d-lg-inline ml-1">Rapports</span>
            <i class="fa fa-angle-down ml-1"></i>
        </a>
        <ul class="dropdown-menu settings-menu border-0 shadow-lg">
            @foreach ($raccourcis['rapports'] as $rapport)
                <li>
                    <a class="dropdown-item" href="{{ $rapport[2] }}">
                        <i class="fa {{ $rapport[1] }} mr-2"></i> {{ $rapport[0] }}
                    </a>
                </li>
            @endforeach
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="{{ route('messagerie.index') }}"><i class="fa fa-comments mr-2"></i> Messagerie</a></li>
            <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="fa fa-bell-o mr-2"></i> Mes notifications</a></li>
        </ul>
    </li>
</ul>

<style>
    .raccourcis-nav { float: left; display: flex; align-items: center; height: 100%; margin: 0 0 0 8px; padding: 0; list-style: none; }
    .raccourcis-nav__item { display: inline-flex; align-items: center; height: 50px; padding: 0 14px; color: rgba(255,255,255,.82);
        font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
    .raccourcis-nav__item:hover, .raccourcis-nav__item:focus { color: #fff; background: rgba(255,255,255,.08); text-decoration: none; }
    /* Variante claire : pastilles lisibles sur un en-tête clair comme sombre. */
    .raccourcis-nav--clair { float: none; display: inline-flex; height: auto; margin: 0; align-items: center; }
    .raccourcis-nav--clair .raccourcis-nav__item { height: 34px; padding: 0 12px; margin-right: 6px; border-radius: 999px;
        background: #f1f5f9; color: #1e293b; }
    .raccourcis-nav--clair .raccourcis-nav__item:hover,
    .raccourcis-nav--clair .raccourcis-nav__item:focus { background: #e2e8f0; color: #0f172a; }
    @media (max-width: 575px) { .raccourcis-nav { margin-left: 0 } .raccourcis-nav__item { padding: 0 8px } }
</style>
