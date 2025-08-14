<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(TestCase::class, RefreshDatabase::class);

it('パスワード確認が一致しないとバリデーションエラーになる', function () {
    if (!Route::has('register')) {
        $this->markTestSkipped('register ルートが無効です。');
    }

    $email = 'mismatch@example.com';
    $payload = [
        'name'                  => '不一致太郎',
        'email'                 => $email,
        'password'              => 'password123',
        'password_confirmation' => 'different', // ← 故意に不一致
    ];

    $res = $this->post(route('register'), $payload);

    // confirmation ルールで password エラー
    $res->assertSessionHasErrors(['password']);

    // ユーザーは作成されていない
    $this->assertDatabaseMissing('users', ['email' => $email]);
});
