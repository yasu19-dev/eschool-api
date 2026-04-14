<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .page-break { page-break-after: always; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1E88E5; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #1E88E5; margin-bottom: 5px; }
        .subtitle { font-size: 14px; color: #555; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background-color: #f8f9fa; border: 1px solid #dee2e6; padding: 8px; text-transform: uppercase; font-size: 9px; }
        td { border: 1px solid #dee2e6; padding: 6px; height: 60px; vertical-align: top; }

        .slot-time { background-color: #f1f3f5; font-weight: bold; text-align: center; vertical-align: middle; width: 80px; }
        .cell-content { font-size: 9px; }
        .module-name { font-weight: bold; color: #1E88E5; margin-bottom: 3px; display: block; }
        .info-label { color: #666; font-style: italic; font-size: 8px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8px; color: #aaa; }
    </style>
</head>
<body>
    @php
        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    @endphp

    @foreach($items as $item)
    <div class="header">
        <div class="title">ISMONTIC - DIRECTION</div>
        <div class="subtitle">{{ $title }} : {{ $item['header'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">Horaires</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($item['grid'] as $time => $sessions)
            <tr>
                <td class="slot-time">{{ str_replace('-', ' à ', $time) }}</td>
                @foreach($days as $day)
                <td>
                    @if(isset($sessions[$day]))
                        <div class="cell-content">
                            <span class="module-name">{{ $sessions[$day]['module'] }}</span>
                            <div class="info-label">
                                {{ $sessions[$day]['info'] }}<br>
                                Salle: {{ $sessions[$day]['salle'] }}
                            </div>
                        </div>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Généré le {{ $date }} - Page {{ $loop->iteration }}</div>

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
    @endforeach
</body>
</html>
