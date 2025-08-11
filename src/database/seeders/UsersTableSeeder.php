<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seller = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'テストユーザー',
                'password' => bcrypt('password'),
                'avatar' => 'uploads/avatars/no-image.png',
                'email_verified_at' => now(),
                'is_profile_set' => true,
            ]
        );

        // 他にもダミーユーザーを作りたい場合
        User::factory()->count(5)->create();
    }
}
