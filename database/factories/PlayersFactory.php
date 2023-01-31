<?php

namespace Database\Factories;

use App\Models\Countries;
use App\Models\Languages;
use App\Models\Players;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PlayersFactory extends Factory
{

    protected $model = Players::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email' => 'tester-' . $this->faker->email(),
            'password' => Hash::make('tester22_AA'),
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'fullname' => $this->faker->name,
            'country_id' => Countries::get()->random()->id,
            'language_id' => Languages::get()->random()->id,
            'city' => $this->faker->city,
            'postal_code' => $this->faker->postcode,
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
