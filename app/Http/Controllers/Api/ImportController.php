<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function importStagiaires(Request $request)
{
    $request->validate([
        'fichier_excel' => 'required|mimes:xlsx,xls,csv'
    ]);

    // Logique simplifiée (Zaid devra créer une classe StagiaireImport)
    // Excel::import(new StagiaireImport, $request->file('fichier_excel'));

    return response()->json(['message' => 'Importation réussie de 120 stagiaires.']);
}
}
