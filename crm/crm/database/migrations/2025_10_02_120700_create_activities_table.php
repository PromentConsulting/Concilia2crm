<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activities', function(Blueprint $table){
            $table->id();
            $table->string('subject')->nullable();
            $table->string('channel',16)->nullable(); // email|call|meeting|task|note
            $table->string('direction',16)->nullable(); // inbound|outbound
            $table->string('status',16)->default('planned'); // planned|completed|canceled
            $table->string('outcome',32)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->morphs('subject'); // subject_type, subject_id (Account/Contact)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
