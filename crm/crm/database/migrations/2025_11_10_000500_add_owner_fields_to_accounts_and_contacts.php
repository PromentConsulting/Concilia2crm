<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ACCOUNTS
        Schema::table('accounts', function (Blueprint $table) {
            // owner_user_id
            if (!Schema::hasColumn('accounts', 'owner_user_id')) {
                $table->foreignId('owner_user_id')
                    ->nullable()
                    ->after('parent_account_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // owner_team_id
            if (!Schema::hasColumn('accounts', 'owner_team_id')) {
                $table->foreignId('owner_team_id')
                    ->nullable()
                    ->after('owner_user_id')
                    ->constrained('teams')
                    ->nullOnDelete();
            }
        });

        // CONTACTS
        Schema::table('contacts', function (Blueprint $table) {
            // owner_user_id
            if (!Schema::hasColumn('contacts', 'owner_user_id')) {
                $table->foreignId('owner_user_id')
                    ->nullable()
                    ->after('primary_account_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // owner_team_id
            if (!Schema::hasColumn('contacts', 'owner_team_id')) {
                $table->foreignId('owner_team_id')
                    ->nullable()
                    ->after('owner_user_id')
                    ->constrained('teams')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // ACCOUNTS
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'owner_user_id')) {
                // si la columna tiene FK, esto la elimina con la constraint
                $table->dropConstrainedForeignId('owner_user_id');
            }

            if (Schema::hasColumn('accounts', 'owner_team_id')) {
                $table->dropConstrainedForeignId('owner_team_id');
            }
        });

        // CONTACTS
        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'owner_user_id')) {
                $table->dropConstrainedForeignId('owner_user_id');
            }

            if (Schema::hasColumn('contacts', 'owner_team_id')) {
                $table->dropConstrainedForeignId('owner_team_id');
            }
        });
    }
};
