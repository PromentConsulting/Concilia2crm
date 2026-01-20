<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Nombres y tipo entidad
            if (! Schema::hasColumn('accounts', 'short_name')) {
                $table->string('short_name', 255)->nullable()->after('legal_name');
            }

            if (! Schema::hasColumn('accounts', 'entity_type')) {
                $table->string('entity_type', 255)->nullable()->after('short_name');
            }

            // NIF/CIF propio (además del vat existente)
            if (! Schema::hasColumn('accounts', 'tax_id')) {
                $table->string('tax_id', 64)->nullable()->after('vat');
            }

            // Fax
            if (! Schema::hasColumn('accounts', 'fax')) {
                $table->string('fax', 64)->nullable()->after('phone');
            }

            // Dirección "genérica" (además de billing_*)
            if (! Schema::hasColumn('accounts', 'address')) {
                $table->string('address', 255)->nullable()->after('billing_street2');
            }

            if (! Schema::hasColumn('accounts', 'city')) {
                $table->string('city', 128)->nullable()->after('address');
            }

            if (! Schema::hasColumn('accounts', 'state')) {
                $table->string('state', 128)->nullable()->after('city');
            }

            if (! Schema::hasColumn('accounts', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('state');
            }

            if (! Schema::hasColumn('accounts', 'country')) {
                $table->string('country', 255)->nullable()->after('postal_code');
            }

            // Tipo, productos/servicios, tamaños, empleados, año fundación
            if (! Schema::hasColumn('accounts', 'company_type')) {
                $table->string('company_type', 255)->nullable()->after('size');
            }

            if (! Schema::hasColumn('accounts', 'products_services')) {
                $table->text('products_services')->nullable()->after('company_type');
            }

            if (! Schema::hasColumn('accounts', 'company_size_min')) {
                $table->unsignedInteger('company_size_min')->nullable()->after('products_services');
            }

            if (! Schema::hasColumn('accounts', 'company_size_max')) {
                $table->unsignedInteger('company_size_max')->nullable()->after('company_size_min');
            }

            if (! Schema::hasColumn('accounts', 'employee_count')) {
                $table->unsignedInteger('employee_count')->nullable()->after('company_size_max');
            }

            if (! Schema::hasColumn('accounts', 'founded_year')) {
                $table->unsignedInteger('founded_year')->nullable()->after('employee_count');
            }

            // Igualdad / contratos / calidad / RSE / certificaciones
            if (! Schema::hasColumn('accounts', 'public_contracts')) {
                $table->string('public_contracts', 255)->nullable()->after('public_administration');
            }

            if (! Schema::hasColumn('accounts', 'equality_plan')) {
                $table->string('equality_plan', 255)->nullable()->after('public_contracts');
            }

            if (! Schema::hasColumn('accounts', 'equality_mark')) {
                $table->string('equality_mark', 255)->nullable()->after('equality_plan');
            }

            if (! Schema::hasColumn('accounts', 'quality')) {
                $table->string('quality', 255)->nullable()->after('equality_mark');
            }

            if (! Schema::hasColumn('accounts', 'rse')) {
                $table->string('rse', 255)->nullable()->after('quality');
            }

            if (! Schema::hasColumn('accounts', 'other_certifications')) {
                $table->text('other_certifications')->nullable()->after('rse');
            }

            // Intereses
            if (! Schema::hasColumn('accounts', 'interest_local')) {
                $table->string('interest_local', 255)->nullable()->after('other_certifications');
            }

            if (! Schema::hasColumn('accounts', 'interest_regional')) {
                $table->string('interest_regional', 255)->nullable()->after('interest_local');
            }

            if (! Schema::hasColumn('accounts', 'interest_national')) {
                $table->string('interest_national', 255)->nullable()->after('interest_regional');
            }

            if (! Schema::hasColumn('accounts', 'no_interest')) {
                $table->string('no_interest', 255)->nullable()->after('interest_national');
            }

            // Datos internos / comerciales
            if (! Schema::hasColumn('accounts', 'sales_department')) {
                $table->string('sales_department', 255)->nullable()->after('group_note');
            }

            if (! Schema::hasColumn('accounts', 'cnae')) {
                $table->string('cnae', 64)->nullable()->after('sales_department');
            }

            if (! Schema::hasColumn('accounts', 'main_contact_role')) {
                $table->string('main_contact_role', 255)->nullable()->after('cnae');
            }

            if (! Schema::hasColumn('accounts', 'legacy_updated_at')) {
                $table->date('legacy_updated_at')->nullable()->after('main_contact_role');
            }

            // Notas largas de la empresa (comentarios del Excel)
            if (! Schema::hasColumn('accounts', 'notes')) {
                $table->text('notes')->nullable()->after('legacy_updated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $cols = [
                'short_name',
                'entity_type',
                'tax_id',
                'fax',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'company_type',
                'products_services',
                'company_size_min',
                'company_size_max',
                'employee_count',
                'founded_year',
                'public_contracts',
                'equality_plan',
                'equality_mark',
                'quality',
                'rse',
                'other_certifications',
                'interest_local',
                'interest_regional',
                'interest_national',
                'no_interest',
                'sales_department',
                'cnae',
                'main_contact_role',
                'legacy_updated_at',
                'notes',
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('accounts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
