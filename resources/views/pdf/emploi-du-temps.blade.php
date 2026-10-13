<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Emploi du temps</title>
{{--
    Calque sur le document officiel de l'institut : en-tete a trois colonnes,
    grille jours x creneaux, pause barree, signatures du chef de departement
    et du DAAC. Paysage A4.
--}}
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111; padding: 18px 22px; }

.entete { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
.entete td { vertical-align: top; }
.logo { height: 58px; }
.etablissement { font-size: 13px; font-weight: bold; text-align: center; }
.sous-titre { font-size: 9px; color: #444; text-align: center; margin-top: 2px; }

.bandeau { width: 100%; border-collapse: collapse; margin: 8px 0 10px; }
.bandeau td { font-size: 9.5px; padding: 1px 0; vertical-align: top; }
.bandeau .titre { font-weight: bold; font-size: 10px; }
.bandeau .anglais { font-style: italic; color: #555; }
.bandeau .droite { text-align: right; }
.niveau { font-size: 11px; font-weight: bold; margin: 2px 0 8px 40px; }

table.grille { width: 100%; border-collapse: collapse; table-layout: fixed; }
table.grille th, table.grille td { border: 1px solid #000; padding: 3px 2px; text-align: center; vertical-align: middle; }
table.grille th { font-size: 9px; font-weight: bold; }
table.grille th .en { display: block; font-weight: normal; font-style: italic; font-size: 8px; color: #555; }
.col-dates { width: 92px; font-size: 9px; font-weight: bold; }
.col-heures { width: 72px; font-size: 8.5px; font-weight: bold; }
.case { height: 54px; font-size: 8.5px; line-height: 1.25; }
.case .matiere { font-weight: bold; }
.pause { background: #d9d9d9; font-style: italic; font-weight: bold; font-size: 9px; }
.diagonale { font-size: 8px; text-align: left; padding-left: 4px; }
.diagonale .jours { text-align: right; display: block; font-weight: bold; }
.diagonale .heures { display: block; font-weight: bold; }

.signatures { width: 100%; margin-top: 26px; border-collapse: collapse; }
.signatures td { font-size: 9px; text-align: center; width: 50%; }
.signatures .en { font-style: italic; color: #555; font-size: 8px; }
</style>
</head>
<body>

@php
    $jours = config('emploi_du_temps.jours');
    $creneaux = $emploi->grilleCreneaux();
    $cases = $emploi->cases();
@endphp

<table class="entete">
    <tr>
        <td width="18%" style="text-align:center;">
            @if ($logo)<img src="{{ $logo }}" class="logo" alt="{{ config('etablissement.sigle') }}">@endif
        </td>
        <td width="64%">
            <div class="etablissement">{{ \Illuminate\Support\Str::upper(config('etablissement.nom')) }}</div>
        </td>
        <td width="18%"></td>
    </tr>
</table>

<table class="bandeau">
    <tr>
        <td width="30%">
            <span class="titre">EMPLOI DU TEMPS</span> / <span class="anglais">TIME TABLE</span>
        </td>
        <td width="38%" style="text-align:center;">
            <strong>Année académique : {{ $emploi->annee_academique }}</strong>
            <div class="anglais">Academic year</div>
        </td>
        <td width="32%" class="droite">
            <strong>Spécialité : {{ $emploi->specialite?->name ?? '—' }}</strong>
            <div class="anglais">Speciality</div>
            <strong>Semestre : {{ $emploi->semestre }}</strong>
            <div class="anglais">Semester</div>
        </td>
    </tr>
</table>

<div class="niveau">{{ $emploi->niveau }}</div>

<table class="grille">
    <thead>
        <tr>
            <th class="col-dates" rowspan="1">Dates (Semaines)</th>
            <th class="col-heures diagonale">
                <span class="jours">Jours / <em>Days</em></span>
                <span class="heures">Heures / <em>Periods</em></span>
            </th>
            @foreach ($jours as $jour)
                <th>{{ \Illuminate\Support\Str::upper($jour['long']) }}<span class="en">{{ $jour['anglais'] }}</span></th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($creneaux as $index => $creneau)
            <tr>
                @if ($loop->first)
                    <td class="col-dates" rowspan="{{ count($creneaux) }}">{{ $emploi->semaine() }}</td>
                @endif

                <td class="col-heures">{{ str_replace(':', 'H', $creneau['debut']) }}-{{ str_replace(':', 'H', $creneau['fin']) }}</td>

                @if ($creneau['pause'])
                    <td class="pause" colspan="{{ count($jours) }}">PAUSE</td>
                @else
                    @foreach ($jours as $numero => $jour)
                        @php($seance = $cases[$numero][$index] ?? null)
                        <td class="case">
                            @if ($seance)
                                <span class="matiere">{{ $seance->matiere() }}</span>
                                @if ($seance->nomEnseignant())<br>{{ $seance->nomEnseignant() }}@endif
                                @if ($seance->salleAffichee())<br>{{ $seance->salleAffichee() }}@endif
                                @if ($seance->progression())<br>{{ $seance->progression() }}@endif
                            @endif
                        </td>
                    @endforeach
                @endif
            </tr>
        @endforeach
    </tbody>
</table>

<table class="signatures">
    <tr>
        <td>
            Date et signature du Chef de Département
            <div class="en">Date and signature of Heads of Departments</div>
        </td>
        <td>
            Date et signature du DAAC
            <div class="en">Date and signature of the Director of Academic Affairs</div>
        </td>
    </tr>
</table>

</body>
</html>
