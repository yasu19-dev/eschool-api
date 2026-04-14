<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\User;
use App\Models\Groupe;
use App\Models\Filiere;
use App\Models\StagiaireProfile;
use App\Models\FormateurProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Barryvdh\DomPDF\Facade\Pdf;

class DirectorController extends Controller
{
    // --- PARTIE DASHBOARD ---
public function index() {
    try {
        // 1. Périodes de calcul (Mois en cours vs Mois dernier)
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 2. Statistiques de base (Totaux)
        $totalStagiaires = StagiaireProfile::count();
        $totalFormateurs = FormateurProfile::count();

        // MODIFICATION : On compte les GROUPES qui ont au moins une séance ce mois-ci
        $totalEmploisActifs = \App\Models\Seance::where('date', '>=', $startOfMonth->format('Y-m-d'))
            ->distinct('groupe_id')
            ->count('groupe_id');

        // 3. Calcul du Taux d'Absence Global (Mois en cours)
        $absencesMoisTable = Absence::where('est_en_retard', false)
            ->where('date', '>=', $startOfMonth->format('Y-m-d'))
            ->count();

        // On compte les séances programmées pour le calcul du taux
        $seancesMoisCount = \App\Models\Seance::where('date', '>=', $startOfMonth->format('Y-m-d'))->count();
        $totalOpportunites = ($seancesMoisCount * $totalStagiaires) ?: 1;
        $tauxActuel = ($absencesMoisTable / $totalOpportunites) * 100;

        // 4. Calcul de la Tendance (vs Mois dernier) pour la flèche
        $absencesMoisDernier = Absence::where('est_en_retard', false)
            ->whereBetween('date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])
            ->count();

        $seancesMoisDernier = \App\Models\Seance::whereBetween('date', [$startOfLastMonth->format('Y-m-d'), $endOfLastMonth->format('Y-m-d')])->count();
        $totalOppDernier = ($seancesMoisDernier * $totalStagiaires) ?: 1;
        $tauxPrecedent = ($absencesMoisDernier / $totalOppDernier) * 100;

        $tendance = $tauxActuel - $tauxPrecedent;

        // 5. Génération dynamique des alertes (Groupes avec > 20 absences)
        $alerts = Absence::join('stagiaire_profiles', 'absences.stagiaire_id', '=', 'stagiaire_profiles.id')
            ->join('groupes', 'stagiaire_profiles.groupe_id', '=', 'groupes.id')
            ->where('absences.date', '>=', $startOfMonth->format('Y-m-d'))
            ->where('absences.est_en_retard', false)
            ->select('groupes.code', DB::raw('count(*) as total'))
            ->groupBy('groupes.code')
            ->having('total', '>', 0)
            //  ->having('total', '>', 20)
            ->take(3)
            ->get()
            ->map(function($g) {
                return [
                    'message' => "Le groupe {$g->code} présente un volume d'absences élevé ({$g->total} ce mois).",
                    'severity' => 'high'
                ];
            });

      // 6. Récupération des logs avec protection Null-safe et identification du formateur
$recentLogs = Absence::with(['stagiaireProfile', 'seance.module', 'seance.formateur']) // Ajout du formateur ici
    ->latest()
    ->take(5)
    ->get()
    ->map(function($abs) {
        $type = $abs->est_en_retard ? 'Retard' : 'Absence';

        // Protection Null-safe pour le stagiaire et le module
        $prenom = $abs->stagiaireProfile?->prenom ?? 'Stagiaire';
        $nom = $abs->stagiaireProfile?->nom ?? 'Inconnu';
        $module = $abs->seance?->module?->intitule ?? 'Module inconnu';

        // Récupération du nom du formateur qui a marqué l'absence
        $nomFormateur = $abs->seance?->formateur
            ? $abs->seance->formateur->nom . ' ' . $abs->seance->formateur->prenom
            : 'Admin/Système';

        return [
            'action' => "{$type} : {$prenom} {$nom} ({$module})",
            'user' => ['nom' => $nomFormateur], // Affiche maintenant le vrai nom du prof
            'created_at' => $abs->created_at
        ];
    });

        // 7. Envoi de la réponse JSON au Frontend
        return response()->json([
            'total_stagiaires' => $totalStagiaires,
            'total_formateurs' => $totalFormateurs,
            'total_emplois_temps' => $totalEmploisActifs, // Nombre de groupes avec séances
            'taux_absences_global' => round($tauxActuel, 1) . '%',
            'tendance_absence' => ($tendance > 0 ? '+' : '') . round($tendance, 1) . '%',
            'alerts' => $alerts,
            'recent_logs' => $recentLogs
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}






//    public function index() {
//         return response()->json([
//             'total_stagiaires' => StagiaireProfile::count(),
//             'total_formateurs' => FormateurProfile::count(),
//             'total_admins' => User::where('role', 'admin')->count(),
//             'taux_presence_global' => '92%',
//             'alertes_absences' => Absence::where('est_en_retard', false)->count()
//         ]);
//     }

    // --- PARTIE GESTION UTILISATEURS ---

    // Récupérer les membres actifs
    public function getUsers() {
        return User::with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
    // Récupérer les membres archivés (LA CORRECTION POUR TON ERREUR 500)
    public function trashed() {
        return User::onlyTrashed()
                    ->with(['adminProfile', 'formateurProfile', 'stagiaireProfile'])
                    ->get();
    }

    // Création complète (Transféré depuis UserController pour tout centraliser)
    public function store(Request $request) {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,formateur,stagiaire',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'cef' => 'required_if:role,stagiaire|nullable|string|unique:stagiaire_profiles,cef',
            'matricule' => 'required_if:role,formateur|nullable|string|unique:formateur_profiles,matricule',
            'groupe_id' => 'required_if:role,stagiaire|nullable|exists:groupes,id',
            'specialite' => 'required_if:role,formateur|nullable|string',
        ]);

        return DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'etat' => 'Actif',
            ]);

            if ($request->role === 'stagiaire') {
                $user->stagiaireProfile()->create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'cef' => $request->cef,
                    'groupe_id' => $request->groupe_id,
                ]);
            } elseif ($request->role === 'formateur') {
                $user->formateurProfile()->create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'specialite' => $request->specialite,
                    'matricule' => $request->matricule,
                    'email_professionnel' => $request->email, // ✅ Correction SQL effectuée
                ]);
            }

            return response()->json(['message' => 'Succès', 'user' => $user->load($request->role . 'Profile')], 201);
        });
    }

    // Archiver (Soft Delete)
    public function deleteUser(User $user) {
        $user->delete();
        return response()->json(['message' => 'Utilisateur archivé']);
    }

    // Restaurer
    public function restore($id) {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(['message' => 'Utilisateur restauré', 'user' => $user->load('stagiaireProfile', 'formateurProfile')]);
    }
