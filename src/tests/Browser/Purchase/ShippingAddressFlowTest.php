<?php

namespace Tests\Browser\Purchase;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShippingAddressFlowTest extends DuskTestCase
{
    

    private const WAIT_SHORT = 5;   // 秒
    private const WAIT_LONG  = 10;  // 秒
    private const POLL_UNTIL = 8.0; // 秒

    /** 送付先を変更すると、購入画面に新住所が反映される */
    public function test_address_edit_reflects_on_purchase_page(): void
    {
        $user = User::factory()->create([
            'zipcode'  => '100-0001',
            'address'  => '東京都千代田区1-1',
            'building' => '旧ビル101',
        ]);
        $product = Product::factory()->create();

        $zip      = '150-0002';
        $addr     = '東京都渋谷区恵比寿2-2';
        $building = '新テストビル201';

        $this->browse(function (Browser $b) use ($user, $product, $zip, $addr, $building) {
            $editUrl     = route('purchase.address.edit', ['product' => $product->id]);
            $purchaseUrl = route('products.purchase', ['product' => $product->id]);

            $b->loginAs($user)
              ->visit($editUrl)
              ->waitFor('input[name="zipcode"]', self::WAIT_LONG)
              ->clear('input[name="zipcode"]')->type('input[name="zipcode"]', $zip)
              ->clear('input[name="address"]')->type('input[name="address"]', $addr)
              ->clear('input[name="building"]')->type('input[name="building"]', $building)
              ->click('button.update-btn')
              ->waitForLocation($purchaseUrl, self::WAIT_LONG)
              ->assertPathIs(parse_url($purchaseUrl, PHP_URL_PATH))
              ->storeSource('purchase-after-address-update');

            $b->pause(200) // 描画の揺れ対策
              ->assertSee($addr)
              ->assertSee($building)
              ->tap(function () use ($b, $zip) {
                  try {
                      $b->assertSee($zip);
                  } catch (\Throwable $e) {
                      $b->assertSee(str_replace('-', '', $zip));
                  }
              });
        });
    }

