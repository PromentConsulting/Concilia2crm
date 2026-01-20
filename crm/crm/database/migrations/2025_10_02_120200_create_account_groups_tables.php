<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_groups', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('account_groups')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('account_group_members', function(Blueprint $table){
            $table->id();
            $table->foreignId('account_group_id')->constrained('account_groups')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['account_group_id','account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_group_members');
        Schema::dropIfExists('account_groups');
    }
};
