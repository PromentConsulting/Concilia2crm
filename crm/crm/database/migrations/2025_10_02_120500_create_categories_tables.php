<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('applies_to',['account','contact','both'])->default('both');
            $table->enum('selection_type',['single','multi'])->default('multi');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->morphs('categorizable'); // categorizable_type, categorizable_id
            $table->timestamps();
            $table->unique(['category_id','categorizable_type','categorizable_id'],'uniq_categ');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorizables');
        Schema::dropIfExists('categories');
    }
};
