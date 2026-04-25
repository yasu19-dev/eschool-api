<?php

namespace App\Http\Controllers;

use App\Models\FormateurProfile;
use App\Models\Module;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
   public function index() {
    // Modules sans formateur (ceux qui ont NULL ou vide)
    $unassigned = Module::whereNull('formateur_id')->get()->map(fn($m) => [
        'id' => $m->id,
        'name' => $m->intitule, // Utilisation de 'intitule'
        'code' => $m->code,
        'hours' => $m->masse_horaire // Utilisation de 'masse_horaire'
    ]);

    // Formateurs et leurs modules
    $trainers = FormateurProfile::with('modules')->get()->map(fn($f) => [
        'id' => $f->id,
        'name' => $f->nom . ' ' . $f->prenom,
        'specialty' => $f->specialite ?? 'Formateur',
        'modules' => $f->modules->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->intitule,
            'code' => $m->code,
            'hours' => $m->masse_horaire
        ])
    ]);

    return response()->json([
        'unassigned' => $unassigned,
        'trainers' => $trainers
    ]);
}

public function assign(Request $request) {
    // Sauvegarde du drag & drop
    $module = Module::findOrFail($request->module_id);
    $module->formateur_id = $request->trainer_id;
    $module->save();

    return response()->json(['success' => true]);
}
}
