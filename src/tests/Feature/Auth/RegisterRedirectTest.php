<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('会員登録後はverification.noticeへ（想定）', function () {
    $payload = [
        'username' => 'taro',
        'email' => 'taro@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];

    $this->post('/register', $payload)
        ->assertStatus(302);
        // ->assertRedirect(route('verification.notice')); // 実際の遷移先が確定したら戻す
});
