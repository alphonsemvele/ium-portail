@php
    // Le tableau respire tant que le releve est court et se resserre quand il
    // s'allonge : la largeur ne bouge pas, seule la hauteur des lignes suit.
    // Le cout estime compte les lignes de texte des intitules, la marge de
    // chaque ligne et les bandeaux de semestre.
    $lignesTexte = 0;
    $nbUe = 0;

    foreach ($sessions as $session) {
        foreach ($session['ues'] as $ue) {
            $nbUe++;
            $lignesTexte += max(1, (int) ceil(mb_strlen((string) $ue['name']) / 54));
        }
    }

    $cout = $lignesTexte + 0.6 * $nbUe + 1.5 * count($sessions);

    // 34 est le releve type : au-dela on resserre, en deca on agrandit.
    $k = max(0.88, min(1.25, 1 - 0.0117 * ($cout - 34)));
    $px = fn (float $base) => round($base * $k, 2).'px';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 20px 34px 46px; }
/* dompdf reprend un bloc fixe sur chaque page : le pied suit le document. */
.pied-page { position: fixed; bottom: 14px; left: 34px; right: 34px;
             border-top: 0.5px solid #ccc; padding-top: 5px;
             text-align: center; font-size: 7.4px; color: #666; line-height: 1.4; }
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.header-table td { vertical-align: middle; }
.logo { height: 86px; }
.tutelle { text-align: center; vertical-align: top; }
.etoiles { font-size: 7px; color: #888; letter-spacing: 1px; margin: 1px 0; }
.ministere { font-size: 7.6px; font-weight: bold; line-height: 1.25; }
.sigle-ministere { font-size: 7.6px; font-weight: bold; }
.institut { font-size: 8px; font-weight: bold; line-height: 1.25; margin-top: 2px; }
.coordonnees { font-size: 7px; color: #555; margin-top: 2px; line-height: 1.3; }
.agrement { font-size: 6.6px; color: #666; margin-top: 2px; line-height: 1.25; }
.annee { text-align: center; font-size: 9.5px; margin-top: 3px; }
.titre-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.titre-table td { vertical-align: bottom; }
.doc-title { font-size: 15px; font-weight: bold; letter-spacing: .5px; text-align: center; }
.reference { text-align: right; font-size: 9px; font-weight: bold; white-space: nowrap; }
.republic { font-size: 9px; font-weight: bold; text-align: center; }
.motto { font-size: 8px; color: #777; font-style: italic; text-align: center; margin-top: 2px; }
.sep { border: none; border-top: 2px solid #1a1a1a; margin: 6px 0; }
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
.info-table td { padding: 2px 6px; font-size: 10px; }
.info-label { font-weight: bold; width: 20%; line-height: 1.2; }
.info-label .en { display: block; font-weight: normal; font-style: italic; font-size: 7.6px; color: #666; }
.en-th { display: block; font-weight: normal; font-style: italic; font-size: {{ $px(7.2) }}; color: #d8d8d8; }
.info-value { border-bottom: 1px solid #bbb; }
.notes-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.notes-table th { background: #1a1a1a; color: white; padding: {{ $px(3.6) }} 6px; font-size: {{ $px(8.9) }}; text-align: center; }
.notes-table th.l { text-align: left; }
.notes-table td { padding: {{ $px(3.5) }} 6px; font-size: {{ $px(8.9) }}; line-height: 1.32; border-bottom: 0.5px solid #e5e5e5; text-align: center; }
.notes-table td.l { text-align: left; }
.sem-row td { background: #1a1a1a; color: #fff; font-weight: bold; font-size: {{ $px(9.3) }}; padding: {{ $px(3.8) }} 6px; text-align: left; }
.ue-row td { background: #e8f5e9; font-weight: bold; border-top: 0.5px solid #c8e6c9; border-bottom: 0.5px solid #c8e6c9; }
.ue-row td.l { text-align: left; }
.recap { width: 100%; border-collapse: collapse; margin-top: {{ $px(10) }}; border: 1px solid #1a1a1a; }
.recap td { padding: {{ $px(5) }} 12px; font-size: 10.5px; }
.recap .k { background: #f0f0f0; font-weight: bold; width: 60%; }
.recap .v { text-align: right; font-weight: bold; }
.recap .mention { font-size: 12px; letter-spacing: .4px; }
.bas-de-releve { page-break-inside: avoid; }
.sig { width: 100%; margin-top: {{ $px(13) }}; border-collapse: collapse; }
.sig td { text-align: center; font-size: 9px; width: 50%; }
.sig-line { border-top: 1px solid #999; margin: 16px 20px 0; }
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

    // Le pied de page : ou se trouve l'institut, et comment l'appeler.
    $lieu = collect([
        $etab['sigle'] ?? null,
        $entete['nom_fr'] ?? ($etab['nom'] ?? null),
        filled($etab['bp'] ?? null) ? 'BP '.$etab['bp'] : null,
        $etab['adresse'] ?? null,
    ])->filter()->implode(' — ');

    // Le numero d'ordre du releve : une suite melangee de l'identifiant de
    // l'etudiant (7919 et 9973 sont premiers entre eux, donc deux etudiants
    // n'ont jamais le meme numero) suivie de l'annee. Il ne bouge pas d'une
    // impression a l'autre : c'est la reference du document.
    $ordre = str_pad((string) ((($user->id ?? 0) * 7919 + 1234) % 9973), 4, '0', STR_PAD_LEFT);
    // Le sigle tient en un mot dans une reference : « IUM NDAZOA » y entre en « IUM ».
    $sigleCourt = strtok((string) ($etab['sigle'] ?? 'IUM'), ' ') ?: 'IUM';
    $reference = 'N° '.$ordre.'/DG/'.$sigleCourt.'/'.str_replace('/', '-', (string) $anneeReleve);

    $joindre = collect([
        filled($etab['telephone'] ?? null)
            ? 'Tél.'."\u{00A0}".str_replace(' ', "\u{00A0}", $etab['telephone'])
            : null,
        $etab['email'] ?: ($etab['domaine'] ?? null),
    ])->filter()->implode(' — ');

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

<table class="titre-table">
    <tr>
        <td width="26%"></td>
        <td width="48%" class="doc-title">RELEVÉ DE NOTES / TRANSCRIPT</td>
        <td width="26%" class="reference">{{ $reference }}</td>
    </tr>
</table>
<div class="annee">Année académique / Academic year : <strong>{{ $anneeReleve }}</strong></div>

<hr class="sep">

<table class="info-table">
    <tr>
        <td class="info-label">Nom &amp; Prénom<span class="en">Surname and name</span></td>
        <td class="info-value"><strong>{{ mb_strtoupper($user->name ?? '') }} {{ $user->lastname ?? '' }}</strong></td>
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
            <th class="l" style="width:9%;">Codes</th>
            <th class="l" style="width:41%;">Intitulés</th>
            <th style="width:8%;">Crédits</th>
            <th style="width:9%;">Note<span class="en-th">/20</span></th>
            <th style="width:11%;">Crédits acquis<span class="en-th">credits earned</span></th>
            <th style="width:14%;">Année académique<span class="en-th">et semestre</span></th>
            <th style="width:8%;">Grade</th>
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

    // La mention se lit a la couleur : du vert de « Tres bien » au rouge.
    $couleurMention = match ($mention) {
        'Très bien' => '#1b5e20',
        'Bien' => '#2e7d32',
        'Assez bien' => '#0277bd',
        'Passable' => '#ef6c00',
        'Insuffisant' => '#c62828',
        default => '#1a1a1a',
    };
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
        <td class="v mention" style="color: {{ $couleurMention }};">{{ mb_strtoupper($mention) }}</td>
    </tr>
</table>

<table class="sig">
    <tr>
        <td>Le Chef de Département<div class="sig-line"></div></td>
        <td>Le Chef d'établissement<div class="sig-line"></div></td>
    </tr>
</table>

</div>

<div class="pied-page">
    {{ $lieu }}
    @if ($joindre)<br>{{ $joindre }}@endif
</div>

</body>
</html>
