<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'primary' => $this->faker->boolean(25),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower($firstName.'.'.$lastName).'@'.$this->faker->freeEmailDomain(),
            'mobile' => $this->faker->optional()->phoneNumber(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'extension' => $this->faker->optional()->numerify('###'),
            'job_title' => $this->faker->jobTitle(),
            'department' => $this->faker->optional()->randomElement(['Ventas','Marketing','Atención al cliente','Dirección']),
            'language' => $this->faker->randomElement(['es', 'en']),
            'timezone' => $this->faker->timezone(),
            'preferred_channel' => $this->faker->randomElement(['email','phone','mobile']),
            'decision_level' => $this->faker->randomElement(['user','influencer','decision_maker']),
            'status' => $this->faker->randomElement(['active','inactive']),
            'legal_basis' => $this->faker->randomElement(['consent','contract','legitimate_interest']),
            'consent_status' => $this->faker->randomElement(['granted','pending','revoked']),
            'consent_at' => $this->faker->optional()->dateTimeBetween('-1 years'),
            'consent_source' => $this->faker->optional()->randomElement(['webform','email','phone']),
            'consent_ip' => $this->faker->optional()->ipv4(),
        ];
    }
}