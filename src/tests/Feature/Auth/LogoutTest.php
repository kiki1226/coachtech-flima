<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_logout(): void
    {
        // 認証済みユーザーを作成
        $user = User::factory()->create();

        // ログイン状態にする
        $this->actingAs($user);

        // ログアウトリクエスト送信
        $response = $this->post(route('logout'));

        // 認証状態が解除されていることを確認
        $this->assertGuest();

        // ログアウト後のリダイレクト先（必要に応じて変更）
        $response->assertRedirect(route('login'));
    }
}
