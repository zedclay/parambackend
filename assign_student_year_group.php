<?php
// Quick Script to Assign Student to Year and Group
// Usage: php assign_student_year_group.php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "==========================================\n";
echo "Student Year/Group Assignment Helper\n";
echo "==========================================\n\n";

// List all students
echo "📋 Current Students:\n";
echo str_repeat("-", 80) . "\n";
$students = DB::table('users')
    ->where('role', 'student')
    ->select('id', 'name', 'email', 'year_id', 'group_id')
    ->get();

foreach ($students as $student) {
    $yearInfo = $student->year_id ? "Year ID: {$student->year_id}" : "❌ No Year";
    $groupInfo = $student->group_id ? "Group ID: {$student->group_id}" : "❌ No Group";
    echo "ID: {$student->id} | {$student->name} ({$student->email}) | {$yearInfo} | {$groupInfo}\n";
}

echo "\n";

// List available years
echo "📚 Available Years:\n";
echo str_repeat("-", 80) . "\n";
$years = DB::table('years')
    ->where('is_active', true)
    ->orderBy('year_number')
    ->get();

foreach ($years as $year) {
    $name = json_decode($year->name, true);
    $nameFr = $name['fr'] ?? "Year {$year->year_number}";
    echo "ID: {$year->id} | {$nameFr} (Year {$year->year_number})\n";
}

echo "\n";

// List available groups (first 10)
echo "👥 Available Groups (first 10):\n";
echo str_repeat("-", 80) . "\n";
$groups = DB::table('groups')
    ->where('is_active', true)
    ->orderBy('year_id')
    ->orderBy('name')
    ->limit(10)
    ->get();

foreach ($groups as $group) {
    echo "ID: {$group->id} | {$group->name} ({$group->code}) | Year ID: {$group->year_id}\n";
}

echo "\n";
echo "==========================================\n";
echo "To assign a student, edit this file and uncomment the lines below:\n";
echo "==========================================\n";
echo "\n";
echo "// Example: Assign student ID 1 to year ID 1 and group ID 1\n";
echo "// DB::table('users')\n";
echo "//     ->where('id', 1)\n";
echo "//     ->update(['year_id' => 1, 'group_id' => 1]);\n";
echo "\n";
echo "// Or by email:\n";
echo "// DB::table('users')\n";
echo "//     ->where('email', 'student@example.com')\n";
echo "//     ->update(['year_id' => 1, 'group_id' => 1]);\n";
echo "\n";

