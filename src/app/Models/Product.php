<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImage;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Category;
use App\Models\User;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'description',
        'price',
        'condition',
        'category_id',
        'image_path',
        'user_id',
        'is_sold',
    ];

    // 画像リレーション
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    // いいねリレーション
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // コメントリレーション
    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class);
    }

    // カテゴリリレーション
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    // sold表示
    public function getIsSoldAttribute()
    {
        return !is_null($this->buyer_id);
    }
    // ユーザリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // 画像のフルパスを取得
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::url($this->image_path); // /storage/products/xxx.jpg
        }
        return asset('images/noimage.png');
    }
}
