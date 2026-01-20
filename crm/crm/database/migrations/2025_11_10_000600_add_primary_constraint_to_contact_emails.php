<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contact_emails', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_emails', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('email');
            }

            $table->unique(['contact_id', 'is_primary'], 'contact_primary_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('contact_emails', function (Blueprint $table) {
            $table->dropUnique('contact_primary_email_unique');
        });
    }
};
