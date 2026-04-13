<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1E88E5; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #dee2e6; padding: 10px; text-align: center; height: 60px; }
        th { background-color: #1E88E5; color: white; text-transform: uppercase; font-size: 10px; }
        .time-column { background-color: #f8f9fa; font-weight: bold; width: 80px; color: #1E88E5; }
        .module { font-weight: bold; font-size: 12px; display: block; margin-bottom: 4px; color: #1e3a8a; }
        .groupe { color: #666; font-weight: bold; }
        .salle { display: block; margin-top: 5px; font-size: 9px; color: #1E88E5; border: 1px solid #bee3f8; background: #ebf8ff; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin:0; color: #1E88E5;">EMPLOI DU TEMPS - ISMONTIC</h1>
        <p>Formateur : <strong>{{ $formateur->name ?? $formateur->prenom . ' ' . $formateur->nom }}</strong> | Date d'édition : {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="time-column">Horaires</th>
                <th>Lundi</th>
                <th>Mardi</th>
                <th>Mercredi</th>
                <th>Jeudi</th>
                <th>Vendredi</th>
                <th>Samedi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slots as $slot)
                <tr>
                    <td class="time-column">{{ $slot }}</td>
                    {{-- On boucle sur les jours de 1 (Lundi) à 6 (Samedi) --}}
                    @for($i = 1; $i <= 6; $i++)
                        <td>
                            @php
                                // On cherche la séance qui correspond au créneau ET au jour de la semaine
                                $s = $seances->first(function($item) use ($i, $slot) {
                                    return date('N', strtotime($item->date)) == $i && $item->creneau == $slot;
                                });
                            @endphp

                            @if($s)
                                <span class="module">{{ $s->module->code ?? $s->module->intitule }}</span>
                                <span class="groupe">Gr: {{ $s->groupe->code }}</span>
                                <span class="salle">{{ $s->salle }}</span>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
