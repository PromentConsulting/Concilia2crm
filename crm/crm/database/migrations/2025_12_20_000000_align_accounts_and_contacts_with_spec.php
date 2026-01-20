<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounts')) {
            Schema::table('accounts', function (Blueprint $table) {
                if (! Schema::hasColumn('accounts', 'nombre_abreviado')) {
                    $table->string('nombre_abreviado', 255)->nullable()->after('name');
                }

                if (! Schema::hasColumn('accounts', 'tipo_entidad')) {
                    $table->enum('tipo_entidad', [
                        'empresa_privada',
                        'aapp',
                        'sin_animo_de_lucro',
                        'corporacion_derecho_publico',
                        'particular',
                    ])->nullable()->after('entity_type');
                }

                if (! Schema::hasColumn('accounts', 'estado')) {
                    $table->enum('estado', ['activo', 'inactivo'])->default('activo')->after('status');
                }

                if (! Schema::hasColumn('accounts', 'departamento_comercial')) {
                    $table->string('departamento_comercial', 255)->nullable()->after('owner_team_id');
                }

                if (! Schema::hasColumn('accounts', 'localidad')) {
                    $table->string('localidad', 128)->nullable()->after('city');
                }

                if (! Schema::hasColumn('accounts', 'provincia')) {
                    $table->string('provincia', 128)->nullable()->after('localidad');
                }

                if (! Schema::hasColumn('accounts', 'pais')) {
                    $table->string('pais', 64)->nullable()->after('country');
                }

                if (! Schema::hasColumn('accounts', 'direccion')) {
                    $table->string('direccion', 255)->nullable()->after('address');
                }

                if (! Schema::hasColumn('accounts', 'codigo_postal')) {
                    $table->string('codigo_postal', 20)->nullable()->after('direccion');
                }

                if (! Schema::hasColumn('accounts', 'habitantes')) {
                    $table->unsignedInteger('habitantes')->nullable()->after('employee_count');
                }

                if (! Schema::hasColumn('accounts', 'group_name')) {
                    $table->string('group_name', 255)->nullable()->after('group_note');
                }

                if (! Schema::hasColumn('accounts', 'tipo_relacion_grupo')) {
                    $table->enum('tipo_relacion_grupo', ['independiente', 'matriz', 'filial'])
                        ->default('independiente')
                        ->after('group_name');
                }

                if (! Schema::hasColumn('accounts', 'email_confirmed_at')) {
                    $table->timestamp('email_confirmed_at')->nullable()->after('email');
                }

                if (! Schema::hasColumn('accounts', 'billing_legal_name')) {
                    $table->string('billing_legal_name', 255)->nullable()->after('legal_name');
                }

                if (! Schema::hasColumn('accounts', 'customer_code')) {
                    $table->string('customer_code', 100)->nullable()->after('billing_legal_name');
                }

                if (! Schema::hasColumn('accounts', 'billing_email')) {
                    $table->string('billing_email', 255)->nullable()->after('billing_city');
                }

                if (! Schema::hasColumn('accounts', 'billing_has_payment_issues')) {
                    $table->boolean('billing_has_payment_issues')->default(false)->after('billing_email');
                }

                if (! Schema::hasColumn('accounts', 'billing_notes')) {
                    $table->text('billing_notes')->nullable()->after('billing_has_payment_issues');
                }

                if (! Schema::hasColumn('accounts', 'is_billable')) {
                    $table->boolean('is_billable')->default(false)->after('billing_notes');
                }

                if (! Schema::hasColumn('accounts', 'odoo_id')) {
                    $table->string('odoo_id', 100)->nullable()->after('is_billable');
                }

                if (! Schema::hasColumn('accounts', 'billing_contact_id')) {
                    $table->foreignId('billing_contact_id')
                        ->nullable()
                        ->after('billing_email')
                        ->constrained('contacts')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('accounts', 'equality_plan_valid_until')) {
                    $table->date('equality_plan_valid_until')->nullable()->after('equality_plan');
                }

                if (! Schema::hasColumn('accounts', 'otras_certificaciones')) {
                    $table->text('otras_certificaciones')->nullable()->after('other_certifications');
                }
            });

            // Rellena campos canónicos con valores legacy antes de limpiar duplicados
            $this->backfillCanonicalAccountColumns();

            // Normalizamos algunos tipos booleanos para seguir la especificación
            $booleanColumns = [
                'public_contracts',
                'equality_plan',
                'equality_mark',
                'interest_local',
                'interest_regional',
                'interest_national',
                'no_interest',
                'quality',
                'rse',
            ];

            foreach ($booleanColumns as $column) {
                if (Schema::hasColumn('accounts', $column)) {
                    $this->normalizeBooleanColumn('accounts', $column);
                }
            }

            // Unicidad para campos clave
            if (
                Schema::hasColumn('accounts', 'customer_code')
                && ! $this->indexExists('accounts', 'accounts_customer_code_unique_spec')
            ) {
                $this->dropDuplicateColumnValues('accounts', 'customer_code');
                DB::statement('CREATE UNIQUE INDEX accounts_customer_code_unique_spec ON accounts(customer_code)');
            }

            if (
                Schema::hasColumn('accounts', 'tax_id')
                && ! $this->indexExists('accounts', 'accounts_tax_id_unique_spec')
            ) {
                $this->dropDuplicateColumnValues('accounts', 'tax_id');
                DB::statement('CREATE UNIQUE INDEX accounts_tax_id_unique_spec ON accounts(tax_id)');
            }

            // Limpia columnas duplicadas que ya no se usan
            $this->dropLegacyAccountColumns([
                'entity_type',
                'status',
                'short_name',
                'other_certifications',
            ]);
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'primary_email')) {
                    $table->string('primary_email')->nullable()->after('email');
                }

                if (! Schema::hasColumn('contacts', 'estado_contacto')) {
                    $table->enum('estado_contacto', ['activo', 'inactivo', 'rebotado', 'baja_marketing', 'no_localizable'])
                        ->default('activo')
                        ->after('status');
                }

                if (! Schema::hasColumn('contacts', 'motivo_cambio_estado')) {
                    $table->text('motivo_cambio_estado')->nullable()->after('estado_contacto');
                }

                if (! Schema::hasColumn('contacts', 'estado_cambiado_en')) {
                    $table->timestamp('estado_cambiado_en')->nullable()->after('motivo_cambio_estado');
                }

                if (! Schema::hasColumn('contacts', 'estado_cambiado_por')) {
                    $table->foreignId('estado_cambiado_por')
                        ->nullable()
                        ->after('estado_cambiado_en')
                        ->constrained('users')
                        ->nullOnDelete();
                }

                if (! Schema::hasColumn('contacts', 'flag_campanas')) {
                    $table->boolean('flag_campanas')->default(true)->after('consent_status');
                }

                if (! Schema::hasColumn('contacts', 'flag_facturacion')) {
                    $table->boolean('flag_facturacion')->default(false)->after('flag_campanas');
                }

                if (! Schema::hasColumn('contacts', 'nivel_decision')) {
                    $table->enum('nivel_decision', ['alto', 'medio', 'bajo'])->nullable()->after('decision_level');
                }

                if (! Schema::hasColumn('contacts', 'estado_rgpd')) {
                    $table->enum('estado_rgpd', ['consentimiento_otorgado', 'no_otorgado', 'revocado'])->nullable()->after('consent_status');
                }

                if (! Schema::hasColumn('contacts', 'canal_preferido')) {
                    $table->enum('canal_preferido', ['email', 'telefono', 'movil', 'otro'])->nullable()->after('preferred_channel');
                }

                if (! Schema::hasColumn('contacts', 'mensajeria_instantanea')) {
                    $table->string('mensajeria_instantanea', 255)->nullable()->after('mobile');
                }

                if (! Schema::hasColumn('contacts', 'comentarios')) {
                    $table->text('comentarios')->nullable()->after('notes');
                }

                if (! Schema::hasColumn('contacts', 'role_otro')) {
                    $table->string('role_otro', 255)->nullable();
                }
            });

            if (
                Schema::hasColumn('contacts', 'primary_email')
                && ! $this->indexExists('contacts', 'contacts_primary_email_unique')
            ) {
                $this->dropDuplicateColumnValues('contacts', 'primary_email');
                DB::statement('CREATE UNIQUE INDEX contacts_primary_email_unique ON contacts(primary_email)');
            }
        }

        if (Schema::hasTable('account_contact')) {
            Schema::table('account_contact', function (Blueprint $table) {
                if (! Schema::hasColumn('account_contact', 'observaciones')) {
                    $table->text('observaciones')->nullable()->after('es_principal');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('account_contact')) {
            Schema::table('account_contact', function (Blueprint $table) {
                if (Schema::hasColumn('account_contact', 'observaciones')) {
                    $table->dropColumn('observaciones');
                }
            });
        }

        if (Schema::hasTable('contacts')) {
            Schema::table('contacts', function (Blueprint $table) {
                foreach ([
                    'primary_email',
                    'estado_contacto',
                    'motivo_cambio_estado',
                    'estado_cambiado_en',
                    'estado_cambiado_por',
                    'flag_campanas',
                    'flag_facturacion',
                    'nivel_decision',
                    'estado_rgpd',
                    'canal_preferido',
                    'mensajeria_instantanea',
                    'comentarios',
                    'role_otro',
                ] as $column) {
                    if (Schema::hasColumn('contacts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            if (Schema::hasColumn('contacts', 'primary_email') && $this->indexExists('contacts', 'contacts_primary_email_unique')) {
                DB::statement('DROP INDEX contacts_primary_email_unique ON contacts');
            }
        }

        if (Schema::hasTable('accounts')) {
            if (Schema::hasColumn('accounts', 'customer_code') && $this->indexExists('accounts', 'accounts_customer_code_unique_spec')) {
                DB::statement('DROP INDEX accounts_customer_code_unique_spec ON accounts');
            }

            if (Schema::hasColumn('accounts', 'tax_id') && $this->indexExists('accounts', 'accounts_tax_id_unique_spec')) {
                DB::statement('DROP INDEX accounts_tax_id_unique_spec ON accounts');
            }

            Schema::table('accounts', function (Blueprint $table) {
                foreach ([
                    'nombre_abreviado',
                    'tipo_entidad',
                    'estado',
                    'departamento_comercial',
                    'localidad',
                    'provincia',
                    'pais',
                    'direccion',
                    'codigo_postal',
                    'habitantes',
                    'group_name',
                    'tipo_relacion_grupo',
                    'email_confirmed_at',
                    'billing_legal_name',
                    'customer_code',
                    'billing_email',
                    'billing_contact_id',
                    'billing_has_payment_issues',
                    'billing_notes',
                    'is_billable',
                    'odoo_id',
                    'equality_plan_valid_until',
                    'otras_certificaciones',
                ] as $column) {
                    if (Schema::hasColumn('accounts', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->count();

        return $result > 0;
    }

    private function normalizeBooleanColumn(string $table, string $column): void
    {
        DB::statement(
            "UPDATE {$table} SET {$column} = CASE " .
            "WHEN LOWER(CAST({$column} AS CHAR)) IN ('1', 'si', 'sí', 'yes', 'true', 't', 'y', 'on') THEN 1 " .
            "WHEN {$column} IS NULL THEN NULL " .
            "ELSE 0 END"
        );

        DB::statement("ALTER TABLE {$table} MODIFY {$column} TINYINT(1) NULL DEFAULT 0");
    }
    private function dropDuplicateColumnValues(string $table, string $column, string $idColumn = 'id'): void
    {
        $duplicates = DB::table($table)
            ->select($column)
            ->whereNotNull($column)
            ->groupBy($column)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($column);

        foreach ($duplicates as $value) {
            $ids = DB::table($table)
                ->where($column, $value)
                ->orderBy($idColumn)
                ->pluck($idColumn);

            $idsToNull = $ids->slice(1); // preserve first occurrence

            if ($idsToNull->isNotEmpty()) {
                DB::table($table)
                    ->whereIn($idColumn, $idsToNull)
                    ->update([$column => null]);
            }
        }
    }

    private function backfillCanonicalAccountColumns(): void
    {
        if (! Schema::hasTable('accounts')) {
            return;
        }

        // nombre_abreviado <= short_name si está vacío
        if (Schema::hasColumn('accounts', 'nombre_abreviado') && Schema::hasColumn('accounts', 'short_name')) {
            DB::table('accounts')
                ->whereNull('nombre_abreviado')
                ->whereNotNull('short_name')
                ->update(['nombre_abreviado' => DB::raw('short_name')]);
        }

        // estado <= status legacy
        if (Schema::hasColumn('accounts', 'estado') && Schema::hasColumn('accounts', 'status')) {
            DB::table('accounts')
                ->whereNull('estado')
                ->whereNotNull('status')
                ->update(['estado' => DB::raw("CASE LOWER(status) WHEN 'inactivo' THEN 'inactivo' ELSE 'activo' END")]);
        }

        // tipo_entidad <= entity_type legacy
        if (Schema::hasColumn('accounts', 'tipo_entidad') && Schema::hasColumn('accounts', 'entity_type')) {
            $mapping = [
                'empresa privada'            => 'empresa_privada',
                'empresa_privada'            => 'empresa_privada',
                'private'                    => 'empresa_privada',
                'aapp'                       => 'aapp',
                'administracion publica'     => 'aapp',
                'administración pública'     => 'aapp',
                'public'                     => 'aapp',
                'sin_animo_de_lucro'         => 'sin_animo_de_lucro',
                'sin ánimo de lucro'         => 'sin_animo_de_lucro',
                'corporacion_derecho_publico'=> 'corporacion_derecho_publico',
                'corporación de derecho público' => 'corporacion_derecho_publico',
                'particular'                 => 'particular',
            ];

            foreach ($mapping as $legacy => $canonical) {
                DB::table('accounts')
                    ->whereNull('tipo_entidad')
                    ->whereRaw('LOWER(entity_type) = ?', [$legacy])
                    ->update(['tipo_entidad' => $canonical]);
            }
        }
    }

    private function dropLegacyAccountColumns(array $columns): void
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn('accounts', $column)) {
                Schema::table('accounts', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }    
};