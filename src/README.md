# フリマアプリ
    coachtech-flema
## 環境構築
### Dockerビルド
    git clone git@github.com:kiki1226/coachtech-flema.git
    docker-compose up -d --build
    docker-compose exec php bash
### Laravel環境構築
    composer install
    cp .env.example .env 
    php artisan key:generate
    php artisan migrate
    php artisan db:seed

## URL（開発環境）
    トップページ：http://localhost/
    ユーザー登録：http://localhost/register
    phpMyAdmin：http://localhost:8080/

## 使用技術
    PHP 8.2.x
    Laravel 10.x
    MySQL 8.0.x
    nginx 1.22.x
    jQuery 3.7.x


## ER図
