<?php

namespace Database\Seeders;

use App\Models\Filiere;
use App\Models\Institution; // 👉 N'oublie pas d'importer le modèle de ton établissement !
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
    public function run(): void
    {
        // 1. On récupère l'institution (ou on la crée si la table est vide)
        // Note : Si ta colonne s'appelle 'name' au lieu de 'nom', corrige-le ici
        $institution = Institution::firstOrCreate([
            'nom' => 'ISMONTIC'
        ]);

        // 2. Tes données React
        $filieres = [
            [
                'title' => 'Développement Digital',
                'code' => 'DD',
                'duration' => '2 ans',
                'niveau' => 'Technicien Spécialisé',
                'description' => 'Formation complète aux métiers du développement web et mobile avec les technologies les plus récentes.',
                'modules' => [
                    'Programmation Web (HTML, CSS, JavaScript)',
                    'Frameworks modernes (React, Angular, Vue.js)',
                    'Développement Backend (PHP, Node.js, Java)',
                    'Bases de données (SQL, NoSQL)',
                    'Développement Mobile (Android, iOS)',
                    'DevOps et déploiement',
                ],
                'debouches' => [
                    'Développeur Web Full Stack',
                    'Développeur Mobile',
                    'Développeur Frontend/Backend',
                    'Intégrateur Web',
                ],
                'color' => '#1E88E5',
            ],
            [
                'title' => 'Infrastructure Digitale',
                'code' => 'ID',
                'duration' => '2 ans',
                'niveau' => 'Technicien Spécialisé',
                'description' => 'Spécialisation en administration système, réseaux informatiques et cybersécurité.',
                'modules' => [
                    'Administration Système (Linux, Windows Server)',
                    'Réseaux informatiques (Cisco, routage, switching)',
                    'Virtualisation et Cloud Computing',
                    'Sécurité informatique et Cybersécurité',
                    'Supervision et monitoring',
                    'Scripts et automatisation',
                ],
                'debouches' => [
                    'Administrateur Système et Réseaux',
                    'Technicien Support IT',
                    'Ingénieur Cybersécurité',
                    'Administrateur Cloud',
                ],
                'color' => '#00C9A7',
            ],
            [   
                'title' => 'Infographie',
                'code' => 'INFO',
                'duration' => '2 ans',
                'niveau' => 'Technicien Spécialisé',
                'description' => "Formation spécialisée dans la communication visuelle, la création graphique et la maîtrise des outils de PAO pour l'impression et le digital.",
                'modules' => [
                    "Adobe Photoshop (Traitement d'image)",
                    'Adobe Illustrator (Dessin vectoriel)',
                    'Adobe InDesign (Mise en page)',
                    'Théorie des couleurs et Typographie',
                    'Conception UI/UX (Web & Mobile)',
                    'Motion Design et Montage Vidéo',
                ],
                'debouches' => [
                    'Infographiste 2D/3D',
                    'Webdesigner / UI Designer',
                    'Maquettiste PAO',
                    'Directeur Artistique Junior',
                ],
                'color' => '#E91E63',
            ],
        ];

        // 3. On attache l'ID de l'institution à chaque filière avant de l'enregistrer
        foreach ($filieres as $filiere) {
            $filiere['institution_id'] = $institution->id; // 👉 On lie la filière à l'école
            Filiere::firstOrCreate(['code' => $filiere['code']], $filiere);
        }
    }
}
