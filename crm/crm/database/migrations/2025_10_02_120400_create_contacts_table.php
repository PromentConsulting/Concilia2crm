<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->boolean('primary')->default(false);
            $table->string('first_name',128);
            $table->string('last_name',128);
            $table->string('email')->unique();
            $table->string('mobile',64)->nullable();
            $table->string('phone',64)->nullable();
            $table->string('extension',16)->nullable();
            $table->string('job_title',128)->nullable();
            $table->string('department',128)->nullable();
            $table->string('language',8)->nullable();
            $table->string('timezone',64)->nullable();
            $table->string('preferred_channel',16)->nullable();
            $table->string('decision_level',32)->nullable();
            $table->string('status',32)->default('active')->index();
            // RGPD
            $table->string('legal_basis',32)->nullable();
            $table->string('consent_status',32)->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_source',255)->nullable();
            $table->string('consent_ip',64)->nullable();
            // Ownership
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['last_name','first_name']);
        });

        Schema::create('contact_emails', function(Blueprint $table){
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('email')->unique();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('contact_phones', function(Blueprint $table){
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('phone',64);
            $table->string('type',32)->nullable(); // mobile|direct|office|other
            $table->boolean('is_primary')->default(false);
            $table->string('extension',16)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_phones');
        Schema::dropIfExists('contact_emails');
        Schema::dropIfExists('contacts');
    }
};
