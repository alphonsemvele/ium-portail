<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Syllabus — {{ $syllabus->intitule() }}</title>
{{--
    Calque sur le document officiel de l'institut : en-tete bilingue
    (Republique a gauche en francais, a droite en anglais), identification de
    la matiere, puis les rubriques en deux colonnes — intitule a gauche,
    contenu a droite. A4 portrait.
--}}
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; color: #111; line-height: 1.45; }
@page { margin: 14mm 13mm 13mm; }

.entete { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.entete td { vertical-align: top; text-align: center; font-size: 8.4px; line-height: 1.4; }
.entete .pays { font-weight: bold; font-size: 9px; }
.entete .etoiles { color: #555; letter-spacing: 1px; }
.entete .institut { font-weight: bold; }
.entete .agrement, .entete .devise { font-size: 7.8px; color: #333; }
.entete .anglais { font-style: italic; }
.logo { height: 56px; }

.filet { border-bottom: 1.4px solid #111; margin: 8px 0 10px; }

.titre { text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 2px; }
.annee { text-align: center; font-size: 10px; margin-top: 2px; margin-bottom: 10px; }

.situation { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 9.5px; }
.situation td { padding: 2px 0; }
.situation .cle { font-weight: bold; width: 22%; }

table.fiche { width: 100%; border-collapse: collapse; }
table.fiche td { border: .8px solid #111; padding: 5px 7px; vertical-align: top; }
table.fiche td.cle { width: 27%; font-weight: bold; background: #f3f4f6; }
table.fiche td.valeur { white-space: pre-line; }


.pied { margin-top: 12px; font-size: 8px; color: #444; }
.pied .etat { font-weight: bold; color: #111; }
</style>
</head>
<body>

@php
    $entete = config('etablissement.entete');
    $matiere = $syllabus->matiere;
@endphp

<table class="entete">
    <tr>
        <td width="40%">
            <div class="pays">{{ $entete['pays_fr'] }}</div>
            <div class="etoiles">**********</div>
            <div>{{ $entete['devise_pays_fr'] }}</div>
            <div class="etoiles">**********</div>
            <div class="institut">{{ $entete['nom_fr'] }}</div>
            <div class="agrement">{{ $entete['agrement_fr'] }}</div>
            <div class="devise">{{ $entete['devise_fr'] }}</div>
        </td>
        <td width="20%">
            @if ($logo)<img src="{{ $logo }}" class="logo" alt="">@endif
        </td>
        <td width="40%" class="anglais">
            <div class="pays">{{ $entete['pays_en'] }}</div>
            <div class="etoiles">**********</div>
            <div>{{ $entete['devise_pays_en'] }}</div>
            <div class="etoiles">**********</div>
            <div class="institut">{{ $entete['nom_en'] }}</div>
            <div class="agrement">{{ $entete['agrement_en'] }}</div>
            <div class="devise">{{ $entete['devise_en'] }}</div>
        </td>
    </tr>
</table>

<div class="filet"></div>

<div class="titre">SYLLABUS</div>
<div class="annee">Année académique {{ $syllabus->annee_academique }} — Semestre {{ $syllabus->semestre }}</div>

<table class="situation">
    <tr>
        <td class="cle">Filière :</td>
        <td>{{ \Illuminate\Support\Str::upper($syllabus->filiere?->name ?? '—') }}</td>
    </tr>
    <tr>
        <td class="cle">Spécialité :</td>
        <td>{{ \Illuminate\Support\Str::upper($syllabus->specialite?->name ?? '—') }}</td>
    </tr>
    <tr>
        <td class="cle">Niveau :</td>
        <td>{{ $syllabus->niveau ?: '—' }}</td>
    </tr>
    <tr>
        <td class="cle">Code et intitulé du cours / EC :</td>
        <td>{{ \Illuminate\Support\Str::upper($syllabus->intitule()) }}</td>
    </tr>
</table>

<table class="fiche">
    <tr>
        <td class="cle">Nombre de crédits</td>
        <td class="valeur">{{ $syllabus->credits ?: '—' }}</td>
    </tr>
    <tr>
        <td class="cle">Volume horaire</td>
        @php
            $volumes = collect([
                'Total' => $syllabus->volume_total,
                'CM' => $syllabus->volume_cm,
                'TD' => $syllabus->volume_td,
                'TP' => $syllabus->volume_tp,
                'TPE' => $syllabus->volume_tpe,
            ])->filter()->map(fn ($heures, $intitule) => $intitule.' : '.$heures.' H')->implode('   ·   ');
        @endphp
        <td class="valeur">{{ $volumes ?: '—' }}</td>
    </tr>
    <tr>
        <td class="cle">Langue d'enseignement</td>
        <td class="valeur">{{ $syllabus->langue ?: '—' }}</td>
    </tr>
    <tr>
        <td class="cle">Équipe pédagogique</td>
        @php
            $equipe = collect($syllabus->equipePedagogique())
                ->map(fn (array $membre, int $rang) => 'Enseignant '.($rang + 1)
                    .($membre['charge'] ? ' ('.$membre['charge'].')' : '').' : '.$membre['nom'])
                ->implode("\n");
        @endphp
        <td class="valeur">{{ $equipe ?: '—' }}</td>
    </tr>

    @foreach (\App\Models\Syllabus::SECTIONS as $champ => $intitule)
        <tr>
            <td class="cle">{{ $intitule }}</td>
            <td class="valeur">{{ filled($syllabus->{$champ}) ? $syllabus->{$champ} : '—' }}</td>
        </tr>
    @endforeach
</table>

<div class="pied">
    @if ($syllabus->statut === 'valide')
        <span class="etat">Validé</span>
        @if ($syllabus->tranche_le) le {{ $syllabus->tranche_le->format('d/m/Y') }} @endif
        @if ($syllabus->trancheur) par {{ trim($syllabus->trancheur->name.' '.$syllabus->trancheur->lastname) }} @endif
        · Département {{ $syllabus->departement?->nom ?? '—' }}
    @elseif ($syllabus->estSoumis())
        <span class="etat">{{ $syllabus->libelleStatut() }}</span>
        @if ($syllabus->soumis_le) — soumis le {{ $syllabus->soumis_le->format('d/m/Y') }} @endif
    @else
        <span class="etat">Brouillon</span> — ce syllabus n'a pas encore été soumis.
    @endif
    <br>Déposé par {{ trim(($syllabus->enseignant?->name ?? '').' '.($syllabus->enseignant?->lastname ?? '')) ?: '—' }}
    @if ($syllabus->fichier_nom) · fichier joint : {{ $syllabus->fichier_nom }} @endif
</div>

</body>
</html>
