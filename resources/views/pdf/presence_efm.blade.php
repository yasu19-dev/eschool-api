<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; border-bottom: 2px solid black; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ISMONTIC - Liste de Présence EFM</h1>
        <p>Module: {{ $seance->module->nom }} | Groupe: {{ $seance->groupe->nom }}</p>
        <p>Date: {{ $seance->date }} | Salle: {{ $seance->salle }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom et Prénom</th>
                <th>Note /20</th>
                <th>Signature Avant</th>
                <th>Signature Après</th>
            </tr>
        </thead>
        <tbody>
            @foreach($seance->groupe->stagiaireprofiles as $index => $profile)
<tr>
    <td>{{ $index + 1 }}</td>
    {{-- On accède au nom via le profil ou la relation user --}}
    <td>{{ $profile->nom }} {{ $profile->prenom }}</td>
    <td></td>
    <td></td>
    <td></td>
</tr>
@endforeach
        </tbody>
    </table>
</body>
</html>
