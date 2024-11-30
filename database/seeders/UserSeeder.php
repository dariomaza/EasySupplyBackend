<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("users")->insert([
            [
                "firstName" => "John",
                "lastName" => "Doe",
                "email" => "john@example.com",
                "password" => bcrypt("password123"), // Recuerda hashear la contraseña
                "phone" => "123456789",
                "role" => "admin",
                "plan" => "premium"
            ],
            [
                "firstName" => "Jane",
                "lastName" => "Smith",
                "email" => "jane@example.com",
                "password" => bcrypt("password456"),
                "phone" => "987654321",
                "role" => "user",
                "plan" => "basic"
            ]
        ]);
    }
}
