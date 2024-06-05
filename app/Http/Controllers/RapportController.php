<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rapport;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contenu' => 'nullable|string',
            'fichier' => 'nullable|file|mimes:pdf,doc,docx,jpg,png',
        ]);

        // Déterminer le nom de fichier d'origine
        $documentName = $request->file('fichier')->getClientOriginalName();

        // Tenter de stocker le fichier
        try {
            $path = $request->file('fichier')->storeAs('Rapportes', $documentName, 'public');
        } catch (\Exception $e) {
            // En cas d'échec de la sauvegarde du fichier
            return response()->json(['error' => 'An error occurred while uploading the file'], 500);
        }

        $rapport = Rapport::create([
            'contenu' => $request->contenu,
            'fichier' => $path,
        ]);

        // Renvoyer les données sous forme de JSON
        return response()->json($rapport);
    }

    public function index()
    {
        $rapports = Rapport::all();
        return response()->json($rapports);
    }
}
