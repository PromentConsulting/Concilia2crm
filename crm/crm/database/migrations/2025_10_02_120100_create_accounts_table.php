<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('vat', 64)->nullable()->index();
            $table->string('domain')->nullable()->index();
            $table->string('website')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('email')->nullable();
            $table->string('status', 32)->default('prospect')->index();
            $table->string('size', 64)->nullable();
            $table->string('source', 128)->nullable();
            $table->string('risk_level', 16)->nullable();
            // Billing
            $table->string('billing_street')->nullable();
            $table->string('billing_street2')->nullable();
            $table->string('billing_postal_code', 32)->nullable();
            $table->string('billing_city', 128)->nullable();
            $table->string('billing_state', 128)->nullable();
            $table->string('billing_country_code', 2)->nullable();
            $table->string('payment_method', 64)->nullable();
            $table->unsignedInteger('payment_term_days')->nullable();
            $table->string('iban', 64)->nullable();
            $table->string('bic', 64)->nullable();
            $table->string('sepa_mandate_ref', 128)->nullable();
            $table->date('sepa_mandate_date')->nullable();
            $table->string('fiscal_position', 64)->nullable();
            $table->string('tax_exemption_reason')->nullable();
            // E-invoicing
            $table->boolean('public_administration')->default(false);
            $table->string('dir3_office', 64)->nullable();
            $table->string('dir3_manager', 64)->nullable();
            $table->string('dir3_unit', 64)->nullable();
            $table->string('e_invoice_channel', 16)->nullable();
            $table->string('e_invoice_identifier', 128)->nullable();
            $table->boolean('e_invoice_ready')->default(false);
            $table->string('e_invoice_acceptance_status', 32)->nullable();
            $table->timestamp('e_invoice_acceptance_at')->nullable();
            $table->date('payment_effective_at')->nullable();
            $table->decimal('payment_effective_amount', 15, 2)->nullable();
            // Ownership and extras
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('owner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('group_note')->nullable();
            $table->string('id_contabilidad')->nullable();
            $table->string('system_origin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
