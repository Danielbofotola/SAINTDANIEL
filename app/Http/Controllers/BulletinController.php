<?php

namespace App\Http\Controllers;

use App\Models\AnneeScolaire;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Periode;
use App\Support\BulletinDocument;
use App\Support\ClassementClasse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(Request $request): View
    {
        $periodeId = $request->query('periode_id');
        $classeId = $request->query('classe_id');

        $annee = AnneeScolaire::enCours();
        $periodes = Periode::anneeEnCours()->orderBy('id')->get();
        $classes = Classe::where('statut', 'actif')->orderBy('nom')->get();

        $periode = null;
        $classe = null;
        $rows = collect();

        if ($annee && $periodeId && $classeId) {
            $periode = $periodes->firstWhere('id', (int) $periodeId);
            $classe = $classes->firstWhere('id', (int) $classeId);

            if ($periode && $classe) {
                $rows = collect(ClassementClasse::pour($classe, $periode))->map(function (array $row) use ($periode) {
                    $row['bulletin'] = Bulletin::query()
                        ->where('periode_id', $periode->id)
                        ->where('inscription_id', $row['inscription']->id)
                        ->first();

                    return $row;
                });
            }
        }

        return view('admin.bulletins.index', compact('annee', 'periodes', 'classes', 'periode', 'classe', 'rows', 'periodeId', 'classeId'));
    }

    public function document(Inscription $inscription): View
    {
        return view('admin.bulletins.document', BulletinDocument::donnees($inscription));
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', Rule::exists('periodes', 'id')],
            'classe_id' => ['required', Rule::exists('classes', 'id')],
        ], [
            'periode_id.required' => 'Choisissez une période.',
            'classe_id.required' => 'Choisissez une classe.',
        ]);

        $periode = Periode::anneeEnCours()->find($data['periode_id']);
        $classe = Classe::find($data['classe_id']);

        if (! $periode || ! $classe) {
            return back()->withInput()->with('error', 'Période ou classe introuvable pour l’année en cours.');
        }

        $rows = ClassementClasse::pour($classe, $periode);

        if ($rows === []) {
            return back()->with('error', 'Aucun élève inscrit dans cette classe pour l’année en cours.');
        }

        DB::transaction(function () use ($rows, $periode) {
            foreach ($rows as $row) {
                Bulletin::updateOrCreate(
                    ['inscription_id' => $row['inscription']->id, 'periode_id' => $periode->id],
                    [
                        'total_points' => $row['total'],
                        'max_possible' => $row['max'],
                        'pourcentage' => $row['pourcentage'],
                        'place_classe' => $row['place'],
                        'mention' => $row['mention'],
                        'statut' => 'actif',
                    ]
                );
            }
        });

        return redirect()
            ->route('admin.bulletins.index', ['periode_id' => $periode->id, 'classe_id' => $classe->id])
            ->with('success', 'Les bulletins de la classe ont été générés.');
    }
}
