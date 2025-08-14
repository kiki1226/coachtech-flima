<?php

// "storage/..." → "uploads/..." へ正規化（publicディスク基準）
if (! function_exists('normalizePublicPath')) {
    function normalizePublicPath(?string $path): ?string
    {
        if (!$path) return null;
        return str_starts_with($path, 'storage/')
            ? substr($path, strlen('storage/')) // => "uploads/xxx.jpg"
            : $path;
    }
}

// 画像パスから公開URLを返す（なければデフォルト画像）
if (! function_exists('toPublicUrl')) {
    function toPublicUrl(?string $path, string $default = 'images/noimage.png'): string
    {
        // デフォルトURL
        $fallback = asset($default);

        if (!$path) return $fallback;

        $publicKey = normalizePublicPath($path);

        // storage/app/public にある場合
        if ($publicKey && \Illuminate\Support\Facades\Storage::disk('public')->exists($publicKey)) {
            return \Illuminate\Support\Facades\Storage::url($publicKey); // => "/storage/..."
        }

        // public直下にある場合（例：/public/uploads/...）
        if (file_exists(public_path($path))) {
            return asset($path); // => "/uploads/..."
        }

        // どちらでもなければデフォルト
        return $fallback;
    }
}
