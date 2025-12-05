<?php
// Activate All Data Script
// Run: php activate_data.php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "Activating All Data\n";
echo "==========================================\n\n";

try {
    // Activate filieres
    $filieres = DB::table('filieres')->update(['is_active' => true]);
    echo "✅ Activated {$filieres} filieres\n";
    
    // Activate specialites
    $specialites = DB::table('specialities')->update(['is_active' => true]);
    echo "✅ Activated {$specialites} specialites\n";
    
    // Activate modules
    $modules = DB::table('modules')->update(['is_active' => true]);
    echo "✅ Activated {$modules} modules\n";
    
    // Publish announcements
    $announcements = DB::table('announcements')->update(['is_published' => true]);
    echo "✅ Published {$announcements} announcements\n";
    
    // Set published_at for announcements without date
    $updated = DB::table('announcements')
        ->whereNull('published_at')
        ->update(['published_at' => DB::raw('NOW()')]);
    echo "✅ Set published_at for {$updated} announcements\n";
    
    echo "\n==========================================\n";
    echo "✅ All Data Activated Successfully!\n";
    echo "==========================================\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

