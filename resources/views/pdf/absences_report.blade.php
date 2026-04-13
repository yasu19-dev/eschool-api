<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport d'Absences - ISMONTIC</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .header { text-align: center; border-bottom: 2px solid #1D6F42; padding-bottom: 10px; margin-bottom: 20px; }
        .logo-text { font-size: 24px; font-weight: bold; color: #1D6F42; }
        .info { margin-bottom: 20px; font-size: 11px; }
        .stats-grid { width: 100%; margin-bottom: 20px; }
        .stat-box { border: 1px solid #ddd; padding: 10px; text-align: center; width: 23%; }
        .stat-value { font-size: 18px; font-weight: bold; color: #ef5350; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th { background-color: #1D6F42; color: white; padding: 8px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 8px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-text">ISMONTIC</div>
        <div>Rapport Analytique des Absences</div>
    </div>

    <div class="info">
        <strong>Période :</strong> {{ $period }} | <strong>Filière :</strong> {{ $filiere }} <br>
        <strong>Généré le :</strong> {{ date('d/m/Y H:i') }}
    </div>

    <table class="stats-grid">
        <tr>
            @foreach($cards as $card)
                <td class="stat-box">
                    <div style="font-size: 9px; color: #666;">{{ $card['title'] }}</div>
                    <div class="stat-value">{{ $card['value'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <h4 style="color: #1D6F42; border-bottom: 1px solid #eee;">Top 5 des étudiants les plus absents</h4>
    <table>
        <thead>
            <tr>
                <th>Nom Complet</th>
                <th>Groupe</th>
                <th>Total Absences</th>
                <th>Justifiées</th>
                <th>Non Justifiées</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topStudents as $student)
            <tr>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['groupe'] }}</td>
                <td><strong>{{ $student['absences'] }}</strong></td>
                <td style="color: green;">{{ $student['justifiees'] }}</td>
                <td style="color: red;">{{ $student['absences'] - $student['justifiees'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} eSchool Platform - Direction des Études ISMONTIC
    </div>
</body>
</html>
