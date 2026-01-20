<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_delegations', function(Blueprint $table){
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('phone',64)->nullable();
            $table->string('email')->nullable();
            $table->string('street')->nullable();
            $table->string('street2')->nullable();
            $table->string('postal_code',32)->nullable();
            $table->string('city',128)->nullable();
            $table->string('state',128)->nullable();
            $table->string('country_code',2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_delegations');
    }
};
