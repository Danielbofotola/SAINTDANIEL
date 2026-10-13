@extends('prefecture.layout')

@section('titre', "Demandes d'inscription")

@section('contenu')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="row row-card-no-pd">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <h4 class="card-title"><i class="fas fa-user-check"></i> Demandes d'inscription en attente</h4>
                        <div class="card-category">{{ $schoolYear?->libelle }} · {{ $requests->count() }} demande(s)</div>
                    </div>
                    <p class="card-category mb-0">
                        En acceptant, l'élève, son tuteur et son inscription sont créés — l'inscription reste
                        « en attente » jusqu'à l'encaissement du frais d'inscription.
                    </p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Élève</th><th>Naissance</th><th>Responsable</th>
                                    <th>Classe &amp; année</th><th class="text-right">Décision</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $request)
                                    <tr>
                                        <td class="fw-bold">
                                            {{ trim($request->nom.' '.($request->postnom ?? '').' '.$request->prenom) }}
                                            <br><small class="text-muted">{{ $request->sexe === 'F' ? 'Fille' : 'Garçon' }}</small>
                                        </td>
                                        <td>
                                            {{ $request->date_naissance ? \Illuminate\Support\Carbon::parse($request->date_naissance)->format('d/m/Y') : '—' }}
                                            @if ($request->lieu_naissance)<br><small class="text-muted">{{ $request->lieu_naissance }}</small>@endif
                                        </td>
                                        <td>
                                            {{ $request->nom_mere ?: '—' }}
                                            @if ($request->telephone_mere)<br><small class="text-muted">{{ $request->telephone_mere }}</small>@endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('prefecture.demandes.inscriptions.approve', $request) }}" class="form-inline">
                                                @csrf
                                                <select name="classe_id" class="form-control form-control-sm mr-2" required>
                                                    <option value="">Classe…</option>
                                                    @foreach ($classes as $classe)
                                                        <option value="{{ $classe->id }}" @selected($request->classe_id == $classe->id)>{{ $classe->nom }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="annee_scolaire_id" class="form-control form-control-sm" required>
                                                    <option value="">Année…</option>
                                                    @foreach ($schoolYears as $annee)
                                                        <option value="{{ $annee->id }}" @selected(($request->annee_scolaire_id ?? $schoolYear?->id) == $annee->id)>{{ $annee->libelle }}</option>
                                                    @endforeach
                                                </select>
                                        </td>
                                        <td class="text-right text-nowrap">
                                                <button class="btn btn-sm btn-round btn-success" type="submit">
                                                    <i class="fas fa-check"></i> Accepter
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('prefecture.demandes.inscriptions.reject', $request) }}" class="d-inline"
                                                  onsubmit="return confirm('Refuser cette demande d’inscription ?');">
                                                @csrf
                                                <button class="btn btn-sm btn-round btn-outline-danger" type="submit">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">Aucune demande d'inscription en attente.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
