<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Fortify\Fortify;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_renders_and_has_expected_fields()
    {
        $res = $this->get('/login');

        $res->assertOk()
            ->assertSee('<form', false)
            ->assertSee('name="'.Fortify::username().'"', false) // email など
            ->assertSee('name="password"', false);
    }

    /** @test */
    public function login_fails_with_wrong_credentials_and_shows_error()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            // factory の既定で password は 'password'
            'email_verified_at' => now(),
            'is_profile_set'    => 1,
        ]);

        $res = $this->post('/login', [
            Fortify::username() => $user->email,
            'password'          => 'wrong-password',
        ]);

        // エラーは username フィールドキーで返る実装
        $res->assertSessionHasErrors([Fortify::username()]);
    }

    /** @test */
    public function login_success_redirects_to_products_index_if_verified_and_profile_set()
    {
        $user = User::factory()->create([
            'email' => 'ok@example.com',
            'email_verified_at' => now(),
            'is_profile_set'    => 1,
            // factory 既定の password は 'password'
        ]);

        $res = $this->post('/login', [
            Fortify::username() => $user->email,
            'password'          => 'password',
        ]);

        $res->assertRedirect(route('products.index'));
    }

    //** @test */
    public function login_redirects_to_verification_notice_if_not_verified()
    {
        // まず作成 → その後 forceFill で未認証に“確実に”上書き
        $user = User::factory()->create([
            'email' => 'need-verify@example.com',
            // パスワードは factory 既定で 'password'
        ]);

        // 未認証 + プロフィール済みを強制
        $user->forceFill([
            'email_verified_at' => null,
            'is_profile_set'    => 1,
        ])->save();

        $res = $this->post('/login', [
            Fortify::username() => $user->email,
            'password'          => 'password',
        ]);

        // 認証された上で、認証案内へ飛ぶことを保証
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(\Route::has('verification.notice'));
        $res->assertRedirect(route('verification.notice'));
    }

}
