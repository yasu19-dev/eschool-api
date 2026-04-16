<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier Médical - {{ $dossier->nom }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 14px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1E88E5; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 20px; font-weight: bold; color: #1E88E5; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; width: 40%; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FICHE DE COUVERTURE MÉDICALE OBLIGATOIRE (AMO)</div>
        <p>Référence Dossier : #{{ str_pad($dossier->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <h3>Identité du Stagiaire</h3>
    <table>
        <tr><th>Code Stagiaire (CEF)</th><td>{{ $dossier->code_stagiaire }}</td></tr>
        <tr><th>C.I.N</th><td>{{ $dossier->cin }}</td></tr>
        <tr><th>Nom Complet</th><td>{{ strtoupper($dossier->nom) }} {{ ucfirst($dossier->prenom) }}</td></tr>
    </table>

    <h3>Coordonnées</h3>
    <table>
        <tr><th>Adresse complète</th><td>{{ $dossier->adresse }}</td></tr>
        <tr><th>Ville</th><td>{{ $dossier->ville }}</td></tr>
        <tr><th>Téléphone</th><td>{{ $dossier->telephone }}</td></tr>
    </table>

    <h3>Situation Familiale</h3>
    <table>
        <tr><th>Situation matrimoniale</th><td>{{ ucfirst($dossier->situation_familiale) }}</td></tr>
        <tr><th>Bénéficiaire d'allocation ?</th><td>{{ $dossier->allocation_familiale ? 'OUI' : 'NON' }}</td></tr>
    </table>

    <div class="footer">
        Document généré le {{ $date_impression }} par le système ISMONTIC.
    </div>
</body>
</html>