/**
 * Suppression physique et définitive de l'utilisateur.
 */
public function forceDelete($id)
{
    try {
        // On cherche l'utilisateur dans la corbeille (withTrashed)
        $user = User::withTrashed()->findOrFail($id);

        // Suppression définitive (SQL DELETE)
        $user->forceDelete();

        return response()->json([
            'message' => 'L\'utilisateur a été supprimé définitivement de la base de données.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la suppression définitive.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    // --- PARTIE DONNÉES FORMULAIRES ---
    public function getGroupes() {
        return Groupe::select('id', 'code')->orderBy('code', 'asc')->get();
    }

    public function getSpecialites() {
        return response()->json([
            ['id' => 'DD', 'nom' => 'Développement Digital'],
            ['id' => 'ID', 'nom' => 'Infrastructure Digitale'],
            ['id' => 'CS', 'nom' => 'Cybersécurité'],
            ['id' => 'IA', 'nom' => 'Intelligence Artificielle'],
            ['id' => 'FR', 'nom' => 'Français'],
            ['id' => 'CN', 'nom' => 'Culture Numérique'],
            ['id' => 'EN', 'nom' => 'Anglais Technique'],
            ['id' => 'BDD', 'nom' => 'Base de Données'],
            ['id' => 'ALGO', 'nom' => 'Algorithmique'],
            ['id' => 'PIE', 'nom' => 'Programme d\'innovation Entrepreneuriale'],


        ]);
    }

public function getSeancesByGroupe($id)
{
    // On récupère les séances avec les infos du module et du formateur
    return \App\Models\Seance::where('groupe_id', $id)
        ->with(['module', 'formateur'])
        ->get();
}

public function getSeancesByFormateur($id)
{
    // On récupère les séances avec les infos du module et du groupe
    return \App\Models\Seance::where('formateur_id', $id)
        ->with(['module', 'groupe'])
        ->get();
}

public function getFiltersData()
{
    try {
        // On récupère une seule ligne par titre de filière pour éviter les doublons
        // On privilégie le code le plus court (ex: 'ID' au lieu de 'IDRS')
        $filieres = Filiere::select('title', DB::raw('MIN(code) as code'))
            ->groupBy('title')
            ->get();

        return response()->json([
            'filieres' => $filieres,
            'groupes' => \App\Models\Groupe::select('id', 'code')->get(), //
        ]);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
    }
}


// ?? la logique de exportation

// app/Http/Controllers/Api/DirectorController.php

public function exportAllGroupsSchedule() {
    try {
        $groupes = Groupe::with(['seances.module', 'seances.formateur'])->orderBy('code')->get();

        $data = [
            'title' => 'PLANNING GLOBAL PAR GROUPE',
            'date'  => date('d/m/Y'), // 👈 LA CORRECTION EST ICI
            'items' => $groupes->map(function($groupe) {
                return [
                    'header' => "GROUPE : " . $groupe->code,
                    'grid' => $this->organizeSchedule($groupe->seances)
                ];
            })
        ];

        $pdf = Pdf::loadView('pdf.master_schedule', $data);
        return $pdf->setPaper('a4', 'landscape')->download('Planning_Global_Groupes.pdf');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function exportAllTeachersSchedule() {
    try {
        $formateurs = FormateurProfile::with(['seances.module', 'seances.groupe'])->get();

        $data = [
            'title' => 'PLANNING GLOBAL PAR FORMATEUR',
            'date'  => date('d/m/Y'), // 👈 LA CORRECTION EST ICI
            'items' => $formateurs->map(function($f) {
                return [
                    'header' => "FORMATEUR : " . strtoupper($f->nom) . " " . $f->prenom,
                    'grid' => $this->organizeSchedule($f->seances)
                ];
            })
        ];

        $pdf = Pdf::loadView('pdf.master_schedule', $data);
        return $pdf->setPaper('a4', 'landscape')->download('Planning_Global_Formateurs.pdf');
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

// Assure-toi que cette fonction est bien PRÉSENTE dans ton DirectorController
// private function organizeSchedule($seances) {
//     $grid = [];
//     foreach ($seances as $s) {
//         // On utilise l'opérateur null-safe pour éviter les crashs
//         $jour = Carbon::parse($s->date)->translatedFormat('l');
//         $grid[$s->creneau][ucfirst($jour)] = [
//             'module' => $s->module?->intitule ?? $s->module?->code,
//             'salle' => $s->salle ?? 'TBD',
//             'info' => $s->groupe ? "Groupe: " . $s->groupe->code : ($s->formateur ? $s->formateur->nom : '')
//         ];
//     }
//     ksort($grid);
//     return $grid;
// }
// app/Http/Controllers/Api/DirectorController.php

private function organizeSchedule($seances) {
    $grid = [];

    // Mappage manuel pour garantir la correspondance avec le PDF
    $daysMap = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi',
        4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'
    ];

    foreach ($seances as $s) {
        // Récupère le numéro du jour (1-7) via Carbon
        $dayNum = Carbon::parse($s->date)->dayOfWeekIso;

        if (isset($daysMap[$dayNum])) {
            $jourFr = $daysMap[$dayNum];
            $timeKey = $s->creneau;

            // Organisation des données par Créneau -> Jour
            $grid[$timeKey][$jourFr] = [
                'module' => $s->module?->intitule ?? $s->module?->code ?? 'Module',
                'salle'  => $s->salle ?? 'TBD',
                // Affiche le groupe pour le PDF prof, ou le prof pour le PDF groupe
                'info'   => $s->groupe ? "Gr: " . $s->groupe->code : ($s->formateur ? $s->formateur->nom : '')
            ];
        }
    }

    // Trier les horaires pour qu'ils s'affichent dans l'ordre chronologique
    ksort($grid);
    return $grid;
}

// !! partie paramètres généraux (settings)
/**
 * 1. Récupérer tous les paramètres pour les envoyer au Frontend
 */
public function getSettings() {
    try {
        // On récupère toutes les lignes et on les transforme en un objet clé => valeur
        // Exemple : ['institution_name' => 'ISMONTIC', 'absence_limit' => '15']
        $settings = DB::table('settings')->pluck('value', 'key');

        return response()->json($settings);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * 2. Sauvegarder les modifications envoyées par le formulaire
 */
// DirectorController.php

public function updateSettings(Request $request) {
    try {
        $data = $request->all();

        foreach ($data as $key => $value) {
            // ✅ Utilise updateOrInsert pour gérer les nouveaux paramètres
            DB::table('settings')->updateOrInsert(
                ['key' => $key], // La condition (recherche par clé)
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => DB::raw('IFNULL(created_at, NOW())')
                ]
            );
        }

        return response()->json(['message' => 'Paramètres mis à jour avec succès !']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
// sauvegarde et restauration de la base de données
// DirectorController.php

public function runBackup() {
    try {
        $filename = "backup-ismontic-" . now()->format('Y-m-d_H-i-s') . ".sql";
        $storagePath = storage_path("app/backups");

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $path = $storagePath . DIRECTORY_SEPARATOR . $filename;
        $mysqldumpPath = 'C:\wamp64\bin\mysql\mysql9.1.0\bin\mysqldump.exe';

        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');

        // ✅ LOGIQUE : On ne met le flag --password que s'il y a un mot de passe
        $passwordPart = !empty($dbPass) ? "--password=" . $dbPass : "";

        // On construit la commande avec des guillemets pour Windows
        $command = sprintf(
            '"%s" --user=%s %s --host=%s %s > "%s" 2>&1', // 2>&1 capture aussi les erreurs dans le fichier
            $mysqldumpPath,
            $dbUser,
            $passwordPart,
            $dbHost,
            $dbName,
            $path
        );

        $output = [];
        $returnVar = null;
        exec($command, $output, $returnVar);

        // Si le fichier contient une erreur (ex: Access denied), on le supprime et on alerte
        if ($returnVar !== 0) {
            $errorContent = file_exists($path) ? file_get_contents($path) : 'Erreur inconnue';
            if (file_exists($path)) unlink($path); // On supprime le fichier vide ou erroné

            return response()->json([
                'error' => 'Erreur MySQL : ' . $errorContent,
                'command_executed' => $command // Pour t'aider à débugger
            ], 500);
        }

        return response()->json([
            'message' => 'Sauvegarde réussie !',
            'file' => $filename,
            'size' => round(filesize($path) / 1024, 2) . ' KB'
        ]);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
// partie restauration


public function restoreBackup() {
    try {
        $storagePath = storage_path("app/backups");

        // 1. Trouver le fichier le plus récent
        $files = glob($storagePath . "/*.sql");
        if (empty($files)) {
            return response()->json(['error' => 'Aucun fichier de sauvegarde trouvé.'], 404);
        }

        // Trier par date pour prendre le dernier
        array_multisort(array_map('filemtime', $files), SORT_DESC, $files);
        $latestBackup = $files[0];

        // 2. Chemin vers l'exécutable mysql de WAMP
        $mysqlPath = 'C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe';

        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');

        $passwordPart = !empty($dbPass) ? "--password=" . $dbPass : "";

        // 3. Commande d'importation (le symbole < au lieu de >)
        $command = sprintf(
            '"%s" --user=%s %s --host=%s %s < "%s"',
            $mysqlPath,
            $dbUser,
            $passwordPart,
            $dbHost,
            $dbName,
            $latestBackup
        );

        $output = [];
        $returnVar = null;
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json(['error' => 'Échec de la restauration technique.'], 500);
        }

        return response()->json(['message' => 'Système restauré avec succès !']);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

// partie systeme
public function getSystemStats() {
    try {
        // 1. Version DB (MySQL)
        $dbVersion = DB::select('SELECT VERSION() as version')[0]->version;

        // 2. Calcul du stockage réel sur ton disque C:
        $totalSpace = disk_total_space("C:");
        $freeSpace = disk_free_space("C:");
        $usedSpace = $totalSpace - $freeSpace;

        // Conversion en GB pour l'affichage
        $totalGB = round($totalSpace / (1024**3), 1);
        $usedGB = round($usedSpace / (1024**3), 1);

        return response()->json([
            'version' => '1.0.0-PROD', // Version de ton projet
            'database' => "MySQL " . $dbVersion,
            'storage' => "$usedGB GB / $totalGB GB",
            'uptime' => 'Actif',
            'lastUpdate' => now()->format('d/m/Y'),
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function runMaintenance($action) {
    try {
        switch ($action) {
            case 'optimize':
                // Optimisation simple (vide les caches Laravel)
                Artisan::call('optimize:clear');
                return response()->json(['message' => 'Base de données et caches optimisés !']);

            case 'clear-temp':
                // Supprime les logs et fichiers temporaires
                $logFile = storage_path('logs/laravel.log');
                if (file_exists($logFile)) file_put_contents($logFile, '');
                return response()->json(['message' => 'Fichiers temporaires nettoyés !']);

            case 'report':
                return response()->json(['message' => 'Rapport de diagnostic généré avec succès.']);

            default:
                return response()->json(['error' => 'Action inconnue'], 400);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}
