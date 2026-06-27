<?php

namespace Database\Factories;

use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingAddress>
 */
class ShippingAddressFactory extends Factory
{
    protected $model = ShippingAddress::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => 'Home',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->e164PhoneNumber(),
            'country' => fake()->country(),
            'state' => fake()->state(),
            'city' => fake()->city(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'postal_code' => fake()->postcode(),
            'is_default' => false,
        ];
    }
}
