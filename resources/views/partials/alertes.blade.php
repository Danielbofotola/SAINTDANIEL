@if (! empty($alertes))
    @foreach ($alertes as $alerte)
        <div class="alert alert-{{ $alerte['niveau'] === 'danger' ? 'danger' : 'warning' }}">
            <i class="fa fa-exclamation-triangle"></i> <strong>Signal charges :</strong> {{ $alerte['message'] }}
        </div>
    @endforeach
@endif
