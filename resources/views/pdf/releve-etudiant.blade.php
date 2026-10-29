<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 20px 34px; }
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.header-table td { vertical-align: middle; }
.logo { height: 52px; margin-bottom: 2px; }
.tutelle { text-align: center; vertical-align: top; }
.etoiles { font-size: 7px; color: #888; letter-spacing: 1px; margin: 1px 0; }
.ministere { font-size: 7.6px; font-weight: bold; line-height: 1.25; }
.sigle-ministere { font-size: 7.6px; font-weight: bold; }
.institut { font-size: 8px; font-weight: bold; line-height: 1.25; margin-top: 2px; }
.coordonnees { font-size: 7px; color: #555; margin-top: 2px; line-height: 1.3; }
.agrement { font-size: 6.6px; color: #666; margin-top: 2px; line-height: 1.25; }
.annee { text-align: center; font-size: 9.5px; margin-top: 3px; }
.school-fr { font-size: 13px; font-weight: bold; }
.school-en { font-size: 10px; color: #444; margin-top: 2px; }
.doc-title { font-size: 15px; font-weight: bold; margin-top: 6px; letter-spacing: .5px; text-align: center; }
.republic { font-size: 9px; font-weight: bold; text-align: center; }
.motto { font-size: 8px; color: #777; font-style: italic; text-align: center; margin-top: 2px; }
.sep { border: none; border-top: 2px solid #1a1a1a; margin: 6px 0; }
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.info-table td { padding: 3px 6px; font-size: 10px; }
.info-label { font-weight: bold; width: 20%; line-height: 1.2; }
.info-label .en { display: block; font-weight: normal; font-style: italic; font-size: 7.6px; color: #666; }
.en-th { display: block; font-weight: normal; font-style: italic; font-size: 7px; color: #d8d8d8; }
.info-value { border-bottom: 1px solid #bbb; }
.notes-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.notes-table th { background: #1a1a1a; color: white; padding: 4px 6px; font-size: 8.5px; text-align: center; }
.notes-table th.l { text-align: left; }
.notes-table td { padding: 3px 6px; font-size: 8.5px; border-bottom: 0.5px solid #e5e5e5; text-align: center; }
.notes-table td.l { text-align: left; }
.sem-row td { background: #1a1a1a; color: #fff; font-weight: bold; font-size: 9px; padding: 4px 6px; text-align: left; }
.ue-row td { background: #e8f5e9; font-weight: bold; border-top: 0.5px solid #c8e6c9; border-bottom: 0.5px solid #c8e6c9; }
.ue-row td.l { text-align: left; }
.recap { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #1a1a1a; }
.recap td { padding: 5px 12px; font-size: 10.5px; }
.recap .k { background: #f0f0f0; font-weight: bold; width: 60%; }
.recap .v { text-align: right; font-weight: bold; }
.bareme { margin-top: 5px; font-size: 7.5px; color: #555; }
.bas-de-releve { page-break-inside: avoid; }
.sig { width: 100%; margin-top: 14px; border-collapse: collapse; }
.sig td { text-align: center; font-size: 9px; width: 50%; }
.sig-line { border-top: 1px solid #999; margin: 18px 20px 4px; }
</style>
</head>
<body>

@php
    use App\Support\AnneeAcademique;

    $etab = config('etablissement');
    $logo = public_path($etab['logo'] ?? 'images/logo.png');
    $entete = $etab['entete'] ?? [];

    // Le releve porte l'annee de ses sessions ; a defaut, celle en cours.
    $anneeReleve = $globalStats['annee'] ?: AnneeAcademique::courante();
    $niveau = $globalStats['niveau'] ?? $user->cycle?->name;

@endphp

<table class="header-table">
    <tr>
        <td width="37%" class="tutelle">
            <div class="republic">RÉPUBLIQUE DU CAMEROUN</div>
            <div class="motto">Paix — Travail — Patrie</div>
            <div class="etoiles">**********</div>
            <div class="ministere">{{ config('etablissement.ministere_fr') }}</div>
            <div class="sigle-ministere">({{ config('etablissement.ministere_sigle') }})</div>
            <div class="etoiles">**********</div>
            <div class="institut">{{ $entete['nom_fr'] ?? $etab['nom'] }}</div>
            @if (filled($etab['ecole_fr'] ?? null))
                <div class="institut">{{ $etab['ecole_fr'] }}</div>
            @endif
            @if (filled($entete['agrement_fr'] ?? null))
                <div class="agrement">{{ $entete['agrement_fr'] }}</div>
            @endif
        </td>

        <td width="26%" style="text-align:center; vertical-align:top;">
            @if (is_file($logo))
                <img src="{{ $logo }}" class="logo" alt="">
            @endif
            <div class="school-fr">{{ $etab['sigle'] }}</div>
        </td>

        <td width="37%" class="tutelle">
            <div class="republic">REPUBLIC OF CAMEROON</div>
            <div class="motto">Peace — Work — Fatherland</div>
            <div class="etoiles">**********</div>
            <div class="ministere">{{ config('etablissement.ministere_en') }}</div>
            <div class="etoiles">**********</div>
            <div class="institut">{{ $entete['nom_en'] ?? '' }}</div>
            @if (filled($etab['ecole_en'] ?? null))
                <div class="institut">{{ $etab['ecole_en'] }}</div>
            @endif
            @if (filled($entete['agrement_en'] ?? null))
                <div class="agrement">{{ $entete['agrement_en'] }}</div>
            @endif
        </td>
    </tr>
</table>

<div class="doc-title">RELEVÉ DE NOTES / TRANSCRIPT</div>
<div class="annee">Année académique / Academic year : <strong>{{ $anneeReleve }}</strong></div>

<hr class="sep">

<table class="info-table">
    <tr>
        <td class="info-label">Nom &amp; Prénom<span class="en">Surname and name</span></td>
        <td class="info-value"><strong>{{ strtoupper($user->name ?? '') }} {{ $user->lastname ?? '' }}</strong></td>
        <td class="info-label">Matricule<span class="en">Registration n°</span></td>
        <td class="info-value">{{ $user->matricule ?: '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Né(e) le<span class="en">Born on</span></td>
        <td class="info-value">{{ optional($user->date_naissance)->format('d/m/Y') ?: '—' }}</td>
        <td class="info-label">À<span class="en">At</span></td>
        <td class="info-value">{{ $user->lieu_naissance ?: '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Année<span class="en">Year</span></td>
        <td class="info-value">{{ $anneeReleve }}</td>
        <td class="info-label">Filière<span class="en">Discipline</span></td>
        <td class="info-value">{{ $user->filiere?->name ?: ($filiere ?: '—') }}</td>
    </tr>
    <tr>
        <td class="info-label">Niveau<span class="en">Level</span></td>
        <td class="info-value">{{ $niveau ?: '—' }}</td>
        <td class="info-label">Spécialité<span class="en">Speciality</span></td>
        <td class="info-value">{{ $user->specialite?->name ?: '—' }}</td>
    </tr>
</table>

<table class="notes-table">
    <thead>
        <tr>
            <th class="l" style="width:10%;">Codes</th>
            <th class="l" style="width:35%;">Intitulés</th>
            <th style="width:9%;">Crédits</th>
            <th style="width:10%;">Note<span class="en-th">/20</span></th>
            <th style="width:12%;">Crédits acquis<span class="en-th">credits earned</span></th>
            <th style="width:16%;">Année académique<span class="en-th">et semestre</span></th>
            <th style="width:10%;">Grade</th>
        </tr>
    </thead>
    <tbody>
    @forelse ($sessions as $session)
        <tr class="sem-row"><td colspan="7">{{ $session['titre'] }} — {{ $session['valides'] }}/{{ $session['credits'] }} crédits validés</td></tr>
        @foreach ($session['ues'] as $ue)
            <tr class="ue-row">
                <td class="l">{{ $ue['code'] ?: 'UE' }}</td>
                <td class="l">{{ $ue['name'] }}</td>
                <td>{{ $ue['credits'] }}</td>
                <td>{{ $ue['moy'] !== null ? number_format($ue['moy'], 2) : '—' }}</td>
                <td>{{ $ue['credits_valides'] }}</td>
                <td>{{ $ue['session'] }}</td>
                <td>{{ $ue['cote'] }}</td>
            </tr>
        @endforeach
    @empty
        <tr><td colspan="7" style="text-align:center; color:#888; padding:30px 0;">Aucune note disponible.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="bas-de-releve">

<div class="bareme">NB : 0&lt;=F&lt;6 ; 6&lt;=D&lt;8 ; 8&lt;=D+&lt;9 ; 9&lt;=C-&lt;10 ; 10&lt;=C&lt;11 ; 11&lt;=C+&lt;12 ; 12&lt;=B-&lt;13 ; 13&lt;=B&lt;14 ; 14&lt;=B+&lt;15 ; 15&lt;=A-&lt;16 ; 16&lt;=A&lt;20</div>

@php
    // Les credits d'une annee academique, tels que le releve les annonce.
    $creditsAnnee = (int) config('etablissement.credits_annee', 60);

    $mention = match (true) {
        $globalStats['moy'] === null => '—',
        $globalStats['moy'] >= 16 => 'Très bien',
        $globalStats['moy'] >= 14 => 'Bien',
        $globalStats['moy'] >= 12 => 'Assez bien',
        $globalStats['moy'] >= 10 => 'Passable',
        default => 'Insuffisant',
    };

    $dette = max($creditsAnnee - $globalStats['valides'], 0);
@endphp

<table class="recap">
    <tr>
        <td class="k">Moyenne générale /20</td>
        <td class="v">{{ $globalStats['moy'] !== null ? number_format($globalStats['moy'], 2) . ' / 20' : '—' }}</td>
    </tr>
    <tr>
        <td class="k">Total des crédits sur {{ $creditsAnnee }}</td>
        <td class="v">{{ $globalStats['valides'] }} / {{ $creditsAnnee }}</td>
    </tr>
    <tr>
        <td class="k">Mention</td>
        <td class="v">{{ $mention }}</td>
    </tr>
    <tr>
        <td class="k">Décision</td>
        <td class="v">{{ $dette === 0 ? 'Admis(e)' : 'En dette de '.$dette.' crédit'.($dette > 1 ? 's' : '') }}</td>
    </tr>
</table>

<table class="sig">
    <tr>
        <td>Le Chef de Département<div class="sig-line"></div></td>
        <td>Le Chef d'établissement<div class="sig-line"></div></td>
    </tr>
</table>

</div>

</body>
</html>
