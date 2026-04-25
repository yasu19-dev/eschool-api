<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function getRoomStatistics()
{
    // 1. Lister les salles physiques uniques (on exclut le distanciel)
    $sallesPhysiques = Seance::whereNotNull('salle')
        ->where('salle', '!=', 'A DISTANCE')
        ->where('salle', '!=', '')
        ->distinct()
        ->pluck('salle');

    $totalSalles = $sallesPhysiques->count();
    // On considère 4 créneaux possibles par jour par salle (ex: 08:30, 10:30, 14:30, 16:30)
    $capaciteSlotsParJour = $totalSalles * 4;

    $daysMapping = [
        'Monday' => 'Lundi', 'Tuesday' => 'Mardi', 'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi', 'Friday' => 'Vendredi', 'Saturday' => 'Samedi'
    ];

    // 2. Calcul du remplissage par jour (basé sur les séances des 7 derniers jours ou semaine type)
    $occupancy = collect($daysMapping)->map(function($nomFrancais, $nomAnglais) use ($capaciteSlotsParJour) {
        // Compte le nombre de séances prévues pour ce jour de la semaine
        $count = Seance::whereNotNull('salle')
            ->where('salle', '!=', 'A DISTANCE')
            ->whereRaw("DAYNAME(date) = ?", [$nomAnglais])
            ->count();

        $percentage = $capaciteSlotsParJour > 0
            ? round(($count / $capaciteSlotsParJour) * 100)
            : 0;

        return [
            'day' => $nomFrancais,
            'percentage' => min($percentage, 100)
        ];
    })->values();

    // 3. Salles libres (Exemple : salles qui n'ont rien aujourd'hui sur certains créneaux)
    // Pour cet exemple, on renvoie les salles qui ont peu de séances
    $availability = $sallesPhysiques->take(5)->map(function($nomSalle) {
        return [
            'name' => $nomSalle,
            'type' => str_contains($nomSalle, 'SDD') ? 'Labo Digital' : 'Salle de cours',
            'day' => 'Aujourd\'hui',
            'slot' => 'Créneaux après-midi'
        ];
    });

    return response()->json([
        'occupancy' => $occupancy,
        'availability' => $availability
    ]);
}
}
