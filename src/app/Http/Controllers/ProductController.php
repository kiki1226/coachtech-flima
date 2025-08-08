<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\ProductImage;
use App\Http\Requests\ExhibitionRequest;

class ProductController extends Controller
{
    /**
     * 商品一覧を表示
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $onlyLiked = $request->has('mylist') && auth()->check();

        // ログイン中なら自分の商品は除く
        $excludeUserId = auth()->check() ? auth()->id() : null;

        // ベースクエリ（自分の商品を除外）
        $baseQuery = Product::query();
        if ($excludeUserId) {
            $baseQuery->where('user_id', '!=', $excludeUserId);
        }

        // 検索結果を取得（キーワードがある場合）
        $searchResults = collect();
        if ($keyword) {
            $searchResults = (clone $baseQuery)
                ->where('name', 'like', '%' . $keyword . '%')
                ->get();
        }

        // いいね済み商品を取得（ログインしている場合）
        $likedProducts = collect();
        if (auth()->check()) {
            $likedProductIds = auth()->user()->likes()->pluck('product_id');
            $likedProducts = (clone $baseQuery)
                ->whereIn('id', $likedProductIds)
                ->get();
        }

        if ($onlyLiked) {
            // 検索結果 + いいね済み商品 をマージ（重複排除）
            $products = $searchResults->merge($likedProducts)->unique('id')->values();
        } else {
            // 通常のおすすめ一覧（キーワードがある場合はフィルタ）
            $query = Product::query();
            if ($excludeUserId) {
                $query->where('user_id', '!=', $excludeUserId);
            }
            if ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%');
            }
            $products = $query->get();
        }

        return view('products.index', [
            'products' => $products,
            'onlyLiked' => $onlyLiked,
            'keyword' => $keyword
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
        $product = Product::with(['categories', 'comments.user', 'productImages'])
            ->withCount(['likes', 'comments'])
            ->findOrFail($item_id);

        $liked = false;
        if (auth()->check()) {
            $liked = $product->likes()->where('user_id', auth()->id())->exists();
        }

        return view('products.show', compact('product', 'liked'));
    }
    
    /**
     * 商品を保存
     */
    public function store(ExhibitionRequest $request)
    {
        $validated = $request->validated();

        // 商品データ作成
        $product = new Product();
        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->description = $validated['description'];
        $product->condition = $validated['condition'];
        $product->user_id = Auth::id();

        // 画像処理
        $images = $request->file('images');
        $storedPaths = [];

        if ($images && count($images) > 0) {
            foreach ($images as $index => $image) {
                $path = $image->store('uploads/products', 'public');
                $fullPath = 'storage/' . $path;

                // 1枚目はメイン画像として保存
                if ($index === 0) {
                    $product->image_path = $fullPath;
                }

                $storedPaths[] = $fullPath;
            }
        } else {
            // 画像が未選択の場合、ダミー画像を設定
            $product->image_path = 'uploads/products/no-image.png';
        }

        $product->save();

        // カテゴリー紐付け
        if (isset($validated['category_ids'])) {
            $product->categories()->attach($validated['category_ids']);
        }

        // 複数画像保存（ProductImageテーブル）
        foreach ($storedPaths as $path) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
            ]);
        }

        return redirect()->route('mypage.index')->with('success', '商品を出品しました');
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
     * 商品更新
     */
    public function update(ExhibitionRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        // 更新
        $product->fill($request->only(['name', 'brand', 'description', 'price']));
        $product->save();

        // 画像保存（新規画像がある場合のみ）
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                if ($image->isValid()) {
                    $path = $image->store('uploads/products', 'public');
                    $imagePath = 'storage/' . $path;

                    if ($index === 0) {
                        $product->image_path = $imagePath;
                        $product->save();
                    }

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                    ]);
                }
            }
        }

        // カテゴリー更新（多対多）
        if ($request->filled('category_ids')) {
            $product->categories()->sync($request->category_ids);
        }

        return redirect()->route('mypage.index')->with('success', '商品を更新しました');
        
        if ($request->input('action') === 'delete') {
        // 削除処理
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品を削除しました');
        }
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
