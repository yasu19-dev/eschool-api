<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Filiere;
use Illuminate\Http\Request;

class PublicController extends Controller
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
    public function destroy(Contact $contact) {
    $contact->delete();
    return response()->json(['message' => 'Supprimé']);
}
    public function getFilieres()
    {
        // On récupère toutes les filières de la base
        $filieres = Filiere::all();
        return response()->json($filieres);
    }
public function getFaq()
{
    return \App\Models\FaqCategorie::all();
}
public function postContact(Request $request) {
    $request->validate([
        'nom' => 'required|string',
        'email' => 'required|email',
        'sujet' => 'required|string',
        'message' => 'required|string',
    ]);

    Contact::create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'nom' => $request->nom,
        'email' => $request->email,
        'telephone' => $request->tel,
        'sujet' => $request->sujet,
        'message' => $request->message,
    ]);

    return response()->json(['message' => 'Message reçu !']);
}
public function getMessagesForAdmin() {
    // On récupère les messages du plus récent au plus ancien
    return Contact::orderBy('created_at', 'desc')->get();
}

public function markAsRead(Contact $contact) {
    $contact->update(['lu' => true]);
    return response()->json(['message' => 'Marqué comme lu']);
}


}
