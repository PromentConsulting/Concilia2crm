<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        $company = $this->faker->unique()->company();
        $domain = Str::slug($company, '');

        return [
            'name' => $company,
            'legal_name' => $company.' S.A.',
            'vat' => 'ES'.$this->faker->unique()->numerify('#########'),
            'domain' => strtolower($domain).'.com',
            'website' => 'https://'.$domain.'.com',
            'phone' => $this->faker->phoneNumber(),
            'email' => 'info@'.$domain.'.com',
            'status' => $this->faker->randomElement(['prospect', 'customer', 'inactive']),
            'size' => $this->faker->randomElement(['startup', 'sme', 'enterprise']),
            'source' => $this->faker->randomElement(['web', 'referral', 'event']),
            'risk_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'billing_street' => $this->faker->streetAddress(),
            'billing_postal_code' => $this->faker->postcode(),
            'billing_city' => $this->faker->city(),
            'billing_state' => $this->faker->state(),
            'billing_country_code' => strtoupper($this->faker->countryCode()),
            'payment_method' => $this->faker->randomElement(['transfer', 'card', 'direct_debit']),
            'payment_term_days' => $this->faker->randomElement([15, 30, 45, 60]),
            'iban' => 'ES'.$this->faker->numerify('####################'),
            'bic' => strtoupper($this->faker->lexify('?????????')),
            'public_administration' => $this->faker->boolean(10),
            'e_invoice_ready' => $this->faker->boolean(),
            'group_note' => $this->faker->sentence(),
            'id_contabilidad' => (string) $this->faker->numberBetween(1000, 9999),
            'system_origin' => $this->faker->randomElement(['import', 'manual']),
        ];
    }
}