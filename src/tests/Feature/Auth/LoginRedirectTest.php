<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

it('未認証メールはverification.noticeへ', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
        'is_profile_set'    => true,
        'password'          => Hash::make('password123'),
    ]);

    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('verification.notice'));
});

it('プロフィール未設定はprofile.setupへ', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'is_profile_set'    => false,
        'password'          => Hash::make('password123'),
    ]);

    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('profile.setup'));
});

it('条件を満たせばproducts.indexへ', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'is_profile_set'    => true,
        'password'          => Hash::make('password123'),
    ]);

    $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ])->assertRedirect(route('products.index'));
});
