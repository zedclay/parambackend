<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CreatePlaceholderFiles extends Seeder
{
    /**
     * Create placeholder PDF files for seeded notes
     */
    public function run(): void
    {
        $notes = Note::all();
        $created = 0;
        $skipped = 0;

        foreach ($notes as $note) {
            // Check if file already exists
            $publicExists = Storage::disk('public')->exists($note->file_path);
            $localExists = Storage::disk('local')->exists($note->file_path);

            if ($publicExists || $localExists) {
                $skipped++;
                continue;
            }

            // Create directory if it doesn't exist
            $directory = dirname($note->file_path);
            Storage::disk('public')->makeDirectory($directory);

            // Create a simple PDF placeholder
            // For PDF files, create a minimal valid PDF
            if ($note->mime_type === 'application/pdf') {
                $pdfContent = $this->createMinimalPdf($note->title, $note->description);
            } elseif (in_array($note->mime_type, ['image/jpeg', 'image/jpg', 'image/png'])) {
                // For images, create a simple placeholder image
                $pdfContent = $this->createPlaceholderImage($note->mime_type);
            } else {
                // Default to PDF
                $pdfContent = $this->createMinimalPdf($note->title, $note->description);
            }

            // Store the file
            Storage::disk('public')->put($note->file_path, $pdfContent);
            $created++;
        }

        $this->command->info("Created {$created} placeholder files. Skipped {$skipped} existing files.");
    }

    /**
     * Create a minimal valid PDF file
     */
    private function createMinimalPdf(string $title, ?string $description): string
    {
        // Minimal PDF content (valid PDF structure)
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Catalog\n";
        $pdf .= "/Pages 2 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n";
        $pdf .= "2 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Pages\n";
        $pdf .= "/Kids [3 0 R]\n";
        $pdf .= "/Count 1\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n";
        $pdf .= "3 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Page\n";
        $pdf .= "/Parent 2 0 R\n";
        $pdf .= "/MediaBox [0 0 612 792]\n";
        $pdf .= "/Contents 4 0 R\n";
        $pdf .= "/Resources <<\n";
        $pdf .= "/Font <<\n";
        $pdf .= "/F1 5 0 R\n";
        $pdf .= ">>\n";
        $pdf .= ">>\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n";
        $pdf .= "4 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Length 100\n";
        $pdf .= ">>\n";
        $pdf .= "stream\n";
        $pdf .= "BT\n";
        $pdf .= "/F1 12 Tf\n";
        $pdf .= "100 700 Td\n";
        $pdf .= "(" . addslashes($title) . ") Tj\n";
        $pdf .= "0 -20 Td\n";
        if ($description) {
            $pdf .= "(" . addslashes(substr($description, 0, 50)) . ") Tj\n";
        }
        $pdf .= "ET\n";
        $pdf .= "endstream\n";
        $pdf .= "endobj\n";
        $pdf .= "5 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Font\n";
        $pdf .= "/Subtype /Type1\n";
        $pdf .= "/BaseFont /Helvetica\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n";
        $pdf .= "xref\n";
        $pdf .= "0 6\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= "0000000009 00000 n \n";
        $pdf .= "0000000058 00000 n \n";
        $pdf .= "0000000115 00000 n \n";
        $pdf .= "0000000306 00000 n \n";
        $pdf .= "0000000440 00000 n \n";
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size 6\n";
        $pdf .= "/Root 1 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n";
        $pdf .= "550\n";
        $pdf .= "%%EOF";

        return $pdf;
    }

    /**
     * Create a placeholder image (simple colored rectangle)
     */
    private function createPlaceholderImage(string $mimeType): string
    {
        // For images, we'll create a simple 1x1 pixel image
        // In a real scenario, you'd use GD or Imagick
        // For now, return a minimal valid image data

        if ($mimeType === 'image/png') {
            // Minimal PNG (1x1 transparent pixel)
            return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        } else {
            // Minimal JPEG (1x1 pixel)
            return base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA==');
        }
    }
}

