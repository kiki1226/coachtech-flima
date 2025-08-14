<?php

namespace Tests\Browser\User;

use App\Models\User;

use Illuminate\Support\Facades\File;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserInfoEditPrefillTest extends DuskTestCase
{
   

    private const BASE    = 'http://nginx';
    private const LOGIN   = '/login';
    private const MYPAGE  = '/mypage';
    private const WAIT    = 25; // 全体の最大粘り

    // 編集URL候補（順に試す）
    private const EDIT_URLS = [
        '/profile/edit',
        '/profile',                 // Breeze系
        '/users/profile/edit',
        '/users/edit',
        '/mypage/profile/edit',
    ];

    // マイページ上の「編集へ」リンク候補（あればクリック）
    private const MYPAGE_EDIT_LINKS = [
        'a[href="/profile/edit"]',
        'a[href="/profile"]',
        'a[href="/users/profile/edit"]',
        'a[href="/users/edit"]',
        'a[href="/mypage/profile/edit"]',
        'a[data-test="profile-edit"]',
        'button[data-test="profile-edit"]',
    ];

    // 入力候補（input/textarea/ID/data-test も見る）
    private const NAME_CANDIDATES = [
        'input[name="name"]', 'input[name="username"]', 'input[name="user_name"]',
        '#name', '#username', '[data-test="name"]',
    ];
    private const ZIP_CANDIDATES = [
        'input[name="zipcode"]', 'input[name="postal_code"]', 'input[name="post_code"]', 'input[name="zip"]',
        '#zipcode', '#postal_code', '[data-test="zipcode"]',
    ];
    private const ADDRESS_CANDIDATES = [
        'input[name="address"]', 'textarea[name="address"]', 'input[name="address1"]', 'textarea[name="address1"]',
        '#address', '[data-test="address"]',
    ];

    // 画像プレビュー候補
    private const AVATAR_CANDIDATES = [
        'img.avatar', 'img#avatar', 'img[data-test="avatar"]', 'img[src*="avatars"]', 'img[alt*="プロフィール"]',
    ];

    /** 例外を投げない“やさしい探索” : 見つかった最初のセレクタを返す / 見つからなければ null */
    private function findFirst(Browser $browser, array $selectors, int $seconds): ?string
    {
        $ticks = max(1, intval($seconds * 4)); // 250ms刻み
        for ($i = 0; $i < $ticks; $i++) {
            foreach ($selectors as $s) {
                if ($browser->element($s)) return $s;
            }
            $browser->pause(250);
        }
        return null;
    }

    public function test_edit_form_has_prefilled_values(): void
    {
        // 画像フィクスチャ（中身はダミーでもOK。src が通れば十分）
        $public = public_path('storage/avatars/prefill.jpg');
        if (!File::exists(dirname($public))) File::makeDirectory(dirname($public), 0777, true);
        if (!File::exists($public)) File::put($public, 'dummy');

        // 初期値ユーザー
        $user = User::factory()->create([
            'name'              => '初期 太郎',
            'email'             => 'init@example.com',
            'password'          => bcrypt('password'),
            'zipcode'           => '100-0001',
            'address'           => '東京都千代田区1-1',
            'avatar'            => 'storage/avatars/prefill.jpg',
            'is_profile_set'    => true,
            'email_verified_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            // 1) ログイン → マイページ
            $browser->visit(self::BASE . self::LOGIN)
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('ログイン')
                ->visit(self::BASE . self::MYPAGE)
                ->pause(500);

            // 2) マイページに編集リンクがあればクリック
            $clicked = false;
            foreach (self::MYPAGE_EDIT_LINKS as $linkSel) {
                if ($browser->element($linkSel)) { $browser->click($linkSel)->pause(300); $clicked = true; break; }
            }
            if (!$clicked) {
                try { $browser->clickLink('プロフィール編集'); $clicked = true; } catch (\Throwable $e) {}
            }

            // 3) URL候補を総当りし、フォーム/画像要素のいずれかが出るURLを見つける
            $editUrlUsed = null;
            $nameSel = $zipSel = $addrSel = $avatarSel = null;

            $deadline = microtime(true) + self::WAIT; // 全体の粘り時間
            foreach (self::EDIT_URLS as $path) {
                if (microtime(true) > $deadline) break;

                $browser->visit(self::BASE . $path)->pause(400);

                // まず画像 or name が見つかるか軽く探す
                $avatarSel = $this->findFirst($browser, self::AVATAR_CANDIDATES, 2);
                $nameSel   = $this->findFirst($browser, self::NAME_CANDIDATES,   2);

                if ($avatarSel || $nameSel) {
                    // 見つかったURLで残りも探す
                    $zipSel  = $this->findFirst($browser, self::ZIP_CANDIDATES,     2);
                    $addrSel = $this->findFirst($browser, self::ADDRESS_CANDIDATES, 2);
                    $editUrlUsed = $path;
                    break;
                }
            }

            // 何も見つからなければ落とす（URLも出す）
            $this->assertNotNull($editUrlUsed, '編集画面のURLを特定できませんでした。候補: '.implode(', ', self::EDIT_URLS));

            // 4) 画像プレフィル
            $this->assertNotNull($avatarSel, 'プロフィール画像プレビューが見つかりません（'.implode(', ', self::AVATAR_CANDIDATES).'）');
            $src = $browser->attribute($avatarSel, 'src');
            $this->assertNotEmpty($src, 'プロフィール画像の src が空です');
            $this->assertStringContainsString('storage/avatars/prefill.jpg', $src, '初期アバターが表示されていません');

            // 5) 入力の初期値
            $this->assertNotNull($nameSel,  'ユーザー名入力が見つかりません（'.implode(', ', self::NAME_CANDIDATES).'）');
            $this->assertSame('初期 太郎', $browser->value($nameSel));

            $this->assertNotNull($zipSel,   '郵便番号入力が見つかりません（'.implode(', ', self::ZIP_CANDIDATES).'）');
            $this->assertSame('100-0001',  $browser->value($zipSel));

            $this->assertNotNull($addrSel,  '住所入力が見つかりません（'.implode(', ', self::ADDRESS_CANDIDATES).'）');
            $this->assertSame('東京都千代田区1-1', $browser->value($addrSel));
        });
    }
}
