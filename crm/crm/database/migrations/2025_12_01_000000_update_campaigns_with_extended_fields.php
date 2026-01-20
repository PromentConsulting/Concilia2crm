<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('campaign_number')->nullable()->after('id');
            $table->boolean('email_confirmation_required')->default(false)->after('estado');
            $table->string('company_size')->nullable()->after('owner_user_id');
            $table->string('equality_plan_preference')->nullable()->after('company_size');
            $table->integer('habitantes')->nullable()->after('equality_plan_preference');
            $table->date('equality_plan_valid_until')->nullable()->after('habitantes');
            $table->string('equality_mark_preference')->nullable()->after('equality_plan_valid_until');
            $table->string('origen')->nullable()->after('equality_mark_preference');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_number',
                'email_confirmation_required',
                'company_size',
                'equality_plan_preference',
                'habitantes',
                'equality_plan_valid_until',
                'equality_mark_preference',
                'origen',
            ]);
        });
    }
};