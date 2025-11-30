<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Seller;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@gameboost.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@gameboost.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create buyer users
        $buyer1 = User::firstOrCreate(
            ['email' => 'buyer1@example.com'],
            [
                'name' => 'John Buyer',
                'email' => 'buyer1@example.com',
                'password' => Hash::make('password'),
                'role' => 'buyer',
            ]
        );

        $buyer2 = User::firstOrCreate(
            ['email' => 'buyer2@example.com'],
            [
                'name' => 'Jane Buyer',
                'email' => 'buyer2@example.com',
                'password' => Hash::make('password'),
                'role' => 'buyer',
            ]
        );

        // Create seller users with seller profiles
        $seller1 = User::firstOrCreate(
            ['email' => 'seller1@example.com'],
            [
                'name' => 'Ahmed Seller',
                'email' => 'seller1@example.com',
                'password' => Hash::make('password'),
                'role' => 'seller',
            ]
        );

        Seller::firstOrCreate(
            ['id' => $seller1->id],
            [
                'id' => $seller1->id,
                'rating' => 4.5,
                'total_sales' => 15,
                'bio' => 'Experienced seller with high-quality accounts. Verified seller.',
                'verified' => 1,
            ]
        );

        $seller2 = User::firstOrCreate(
            ['email' => 'seller2@example.com'],
            [
                'name' => 'Sarah Seller',
                'email' => 'seller2@example.com',
                'password' => Hash::make('password'),
                'role' => 'seller',
            ]
        );

        Seller::firstOrCreate(
            ['id' => $seller2->id],
            [
                'id' => $seller2->id,
                'rating' => 4.8,
                'total_sales' => 32,
                'bio' => 'Top-rated seller specializing in Mobile Legends accounts.',
                'verified' => 1,
            ]
        );

        $seller3 = User::firstOrCreate(
            ['email' => 'seller3@example.com'],
            [
                'name' => 'Mohamed Seller',
                'email' => 'seller3@example.com',
                'password' => Hash::make('password'),
                'role' => 'seller',
            ]
        );

        Seller::firstOrCreate(
            ['id' => $seller3->id],
            [
                'id' => $seller3->id,
                'rating' => 4.2,
                'total_sales' => 8,
                'bio' => 'New seller with great deals on gaming accounts.',
                'verified' => 0,
            ]
        );
    }
}
