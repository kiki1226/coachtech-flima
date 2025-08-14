<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(TestCase::class, RefreshDatabase::class); 

it('会員登録後は verification.notice へリダイレクトする', function () {
    if (!Route::has('register')) {
        $this->markTestSkipped('register ルートが無効です。');
    }

    $email = 'taro@example.com';
    $payload = [
        'name'                  => 'taro',        
        'email'                 => $email,
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ];

    $res = $this->post(route('register'), $payload);

    $this->assertDatabaseHas('users', ['email' => $email]);

    if (Route::has('verification.notice')) {
        $res->assertRedirect(route('verification.notice'));
    } else {
        $res->assertRedirect(route('login'));
    }
});

it('会員登録の必須入力が足りないとエラーになる', function () {
    if (!Route::has('register')) {
        $this->markTestSkipped('register ルートが無効です。');
    }

    $this->post(route('register'), [])
         ->assertSessionHasErrors(['name','email','password']);
});
