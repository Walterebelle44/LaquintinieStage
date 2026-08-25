<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use App\Models\Stagiaire;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Compte administrateur principal
        $admin = User::create([
            'nom' => 'EBELLE',
            'prenom' => 'Walter',
            'email' => 'admin@laquintinie.cm',
            'telephone' => '+237600000000',
            'password' => Hash::make('Admin@2026'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Encadrants
        $encadrant1 = User::create([
            'nom' => 'NGONO', 'prenom' => 'Paul', 'email' => 'p.ngono@laquintinie.cm',
            'telephone' => '+237670000001', 'password' => Hash::make('Encadrant@2026'),
            'role' => User::ROLE_ENCADRANT, 'is_active' => true, 'created_by' => $admin->id,
        ]);
        $encadrant2 = User::create([
            'nom' => 'MBALLA', 'prenom' => 'Sylvie', 'email' => 's.mballa@laquintinie.cm',
            'telephone' => '+237670000002', 'password' => Hash::make('Encadrant@2026'),
            'role' => User::ROLE_ENCADRANT, 'is_active' => true, 'created_by' => $admin->id,
        ]);

        // Établissements partenaires
        $iut = Etablissement::create([
            'nom' => 'IUT de Douala', 'ville' => 'Douala', 'filiere' => 'Génie Informatique',
            'contact_nom' => 'Service des stages', 'contact_email' => 'stages@iutdouala.cm',
        ]);
        $univ = Etablissement::create([
            'nom' => 'Université de Douala - Faculté des Sciences', 'ville' => 'Douala', 'filiere' => 'Informatique',
        ]);

        // Stagiaires de démonstration
        $stagiaireAcademique = User::create([
            'nom' => 'FOKOU', 'prenom' => 'Aristide', 'email' => 'a.fokou@stagiaires.cm',
            'password' => Hash::make('Stagiaire@2026'), 'role' => User::ROLE_STAGIAIRE,
            'is_active' => true, 'created_by' => $encadrant1->id,
        ]);
        Stagiaire::create([
            'user_id' => $stagiaireAcademique->id,
            'matricule' => 'STG-26-0001',
            'type_stage' => Stagiaire::TYPE_ACADEMIQUE,
            'encadrant_id' => $encadrant1->id,
            'etablissement_id' => $iut->id,
            'maitre_stage_ecole_nom' => 'Dr. TCHOUA',
            'maitre_stage_ecole_email' => 'tchoua@iutdouala.cm',
            'niveau_etude' => 'DUT 2 - Génie Informatique',
            'service' => 'Service Informatique',
            'sujet' => "Développement d'une application de gestion des stagiaires",
            'date_debut' => now()->subMonths(1),
            'date_fin' => now()->addMonths(2),
            'statut' => Stagiaire::STATUT_EN_COURS,
        ]);

        $stagiairePro = User::create([
            'nom' => 'ATANGANA', 'prenom' => 'Christelle', 'email' => 'c.atangana@stagiaires.cm',
            'password' => Hash::make('Stagiaire@2026'), 'role' => User::ROLE_STAGIAIRE,
            'is_active' => true, 'created_by' => $encadrant2->id,
        ]);
        Stagiaire::create([
            'user_id' => $stagiairePro->id,
            'matricule' => 'STG-26-0002',
            'type_stage' => Stagiaire::TYPE_PROFESSIONNEL,
            'encadrant_id' => $encadrant2->id,
            'referentiel_competences' => 'Maintenance parc informatique, support utilisateurs',
            'employabilite_souhaitee' => 'Technicien support IT',
            'service' => 'Service Informatique',
            'date_debut' => now()->subWeeks(2),
            'date_fin' => now()->addMonths(4),
            'statut' => Stagiaire::STATUT_EN_COURS,
        ]);

        $this->command?->info('Comptes créés :');
        $this->command?->info('  Admin      : admin@laquintinie.cm / Admin@2026');
        $this->command?->info('  Encadrant  : p.ngono@laquintinie.cm / Encadrant@2026');
        $this->command?->info('  Stagiaire  : a.fokou@stagiaires.cm / Stagiaire@2026');
    }
}
