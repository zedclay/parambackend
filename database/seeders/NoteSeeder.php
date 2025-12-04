<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use App\Models\Module;
use App\Models\Speciality;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates sample notes for each student, assigned to their modules/specialities
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $students = User::where('role', 'student')->get();

        if (!$admin || $students->isEmpty()) {
            $this->command->warn('No admin or students found. Please run UserSeeder first.');
            return;
        }

        // Get all specialities to assign notes
        $specialities = Speciality::all();
        
        if ($specialities->isEmpty()) {
            $this->command->warn('No specialities found. Please run SpecialitySeeder first.');
            return;
        }

        // Sample note templates for different subjects
        $noteTemplates = [
            [
                'title' => 'Cours d\'Anatomie et Physiologie',
                'description' => 'Cours complet sur l\'anatomie et la physiologie humaine. Ce document couvre les systèmes principaux du corps humain.',
                'mime_type' => 'application/pdf',
                'file_size' => 2048000, // 2MB
            ],
            [
                'title' => 'TP - Techniques de Soins',
                'description' => 'Travaux pratiques sur les techniques de soins infirmiers de base. Inclut les procédures et protocoles.',
                'mime_type' => 'application/pdf',
                'file_size' => 1536000, // 1.5MB
            ],
            [
                'title' => 'Pharmacologie - Médicaments courants',
                'description' => 'Guide des médicaments les plus couramment utilisés en soins infirmiers avec posologies et contre-indications.',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
            ],
            [
                'title' => 'Pathologie Générale',
                'description' => 'Cours sur les principales pathologies rencontrées en milieu hospitalier. Symptômes, diagnostics et traitements.',
                'mime_type' => 'application/pdf',
                'file_size' => 3072000, // 3MB
            ],
            [
                'title' => 'Éthique et Déontologie Professionnelle',
                'description' => 'Principes éthiques et déontologiques de la profession paramédicale. Code de déontologie et cas pratiques.',
                'mime_type' => 'application/pdf',
                'file_size' => 512000, // 500KB
            ],
            [
                'title' => 'Hygiène Hospitalière',
                'description' => 'Cours sur les règles d\'hygiène en milieu hospitalier. Prévention des infections nosocomiales.',
                'mime_type' => 'application/pdf',
                'file_size' => 1536000, // 1.5MB
            ],
            [
                'title' => 'Soins d\'Urgence',
                'description' => 'Guide pratique pour les soins d\'urgence. Procédures de réanimation et premiers secours.',
                'mime_type' => 'application/pdf',
                'file_size' => 2048000, // 2MB
            ],
            [
                'title' => 'Anatomie - Système Cardiovasculaire',
                'description' => 'Cours détaillé sur le système cardiovasculaire. Structure du cœur, circulation sanguine.',
                'mime_type' => 'application/pdf',
                'file_size' => 2560000, // 2.5MB
            ],
            [
                'title' => 'Techniques de Laboratoire - Hématologie',
                'description' => 'Cours sur les techniques d\'analyse hématologique. Prélèvements et analyses sanguines.',
                'mime_type' => 'application/pdf',
                'file_size' => 1792000, // 1.75MB
            ],
            [
                'title' => 'Radiologie - Principes de Base',
                'description' => 'Introduction à la radiologie médicale. Principes physiques, sécurité et techniques d\'imagerie.',
                'mime_type' => 'application/pdf',
                'file_size' => 2304000, // 2.25MB
            ],
        ];

        $noteCounter = 0;

        // Assign notes to each student
        foreach ($students as $student) {
            // Assign 3-5 notes per student
            $notesPerStudent = rand(3, 5);
            $assignedTemplates = array_rand($noteTemplates, min($notesPerStudent, count($noteTemplates)));
            
            if (!is_array($assignedTemplates)) {
                $assignedTemplates = [$assignedTemplates];
            }

            foreach ($assignedTemplates as $templateIndex) {
                $template = $noteTemplates[$templateIndex];
                
                // Assign to a random speciality (or the first one if student has enrolled modules)
                $speciality = $specialities->random();
                
                // Get a module from this speciality if available
                $module = Module::where('specialite_id', $speciality->id)->first();

                // Generate filename
                $filename = str_replace(' ', '_', strtolower($template['title'])) . '.pdf';
                $storedFilename = 'note_' . time() . '_' . $student->id . '_' . $noteCounter . '.pdf';
                $filePath = 'notes/' . date('Y/m') . '/' . $storedFilename;

                Note::create([
                    'module_id' => $module?->id,
                    'specialite_id' => $speciality->id,
                    'uploader_id' => $admin->id,
                    'assigned_student_id' => $student->id,
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'filename' => $filename,
                    'stored_filename' => $storedFilename,
                    'file_path' => $filePath,
                    'mime_type' => $template['mime_type'],
                    'file_size' => $template['file_size'],
                    'visibility' => 'private', // Private notes assigned to specific students
                    'download_count' => 0,
                ]);

                $noteCounter++;
            }
        }

        // Also create some public notes (visible to all students in a speciality)
        foreach ($specialities->take(3) as $speciality) {
            $module = Module::where('specialite_id', $speciality->id)->first();
            
            if ($module) {
                $publicTemplate = $noteTemplates[array_rand($noteTemplates)];
                $filename = str_replace(' ', '_', strtolower($publicTemplate['title'])) . '_public.pdf';
                $storedFilename = 'note_public_' . time() . '_' . $speciality->id . '.pdf';
                $filePath = 'notes/public/' . date('Y/m') . '/' . $storedFilename;

                Note::create([
                    'module_id' => $module->id,
                    'specialite_id' => $speciality->id,
                    'uploader_id' => $admin->id,
                    'assigned_student_id' => null, // Public note
                    'title' => $publicTemplate['title'] . ' (Public)',
                    'description' => $publicTemplate['description'] . ' - Note accessible à tous les étudiants de cette spécialité.',
                    'filename' => $filename,
                    'stored_filename' => $storedFilename,
                    'file_path' => $filePath,
                    'mime_type' => $publicTemplate['mime_type'],
                    'file_size' => $publicTemplate['file_size'],
                    'visibility' => 'specialite', // Visible to all students in this speciality
                    'download_count' => rand(5, 25),
                ]);
            }
        }

        // Create general semester and year notes (visible to all students)
        $generalNotes = [
            [
                'title' => 'Planning Semestre 1 - Année 2024-2025',
                'description' => 'Calendrier académique complet du premier semestre. Dates des cours, examens, vacances et événements importants.',
                'filename' => 'planning_semestre_1_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
            ],
            [
                'title' => 'Planning Semestre 2 - Année 2024-2025',
                'description' => 'Calendrier académique complet du deuxième semestre. Dates des cours, examens, vacances et événements importants.',
                'filename' => 'planning_semestre_2_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
            ],
            [
                'title' => 'Calendrier Année Académique 2024-2025',
                'description' => 'Vue d\'ensemble du calendrier académique complet pour l\'année 2024-2025. Inclut tous les semestres, examens et périodes de vacances.',
                'filename' => 'calendrier_annee_academique_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1536000, // 1.5MB
            ],
            [
                'title' => 'Calendrier des Examens - Session 1',
                'description' => 'Planning détaillé des examens de la première session. Dates, heures, salles et modalités d\'examen.',
                'filename' => 'calendrier_examens_session_1.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 512000, // 500KB
            ],
            [
                'title' => 'Calendrier des Examens - Session 2',
                'description' => 'Planning détaillé des examens de la session de rattrapage. Dates, heures, salles et modalités d\'examen.',
                'filename' => 'calendrier_examens_session_2.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 512000, // 500KB
            ],
            [
                'title' => 'Règlement Intérieur - Année 2024-2025',
                'description' => 'Règlement intérieur de l\'institut. Règles de vie, droits et devoirs des étudiants, procédures disciplinaires.',
                'filename' => 'reglement_interieur_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 768000, // 750KB
            ],
            [
                'title' => 'Guide de l\'Étudiant 2024-2025',
                'description' => 'Guide complet pour les étudiants. Informations pratiques, services disponibles, contacts utiles, procédures administratives.',
                'filename' => 'guide_etudiant_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 2048000, // 2MB
            ],
            [
                'title' => 'Planning des Stages - Année 2024-2025',
                'description' => 'Calendrier des stages pratiques. Dates, durées, établissements d\'accueil et modalités d\'évaluation.',
                'filename' => 'planning_stages_2024_2025.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
            ],
            [
                'title' => 'Organigramme de l\'Institut',
                'description' => 'Organigramme de l\'institut montrant la structure organisationnelle, les services et les responsables.',
                'filename' => 'organigramme_institut.png',
                'mime_type' => 'image/png',
                'file_size' => 512000, // 500KB
            ],
            [
                'title' => 'Carte du Campus',
                'description' => 'Plan du campus avec localisation des bâtiments, salles de cours, laboratoires, bibliothèque et services.',
                'filename' => 'carte_campus.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 1024000, // 1MB
            ],
            [
                'title' => 'Horaires des Cours - Semestre 1',
                'description' => 'Emploi du temps général pour le premier semestre. Horaires des cours par filière et niveau.',
                'filename' => 'horaires_cours_semestre_1.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 768000, // 750KB
            ],
            [
                'title' => 'Horaires des Cours - Semestre 2',
                'description' => 'Emploi du temps général pour le deuxième semestre. Horaires des cours par filière et niveau.',
                'filename' => 'horaires_cours_semestre_2.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 768000, // 750KB
            ],
            [
                'title' => 'Liste des Bibliothèques Partenaires',
                'description' => 'Liste des bibliothèques partenaires où les étudiants peuvent accéder aux ressources documentaires.',
                'filename' => 'bibliotheques_partenaires.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 256000, // 250KB
            ],
            [
                'title' => 'Procédures d\'Inscription et Réinscription',
                'description' => 'Guide détaillé des procédures d\'inscription et de réinscription. Documents requis, dates importantes, modalités de paiement.',
                'filename' => 'procedures_inscription_reinscription.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024000, // 1MB
            ],
        ];

        $generalNoteCounter = 0;
        foreach ($generalNotes as $generalNote) {
            $storedFilename = 'note_general_' . time() . '_' . $generalNoteCounter . '.' . 
                (strpos($generalNote['mime_type'], 'image') !== false ? 
                    (strpos($generalNote['mime_type'], 'png') !== false ? 'png' : 'jpg') : 'pdf');
            $filePath = 'notes/general/' . date('Y') . '/' . $storedFilename;

            Note::create([
                'module_id' => null, // General note, not linked to a specific module
                'specialite_id' => null, // General note, visible to all students
                'uploader_id' => $admin->id,
                'assigned_student_id' => null, // Visible to all students
                'title' => $generalNote['title'],
                'description' => $generalNote['description'],
                'filename' => $generalNote['filename'],
                'stored_filename' => $storedFilename,
                'file_path' => $filePath,
                'mime_type' => $generalNote['mime_type'],
                'file_size' => $generalNote['file_size'],
                'visibility' => 'specialite', // Using 'specialite' but with null specialite_id means visible to all
                'download_count' => rand(10, 100), // General notes have more downloads
            ]);

            $generalNoteCounter++;
        }

        $this->command->info("Created {$noteCounter} notes for students, " . $specialities->take(3)->count() . " public notes, and {$generalNoteCounter} general semester/year notes.");
    }
}

