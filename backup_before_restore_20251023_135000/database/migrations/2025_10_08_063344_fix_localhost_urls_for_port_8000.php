<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any URLs that might be pointing to localhost/piclicks to localhost:8000
        // This is specifically for the development environment on port 8000
        
        // Check if there are any URLs in the database that need updating
        $tables_to_check = [
            'design_collage' => ['image', 'image_edited'],
            'design_collage_master' => ['image_path'],
            'uploaded_images' => ['print_path', 'preview_path'],
            'users' => ['profile_picture'],
            'admins' => ['profile_picture']
        ];
        
        foreach ($tables_to_check as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        // Update localhost/piclicks to localhost:8000
                        DB::table($table)
                            ->where($column, 'like', 'http://localhost/piclicks%')
                            ->update([
                                $column => DB::raw("REPLACE($column, 'http://localhost/piclicks/', 'http://localhost:8000/')")
                            ]);
                            
                        // Update any other localhost references that might be missing port
                        DB::table($table)
                            ->where($column, 'like', 'http://localhost/%')
                            ->where($column, 'not like', 'http://localhost:%')
                            ->update([
                                $column => DB::raw("REPLACE($column, 'http://localhost/', 'http://localhost:8000/')")
                            ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert localhost:8000 back to localhost/piclicks
        $tables_to_check = [
            'design_collage' => ['image', 'image_edited'],
            'design_collage_master' => ['image_path'],
            'uploaded_images' => ['print_path', 'preview_path'],
            'users' => ['profile_picture'],
            'admins' => ['profile_picture']
        ];
        
        foreach ($tables_to_check as $table => $columns) {
            if (Schema::hasTable($table)) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        DB::table($table)
                            ->where($column, 'like', 'http://localhost:8000%')
                            ->update([
                                $column => DB::raw("REPLACE($column, 'http://localhost:8000/', 'http://localhost/piclicks/')")
                            ]);
                    }
                }
            }
        }
    }
};