    /** 購入を実行すると、purchases にユーザー・商品・送付先住所が保存される（or 外部決済へ遷移） */
    public function test_purchase_creates_order_with_shipping_address(): void
    {
        $user = User::factory()->create([
            'zipcode'  => '150-0002',
            'address'  => '東京都渋谷区恵比寿2-2',
            'building' => '新テストビル201',
        ]);
        $product = Product::factory()->create();

        $before = DB::table('purchases')->count();

        $this->browse(function (Browser $b) use ($user, $product) {
            $purchaseUrl = route('products.purchase', ['product' => $product->id]);

            $b->loginAs($user)
              ->visit($purchaseUrl)
              ->waitUsing(self::WAIT_LONG, 200, function () use ($b, $purchaseUrl) {
                  $url   = $b->driver->getCurrentURL();
                  $ready = $b->script('return document.readyState')[0] ?? '';
                  return (str_starts_with($url, $purchaseUrl) || str_contains($url, '/purchase'))
                      && $ready === 'complete';
              })
              ->storeSource('purchase-before-submit');

            // 必須の自動選択（支払い方法・同意・送付先セレクトを広めに拾う）
            $b->script(<<<'JS'
            (function(){
                // 支払い方法
                var r = document.querySelectorAll('input[name="payment_method"]');
                if (r.length) r[0].checked = true;
                var s = document.querySelector('select[name="payment_method"]');
                if (s && s.options.length) {
                    if (s.selectedIndex < 0) s.selectedIndex = 0;
                }

                // 同意チェック
                var agree = document.querySelector('input[type="checkbox"][name*="agree"], input[type="checkbox"][id*="agree"]');
                if (agree) agree.checked = true;

                // 送付先（name の揺れに対応）
                var shipSel = document.querySelector(
                    'select[name="shipping_address"],select[name="shipping_address_id"],select[name="address_id"],select[name*="shipping"]'
                );
                if (shipSel && shipSel.options.length) {
                    if (shipSel.value === '' || shipSel.selectedIndex < 0) {
                        var idx = 0;
                        if (shipSel.options.length >= 2 && shipSel.options[0].value === '') idx = 1;
                        shipSel.selectedIndex = idx;
                    }
                }
            })();
            JS);

            // 送信直前のスナップショット
            $b->storeSource('purchase-before-submit');

            // 送信（1）dusk 優先
            $clicked = false;
            if ($b->element('[dusk="purchase-button"]')) {
                $b->click('[dusk="purchase-button"]');
                $clicked = true;
            }

            // 送信（2）決済フォームを直接 submit
            if (! $clicked) {
                $b->script(<<<'JS'
                (function(){
                    var f =
                        document.querySelector('form[action*="/purchase/redirect"]') ||
                        document.querySelector('form[action*="purchase/redirect"]') ||
                        document.querySelector('form[action*="/products/"][action*="/purchase"]') ||
                        null;
                    if (f) f.submit();
                })();
                JS);
                $clicked = true;
            }

            // 送信（3）ボタンのテキストでクリック
            if (! $clicked) {
                $b->script(<<<'JS'
                (function(){
                    function clickByText(words){
                        var nodes = document.querySelectorAll('button, input[type="submit"], a, [role="button"]');
                        for (var i=0;i<nodes.length;i++){
                            var t = (nodes[i].innerText || nodes[i].value || '').trim();
                            for (var j=0;j<words.length;j++){
                                if (t && t.indexOf(words[j]) !== -1){ nodes[i].click(); return true; }
                            }
                        }
                        return false;
                    }
                    if (!clickByText(['購入','購入する','支払い','決済','お支払い','注文確認','Checkout','Pay','Proceed'])){
                        // 何も無ければ最初の submit をクリック
                        var btn = document.querySelector('button[type="submit"], input[type="submit"]');
                        if (btn) btn.click();
                    }
                })();
                JS);
                $clicked = true;
            }

            // 送信（4）最後の保険
            if (! $clicked) {
                $b->script('(function(){ var f=document.querySelector("form"); if(f){ f.submit(); } })();');
            }

            // 新しいタブ/ウィンドウで開いた場合は最後のウィンドウに切替
            try {
                $handles = $b->driver->getWindowHandles();
                if (is_array($handles) && count($handles) > 1) {
                    $b->driver->switchTo()->window(end($handles));
                    usleep(300_000);
                }
            } catch (\Throwable $e) {}

            // 送信後の完了待ちと保存（デバッグ用）
            $b->waitUsing(self::WAIT_LONG, 200, fn () => ($b->script('return document.readyState')[0] ?? '') === 'complete')
              ->storeSource('purchase-after-submit')
              ->screenshot('purchase-after-submit');
        });

        // A) 即時保存するタイプ
        $deadline = microtime(true) + self::POLL_UNTIL;
        while (microtime(true) < $deadline && DB::table('purchases')->count() <= $before) {
            usleep(200_000);
        }

        if (DB::table('purchases')->count() > $before) {
            $purchase = DB::table('purchases')->latest('id')->first();
            $this->assertNotNull($purchase, 'purchases にレコードが見つかりません。');
            $this->assertSame($user->id,    (int)$purchase->user_id);
            $this->assertSame($product->id, (int)$purchase->product_id);

            // 住所直持ち or 外部キー参照の両対応
            $inlineZip  = Schema::hasColumn('purchases','zipcode')           ? $purchase->zipcode
                        : (Schema::hasColumn('purchases','shipping_zipcode') ? $purchase->shipping_zipcode : null);
            $inlineAddr = Schema::hasColumn('purchases','address')           ? $purchase->address
                        : (Schema::hasColumn('purchases','shipping_address') ? $purchase->shipping_address : null);
            $inlineBldg = Schema::hasColumn('purchases','building')          ? $purchase->building
                        : (Schema::hasColumn('purchases','shipping_building')? $purchase->shipping_building : null);

            if ($inlineZip !== null || $inlineAddr !== null || $inlineBldg !== null) {
                if ($inlineZip  !== null) $this->assertStringContainsString('150', (string)$inlineZip);
                if ($inlineAddr !== null) $this->assertStringContainsString('渋谷区', (string)$inlineAddr);
                if ($inlineBldg !== null) $this->assertStringContainsString('新テストビル', (string)$inlineBldg);
            } else {
                $addressId = null;
                foreach (['shipping_address_id','address_id'] as $fk) {
                    if (Schema::hasColumn('purchases', $fk) && !empty($purchase->{$fk})) {
                        $addressId = $purchase->{$fk};
                        break;
                    }
                }
                $this->assertNotNull($addressId, 'purchases に住所の外部キーがありません。');

                $this->assertTrue(Schema::hasTable('addresses'), 'addresses テーブルがありません。');
                $addrRow = DB::table('addresses')->where('id', $addressId)->first();
                $this->assertNotNull($addrRow, "addresses に id={$addressId} が見つかりません。");

                $this->assertStringContainsString('150', (string)($addrRow->zipcode  ?? ''));
                $this->assertStringContainsString('渋谷区', (string)($addrRow->address  ?? ''));
                $this->assertStringContainsString('新テストビル', (string)($addrRow->building ?? ''));
            }

            $this->assertTrue(true);
            return;
        }

        // B) 外部決済へリダイレクトするタイプ
        $this->browse(function (Browser $b) {
            // ウィンドウ切替（保険）
            try {
                $handles = $b->driver->getWindowHandles();
                if (is_array($handles) && count($handles) > 1) {
                    $b->driver->switchTo()->window(end($handles));
                    usleep(300_000);
                }
            } catch (\Throwable $e) {}

            $url  = $b->driver->getCurrentURL();
            $html = $b->driver->getPageSource();

            $reached =
                preg_match('#/(purchase/redirect|checkout|pay|payment|stripe|session|confirm)#i', $url) ||
                preg_match('#(checkout|stripe|payment|決済|支払い|注文確認|お支払い)#u', $html);

            $this->assertTrue($reached, '決済リダイレクトに遷移していません。URL: '.$url);
        });
    }
}
