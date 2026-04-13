<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Attestation de Scolarité</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; line-height: 1.6; color: #333; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin-bottom: 30px; }
        .content { margin: 0 40px; }
        .info-table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; }
        .info-table td { padding: 8px; border: 1px solid #ddd; }
        .info-table td:first-child { font-weight: bold; width: 40%; background-color: #f9f9f9; }
        .footer { margin-top: 60px; text-align: right; margin-right: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Office de la Formation Professionnelle et de la Promotion du Travail</h2>
        <h3>INSTITUT SPECIALISE DE TECHNOLOGIE APPLIQUEE NTIC TANGER</h3>
    </div>

    <div class="title">ATTESTATION DE POURSUITE DE FORMATION</div>

    <div class="content">
        <p>Je soussigné, Directeur de l'établissement, atteste que le stagiaire :</p>

        <table class="info-table">
            <tr>
                <td>Nom et Prénom :</td>
                <td><strong>{{ $nom_complet }}</strong></td>
            </tr>
            <tr>
                <td>Né(e) le :</td>
                <td>{{ $date_naissance }}</td>
            </tr>
            <tr>
                <td>Niveau de formation :</td>
                <td>{{ $niveau }}</td>
            </tr>
            <tr>
                <td>Spécialité :</td>
                <td>{{ $specialite }}</td>
            </tr>
            <tr>
                <td>Année d'étude :</td>
                <td>{{ $annee }}</td>
            </tr>
            <tr>
                <td>N° d'inscription :</td>
                <td>{{ $matricule }}</td>
            </tr>
        </table>

        <p>- Poursuit sa formation à l'établissement depuis le : <strong>{{ $date_debut }}</strong>.</p>
        <br>
        <p><em>Cette attestation est délivrée à l'intéressé pour servir et valoir ce que de droit.</em></p>
    </div>

    <div class="footer">
        <p>Fait à Tanger, le {{ $date_generation }}</p>
        <br><br>
        <p><strong>Signature et Cachet du Directeur</strong></p>
    </div>
</body>
</html>
