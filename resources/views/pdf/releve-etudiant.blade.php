<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 26px 34px; }
.header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
.header-table td { vertical-align: middle; }
.logo { height: 62px; }
.school-fr { font-size: 13px; font-weight: bold; }
.school-en { font-size: 10px; color: #444; margin-top: 2px; }
.doc-title { font-size: 15px; font-weight: bold; margin-top: 8px; letter-spacing: .5px; }
.republic { font-size: 9px; font-weight: bold; text-align: center; }
.motto { font-size: 8px; color: #777; font-style: italic; text-align: center; margin-top: 2px; }
.sep { border: none; border-top: 2px solid #1a1a1a; margin: 8px 0; }
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.info-table td { padding: 3px 6px; font-size: 10px; }
.info-label { font-weight: bold; width: 20%; }
.info-value { border-bottom: 1px solid #bbb; }
.notes-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.notes-table th { background: #1a1a1a; color: white; padding: 4px 6px; font-size: 8.5px; text-align: center; }
.notes-table th.l { text-align: left; }
.notes-table td { padding: 3px 6px; font-size: 8.5px; border-bottom: 0.5px solid #e5e5e5; text-align: center; }
.notes-table td.l { text-align: left; }
.sem-row td { background: #1a1a1a; color: #fff; font-weight: bold; font-size: 9px; padding: 4px 6px; text-align: left; }
.ue-row td { background: #e8f5e9; font-weight: bold; border-top: 0.5px solid #c8e6c9; border-bottom: 0.5px solid #c8e6c9; }
.ue-row td.l { text-align: left; }
.matiere-row td.l { padding-left: 16px; }
.ok { color: #1a6b1a; }
.ko { color: #b71c1c; }
.recap { width: 100%; border-collapse: collapse; margin-top: 14px; border: 1px solid #1a1a1a; }
.recap td { padding: 6px 12px; font-size: 10.5px; }
.recap .k { background: #f0f0f0; font-weight: bold; width: 60%; }
.recap .v { text-align: right; font-weight: bold; }
.bareme { margin-top: 6px; font-size: 7.5px; color: #555; }
.footer { margin-top: 20px; border-top: 1px solid #ccc; padding-top: 8px; text-align: center; font-size: 8px; color: #888; }
.sig { width: 100%; margin-top: 26px; border-collapse: collapse; }
.sig td { text-align: center; font-size: 9px; width: 50%; }
.sig-line { border-top: 1px solid #999; margin: 26px 20px 4px; }
</style>
</head>
<body>

<table class="header-table">
    <tr>
        <td width="20%" style="text-align:center;"><img src="https://ium-ndazoa.com/images/logo.png" class="logo" alt="IUM"></td>
        <td width="60%" style="text-align:center;">
            <div class="school-fr">IUM NDAZOA</div>
            <div class="school-en">Institut Universitaire La Majestueuse</div>
            <div class="doc-title">RELEVÉ DE NOTES / TRANSCRIPT</div>
        </td>
        <td width="20%">
            <div class="republic">REPUBLIQUE DU CAMEROUN</div>
            <div class="motto">Paix — Travail — Patrie</div>
            <div class="republic" style="margin-top:5px;">REPUBLIC OF CAMEROON</div>
            <div class="motto">Peace — Work — Fatherland</div>
        </td>
    </tr>
</table>

<hr class="sep">

<table class="info-table">
    <tr>
        <td class="info-label">Matricule :</td>
        <td class="info-value">{{ $user->matricule ?? '—' }}</td>
        <td class="info-label">Nom &amp; Prénom :</td>
        <td class="info-value"><strong>{{ strtoupper($user->name ?? '') }} {{ $user->lastname ?? '' }}</strong></td>
    </tr>
    <tr>
        <td class="info-label">Né(e) le :</td>
        <td class="info-value">{{ optional($user->date_naissance)->format('d/m/Y') ?? '—' }}</td>
        <td class="info-label">À :</td>
        <td class="info-value">{{ $user->lieu_naissance ?? '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Sexe :</td>
        <td class="info-value">{{ $user->sexe === 'M' ? 'Masculin' : ($user->sexe === 'F' ? 'Féminin' : '—') }}</td>
        <td class="info-label">Filière :</td>
        <td class="info-value">{{ $filiere ?? '—' }}</td>
    </tr>
    <tr>
        <td class="info-label">Cycle :</td>
        <td class="info-value" colspan="3">{{ $user->cycle?->name ?? '—' }}</td>
    </tr>
</table>

<table class="notes-table">
    <thead>
        <tr>
            <th class="l" style="width:10%;">Codes</th>
            <th class="l" style="width:35%;">Intitulés</th>
            <th style="width:9%;">Crédits</th>
            <th style="width:10%;">Notes/20</th>
            <th style="width:11%;">Crédits validés</th>
            <th style="width:15%;">Session de validation</th>
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
            @foreach ($ue['lignes'] as $l)
                <tr class="matiere-row">
                    <td class="l">{{ $l['code'] }}</td>
                    <td class="l">{{ $l['nom'] }}</td>
                    <td>{{ $l['credit'] }}</td>
                    <td class="{{ $l['moy'] !== null ? ($l['moy'] >= 10 ? 'ok' : 'ko') : '' }}">{{ $l['moy'] !== null ? number_format($l['moy'], 2) : '—' }}</td>
                    <td>{{ $l['credits_valides'] }}</td>
                    <td>{{ $l['session'] }}</td>
                    <td>—</td>
                </tr>
            @endforeach
        @endforeach
    @empty
        <tr><td colspan="7" style="text-align:center; color:#888; padding:30px 0;">Aucune note disponible.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="bareme">NB : 0&lt;=F&lt;6 ; 6&lt;=D&lt;8 ; 8&lt;=D+&lt;9 ; 9&lt;=C-&lt;10 ; 10&lt;=C&lt;11 ; 11&lt;=C+&lt;12 ; 12&lt;=B-&lt;13 ; 13&lt;=B&lt;14 ; 14&lt;=B+&lt;15 ; 15&lt;=A-&lt;16 ; 16&lt;=A&lt;20</div>

<table class="recap">
    <tr><td class="k">Moyenne générale</td><td class="v">{{ $globalStats['moy'] !== null ? number_format($globalStats['moy'], 2) . ' / 20' : '—' }}</td></tr>
    <tr><td class="k">Crédits validés (reste à valider)</td><td class="v">{{ $globalStats['valides'] }} / {{ $globalStats['credits'] }} ({{ $globalStats['reste'] }})</td></tr>
    <tr><td class="k">Nombre de matières notées</td><td class="v">{{ $globalStats['matieres'] }}</td></tr>
</table>

<table class="sig">
    <tr>
        <td>Le Chef de Département<div class="sig-line"></div></td>
        <td>Le Directeur des Études<div class="sig-line"></div></td>
    </tr>
</table>

<div class="footer">
    IUM NDAZOA — Institut Universitaire La Majestueuse — Ndazoa, route Yaoundé-Douala &nbsp;|&nbsp; Document généré le {{ now()->format('d/m/Y') }}
</div>

</body>
</html>
