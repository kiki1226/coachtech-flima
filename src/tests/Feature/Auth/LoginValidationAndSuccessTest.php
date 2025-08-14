<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginValidationAndSuccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ①-1: メールアドレスなし・パスワードなし → バリデーション
     */
    public function test_validation_errors_when_email_and_password_are_missing(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * ①-2: 入力情報が間違っている（存在するメール × 誤ったパスワード）→ バリデーション
     */
    public function test_validation_error_when_credentials_are_wrong(): void
    {
        User::factory()->create([
            'name'     => 'テストユーザー', 
            'email'    => 'wrong-case@example.com', 
            'password' => Hash::make('password'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email'    => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        // 標準動作：email フィールドにエラーが付与される
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * ②: 正しい情報が入力された場合 → ログイン成功 & リダイレクト
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'name'     => 'テストユーザー',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email'    => $user->email,   
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('products.index'));
    }
}
