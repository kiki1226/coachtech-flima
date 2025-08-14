<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * 商品一覧を表示
     */
    public function index(Request $request)
    {
        $keyword   = $request->input('keyword');
        $onlyLiked = $request->boolean('mylist');

        // 未認証でマイリスト要求なら空
        if ($onlyLiked && ! auth()->check()) {
            return view('products.index', [
                'products'  => collect(),
                'onlyLiked' => true,
                'keyword'   => $keyword,
            ]);
        }

        // 自分の商品は除外
        $excludeUserId = auth()->id(); // 未認証なら null
        $baseQuery = Product::query();
        if ($excludeUserId) {
            $baseQuery->where('user_id', '!=', $excludeUserId);
        }

        if ($onlyLiked) {
            // ✅ いいねIDに絞り、さらにキーワードでも絞る（積集合）
            $likedIds = auth()->user()->likes()->pluck('product_id');

            $query = (clone $baseQuery)->whereIn('id', $likedIds);
            if ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            }
            $products = $query->get();

        } else {
            // 通常タブ：必要ならキーワードだけ適用
            $query = (clone $baseQuery);
            if ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            }
            $products = $query->get();
        }

        return view('products.index', [
            'products'  => $products,
            'onlyLiked' => $onlyLiked,
            'keyword'   => $keyword,
        ]);
    }

    /**
     * 商品登録画面
     */
    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    /**
     * 商品詳細
     */
    public function show($item_id)
    {
        $product = \App\Models\Product::with([
                'categories',
                'comments.user',
                'productImages',
            ])
            ->withCount(['likes', 'comments'])   // ★ これを追加
            ->findOrFail($item_id);

        $liked = auth()->check()
            ? auth()->user()->likes()->where('product_id', $product->id)->exists()
            : false;

        return view('products.show', compact('product', 'liked'));
    }

    
    /**
     * 商品を保存
     */
    public function store(Request $request)
    {

        // ExhibitionRequestのルールをここで使う
        $form = new \App\Http\Requests\ExhibitionRequest();

        $validator = Validator::make(
            $request->all(),
            $form->rules(),
            method_exists($form, 'messages') ? $form->messages() : [],
            method_exists($form, 'attributes') ? $form->attributes() : []
        );

        if ($validator->fails()) {
            return redirect()->route('products.create')->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $images = $request->file('images', []);

        try {
            DB::transaction(function () use ($validated, $images) {
                $product = new Product();
                $product->user_id     = auth()->id();
                $product->name        = $validated['name'];
                $product->description = $validated['description'];
                $product->price       = $validated['price'];
                $product->condition   = $validated['condition'] ?? null;
                $product->brand       = $validated['brand'] ?? null;
                $product->image_path  = null;
                $product->save();

                // 画像保存（1枚目をメイン）
                foreach ($images as $i => $image) {
                    $path = $image->store('uploads/products', 'public'); // "uploads/..."
                    $publicPath = 'storage/'.$path; // 既存の表示運用に合わせる

                    if ($i === 0) {
                        $product->image_path = $publicPath;
                        $product->save();
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $publicPath,
                    ]);
                }

                // カテゴリー
                if (!empty($validated['category_ids'])) {
                    $product->categories()->attach($validated['category_ids']);
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Product store failed: '.$e->getMessage());
            return redirect()->route('products.create')
                ->withInput()
                ->withErrors('出品に失敗しました。もう一度お試しください。');
        }

        // ✅ 成功時は必ずマイページ（出品タブ）へ戻す
        return redirect('/mypage?tab=sell')->with('success', '商品を出品しました');
    }


    /**
     * 商品更新
     */
    public function update(ExhibitionRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->fill($request->only(['name','brand','description','price','features','condition']));
        $product->save();

        // 画像保存（新規がある場合）
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $idx => $image) {
                if (!$image->isValid()) continue;

                $path = $image->store('uploads/products', 'public'); // 'uploads/...'

                if ($idx === 0) {
                    $product->image_path = $path; // 代表も統一
                    $product->save();
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                ]);
            }
        }

        // カテゴリ更新
        if ($request->filled('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        // （削除分岐は別アクションに分けた方が安全）

        return redirect()->route('mypage.index')->with('success', '商品を更新しました');
    }

    /**
     * 商品編集画面
     */
    public function edit($id)
    {
        $product = Product::with('productImages')->findOrFail($id);
        $categories = Category::all();

        return view('products.edit', compact('product', 'categories'));
    }


    /**
     * 商品を購入
     */
    public function purchase($id)
    {
        $product = Product::findOrFail($id);

        if ($product->is_sold) {
            return redirect()->route('products.index')
                            ->with('error', 'この商品はすでに売り切れています');
        }

        $user = Auth::user();
        return view('products.purchase', compact('product', 'user'));
    }

    public function confirm(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'card_number' => 'nullable|string',
        ]);

        $product = Product::findOrFail($id);
        $user = Auth::user();
        $payment_method = $request->payment_method;

        // カード番号の末尾だけ表示
        $card_number = $request->card_number;
        $masked_card = $card_number ? '**** **** **** ' . substr($card_number, -4) : null;

        return view('products.confirm', [
            'product' => $product,
            'user' => $user,
            'payment_method' => $payment_method,
            'masked_card' => $masked_card,
        ]);
    }
    // 購入した商品をマイページに表示
    public function complete($id)
    {
        $product = Product::findOrFail($id);

        if ($product->is_sold) {
            return redirect()->route('products.index')->with('error', 'すでに売り切れています');
        }

        $product->is_sold = true;
        $product->buyer_id = auth()->id(); // ← これを追加
        $product->save();

        return view('products.complete');
    }

        /**
     * 商品を削除
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // もし画像などがあれば削除処理も
        if ($product->image_path && file_exists(public_path($product->image_path))) {
            unlink(public_path($product->image_path));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', '商品を削除しました');
    }
}
