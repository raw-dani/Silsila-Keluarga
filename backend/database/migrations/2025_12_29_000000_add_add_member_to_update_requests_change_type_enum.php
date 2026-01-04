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
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB specific syntax
            DB::statement("ALTER TABLE update_requests MODIFY COLUMN change_type ENUM('biodata', 'hubungan', 'foto', 'add_member') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN with ENUM
            // We'll recreate the table (SQLite limitation)
            $this->recreateTableForSQLite(['biodata', 'hubungan', 'foto', 'add_member']);
        } elseif ($driver === 'pgsql') {
            // PostgreSQL syntax
            DB::statement("ALTER TYPE update_requests_change_type_enum ADD VALUE 'add_member'");
        } else {
            // Fallback - use string instead of enum
            Schema::table('update_requests', function (Blueprint $table) {
                $table->string('change_type')->default('biodata')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            // MySQL/MariaDB specific syntax
            DB::statement("ALTER TABLE update_requests MODIFY COLUMN change_type ENUM('biodata', 'hubungan', 'foto') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN with ENUM
            $this->recreateTableForSQLite(['biodata', 'hubungan', 'foto']);
        } elseif ($driver === 'pgsql') {
            // PostgreSQL - can't easily remove enum values, so we'll leave it
            // In production, you'd want to handle this differently
        } else {
            // Fallback - keep current values
        }
    }

    /**
     * Recreate table for SQLite (SQLite has limitations with ALTER TABLE)
     */
    private function recreateTableForSQLite(array $enumValues)
    {
        // Get all existing data
        $existingData = DB::table('update_requests')->get();

        // Drop and recreate the table
        DB::statement('DROP TABLE IF EXISTS update_requests');

        // Recreate with new enum
        Schema::create('update_requests', function (Blueprint $table) use ($enumValues) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('change_type', $enumValues)->default('biodata');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Restore data
        foreach ($existingData as $data) {
            DB::table('update_requests')->insert((array) $data);
        }
    }
};
