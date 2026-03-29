<?php

namespace Database\Seeders;

use App\Models\FaqCategorie;
use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'category' => 'Admission',
                'questions' => [
                    [
                        'q' => "Quelles sont les conditions d'admission à l'ISMONTIC ?",
                        'a' => "L'admission à l'ISMONTIC, en tant qu'Institut Supérieur, s'adresse principalement aux bacheliers. Selon la filière, l'accès se fait sur dossier (moyenne du Bac) et peut inclure des tests de positionnement pour évaluer les aptitudes des candidats.",
                    ],
                    [
                        'q' => "Quand commencent les inscriptions ?",
                        'a' => "Les inscriptions débutent généralement au mois de mai/juin sur la plateforme nationale de l'OFPPT. La sélection finale et les dépôts de dossiers se poursuivent jusqu'en septembre, selon les places disponibles pour la rentrée d'octobre.",
                    ],
                    [
                        'q' => "Quel est le coût de la formation ?",
                        'a' => "Conformément au système de l'OFPPT, la formation est financée par l'État. Les stagiaires admis ne doivent s'acquitter que des frais d'inscription annuels (assurance et frais de dossier), dont le montant varie selon le niveau (Technicien ou Technicien Spécialisé).",
                    ],
                ],
            ],
            [
                'category' => 'Formations',
                'questions' => [
                    [
                        'q' => "Quelle est la durée des formations proposées ?",
                        'a' => "La majorité de nos cursus menant au diplôme de Technicien Spécialisé s'étendent sur une durée de 2 ans, incluant une formation théorique à l'institut et des stages pratiques.",
                    ],
                    [
                        'q' => "Les diplômes sont-ils reconnus par l'État ?",
                        'a' => "Absolument. En tant qu'établissement public de l'OFPPT, tous les diplômes délivrés par l'ISMONTIC sont reconnus par l'État et permettent d'intégrer le marché de l'emploi ou de poursuivre des études supérieures (Licences Professionnelles, Écoles d'Ingénieurs).",
                    ],
                    [
                        'q' => "Y a-t-il des stages obligatoires ?",
                        'a' => "Oui, l'approche par compétences de l'OFPPT impose des stages en entreprise à la fin de chaque année de formation pour permettre au stagiaire d'appliquer ses acquis dans un environnement professionnel réel.",
                    ],
                ],
            ],
            [
                'category' => "Vie à l'institut",
                'questions' => [
                    [
                        'q' => "Quels sont les horaires de l'établissement ?",
                        'a' => "L'institut est ouvert du lundi au vendredi de 8h30 à 18h30. Les horaires de cours spécifiques sont définis dans l'emploi du temps de chaque groupe, consultable via le portail stagiaire de cette plateforme.",
                    ],
                    [
                        'q' => "L'établissement dispose-t-il d'un staff d'accompagnement ?",
                        'a' => "Oui, l'ISMONTIC dispose d'un corps professoral expert et d'une équipe administrative dédiée pour accompagner les stagiaires dans leurs démarches et leur orientation professionnelle.",
                    ],
                ],
            ],
        ];

        // 1. On parcourt le tableau principal
        foreach ($faqs as $faqGroup) {

            // 2. On crée (ou récupère) la catégorie dans la table 'faq_categories'
            // ⚠️ Ajuste 'nom' si ta colonne s'appelle 'name' ou 'title'
            $categoryRecord = FaqCategorie::firstOrCreate([
                'nom' => $faqGroup['category']
            ]);

            // 3. On parcourt les questions de cette catégorie
            foreach ($faqGroup['questions'] as $item) {

                // 4. On crée la question dans la table 'faq_items' en la liant à la catégorie
                FaqItem::firstOrCreate(
                    ['question' => $item['q']], // On vérifie si la question existe déjà
                    [
                        'faq_categorie_id' => $categoryRecord->id, // 👈 La clé étrangère ! Ajuste le nom si besoin.
                        'reponse' => $item['a'] // ⚠️ Ajuste 'answer' si ta colonne s'appelle 'reponse'
                    ]
                );
            }
        }
    }
}
