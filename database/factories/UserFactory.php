<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'tendangnhap' => $this->faker->unique()->userName(),
            'matkhau' => md5('password'),
            'hoten' => $this->faker->name(),
            'trangthai' => 1,
        ];
    }
}