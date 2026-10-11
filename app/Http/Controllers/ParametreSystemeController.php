<?php

namespace App\Http\Controllers;

use App\Models\ParametreSysteme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Réglages de l'établissement : identité, options de l'application (activation des
 * modules) et paramètres du bulletin. Ces valeurs sont enregistrées une fois pour
 * toutes et réutilisées partout dans l'application.
 */
class ParametreSystemeController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.settings', [
            'settings' => ParametreSysteme::query()->first() ?? ParametreSysteme::defaults(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Identité de l'établissement.
            'nom_etablissement' => ['required', 'string', 'max:150'],
            'nom_court' => ['required', 'string', 'max:60'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'adresse' => ['nullable', 'string', 'max:1000'],

            // Options de l'application : modules activés ou non.
            'saisie_notes_active' => ['nullable', 'boolean'],
            'portail_parents_actif' => ['nullable', 'boolean'],
            'deliberation_active' => ['nullable', 'boolean'],
            'module_recus_actif' => ['nullable', 'boolean'],
            'module_recouvrement_actif' => ['nullable', 'boolean'],

            // Devises et taux de change.
            'devise' => ['nullable', 'in:USD,CDF'],
            'taux_change' => ['nullable', 'numeric', 'min:0'],

            // Paramètres du bulletin.
            'bulletin_pays' => ['nullable', 'string', 'max:100'],
            'bulletin_ministere' => ['nullable', 'string', 'max:160'],
            'bulletin_titre' => ['nullable', 'string', 'max:160'],
            'bulletin_reference' => ['nullable', 'string', 'max:50'],
            'bulletin_province' => ['nullable', 'string', 'max:100'],
            'bulletin_ville' => ['nullable', 'string', 'max:100'],
            'bulletin_commune' => ['nullable', 'string', 'max:100'],
            'bulletin_code_ecole' => ['nullable', 'string', 'max:50'],
            'bulletin_lieu_signature' => ['nullable', 'string', 'max:100'],
            'bulletin_signataire' => ['nullable', 'string', 'max:120'],
            'bulletin_seuil_reussite' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bulletin_afficher_place' => ['nullable', 'boolean'],
            'bulletin_afficher_conduite' => ['nullable', 'boolean'],
        ], [
            'nom_etablissement.required' => 'Le nom de l’établissement est obligatoire.',
            'nom_court.required' => 'Le nom court de l’établissement est obligatoire.',
            'email.email' => 'L’adresse e-mail de l’établissement n’est pas valide.',
            'bulletin_seuil_reussite.numeric' => 'Le seuil de réussite doit être un nombre (exemple : 50).',
            'bulletin_seuil_reussite.min' => 'Le seuil de réussite ne peut pas être négatif.',
            'bulletin_seuil_reussite.max' => 'Le seuil de réussite ne peut pas dépasser 100 %.',
        ]);

        // Les cases à cocher sont accompagnées d'un champ caché « 0 » dans le formulaire :
        // les options transmises sont donc toujours explicites. Un réglage absent du
        // formulaire est laissé intact au lieu d'être écrasé.
        foreach (['saisie_notes_active', 'portail_parents_actif', 'deliberation_active',
            'module_recus_actif', 'module_recouvrement_actif',
            'bulletin_afficher_place', 'bulletin_afficher_conduite'] as $case) {
            if ($request->has($case)) {
                $data[$case] = $request->boolean($case);
            }
        }

        // Seuil laissé vide : on conserve la valeur enregistrée (50 % si aucune).
        if (($data['bulletin_seuil_reussite'] ?? null) === null || $data['bulletin_seuil_reussite'] === '') {
            unset($data['bulletin_seuil_reussite']);
        }

        // Colonnes optionnelles : ignorées si la table n'est pas migrée.
        foreach (['devise', 'taux_change', 'module_recus_actif', 'module_recouvrement_actif'] as $colonne) {
            if (! Schema::hasColumn('parametres_systeme', $colonne)) {
                unset($data[$colonne]);
            }
        }

        $settings = ParametreSysteme::query()->first() ?? ParametreSysteme::create(ParametreSysteme::defaults()->getAttributes());
        $settings->update($data);

        return redirect()->route('admin.profile.settings')
            ->with('success', 'Les paramètres de l’établissement, des modules et du bulletin ont été enregistrés.');
    }
}
