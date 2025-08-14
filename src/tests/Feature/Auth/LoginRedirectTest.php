<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

uses(TestCase::class, RefreshDatabase::class);

it('未認証メールはverification.noticeへ', function () {
    $user = User::factory()->unverified()->create([
        'is_profile_set' => true,
        'password'       => Hash::make('password123'),
        'email'          => 'need-verify@example.com',
    ]);

    $res = $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $res->assertRedirect(route('verification.notice'));
});

it('プロフィール未設定はprofile.setupへ', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'password'          => Hash::make('password123'),
        'email'             => 'need-setup@example.com',
    ]);
    // プロフィール未設定を確実に
    $user->forceFill(['is_profile_set' => false])->save();

    $res = $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $res->assertRedirect(route('profile.setup'));
});

it('条件を満たせばproducts.indexへ', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'is_profile_set'    => true,
        'password'          => Hash::make('password123'),
        'email'             => 'ok@example.com',
    ]);

    $res = $this->post(route('login'), [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $res->assertRedirect(route('products.index'));
});
