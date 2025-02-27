<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserFactory extends Factory
{
    protected static ?string $password;
    public function definition(): array
    {
        $imageFolder = storage_path('app/public/images/users/');

        if (!file_exists($imageFolder)) {
            mkdir($imageFolder, 0777, true);
        }

        $imageName = Str::random(15) . '.jpg';
        $imagePath = $imageFolder . $imageName;

        $randomImageUrl = 'https://picsum.photos/70/70';

        if (!file_exists($imagePath)) {
            file_put_contents($imagePath, file_get_contents($randomImageUrl));
        }

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password(),
            'phone' =>  random_int(600000000, 999999999),
            'position_id' => $this->faker->numberBetween(1, 4),
            'photo' => 'images/users/' . $imageName,
        ];
    }

}
