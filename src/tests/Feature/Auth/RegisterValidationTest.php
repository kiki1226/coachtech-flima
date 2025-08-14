<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_requires_name_email_password_with_confirmation()
    {
        if (!\Route::has('register')) {
            $this->markTestSkipped('register ルートが無効化されています。');
        }

        $res = $this->post(route('register'), []); // 空で送る

        $res->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function register_rejects_invalid_email_and_short_password()
    {
        if (!\Route::has('register')) {
            $this->markTestSkipped('register ルートが無効化されています。');
        }

        $payload = [
            'name'                  => 'テスト太郎',
            'email'                 => 'not-an-email',
            'password'              => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $res = $this->post(route('register'), $payload);

        $res->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function register_success_creates_user_and_redirects_to_verification_notice()
    {
        if (!\Route::has('register')) {
            $this->markTestSkipped('register ルートが無効化されています。');
        }

        $email = 'newuser@example.com';
        $payload = [
            'name'                  => '新規ユーザー',
            'email'                 => $email,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        $res = $this->post(route('register'), $payload);

        // ユーザー作成
        $this->assertDatabaseHas('users', ['email' => $email]);

        // Fortifyの既定挙動: メール認証ページへ
        if (\Route::has('verification.notice')) {
            $res->assertRedirect(route('verification.notice'));
        } else {
            $res->assertRedirect(); // 少なくともリダイレクトはしている
        }
    }
}

