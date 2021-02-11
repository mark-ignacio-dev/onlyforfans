<?php

namespace Database\Factories;

use App\Vault;
use Illuminate\Database\Eloquent\Factories\Factory;

class VaultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vault::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->catchPhrase,
        ];
    }
}
