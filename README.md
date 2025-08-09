# coachtech-flema

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
    メール確認：http://localhost:8025/


## 使用技術
    PHP 8.2.x
    Laravel 10.x
    MySQL 8.0.x
    nginx 1.22.x
    jQuery 3.7.x


## ER図
```mermaid
erDiagram
    USERS ||--o{ PRODUCTS : "出品"
    USERS ||--o{ COMMENTS : "コメント"
    USERS ||--o{ LIKES : "いいね"
    USERS ||--o{ PURCHASES : "購入"
    USERS ||--o{ ADDRESSES : "住所"

    PRODUCTS ||--o{ PRODUCT_IMAGES : "画像"
    PRODUCTS ||--o{ COMMENTS : "コメント"
    PRODUCTS ||--o{ LIKES : "いいね"
    PRODUCTS ||--o{ PURCHASES : "購入"
    PRODUCTS ||--o{ CATEGORY_PRODUCT : "カテゴリ紐付け"

    CATEGORIES ||--o{ CATEGORY_PRODUCT : ""
    CATEGORY_PRODUCT }o--|| PRODUCTS : ""

    USERS {
        bigint id PK
        string name
        string email
        string password
        string avatar
        string zipcode
        string address
        string building
        boolean is_profile_set
        timestamps
    }

    PRODUCTS {
        bigint id PK
        bigint user_id FK
        string name
        integer price
        string season
        text description
        string image_path
        timestamps
    }

    PRODUCT_IMAGES {
        bigint id PK
        bigint product_id FK
        string image_path
        timestamps
    }

    CATEGORIES {
        bigint id PK
        string name
        timestamps
    }

    CATEGORY_PRODUCT {
        bigint id PK
        bigint category_id FK
        bigint product_id FK
    }

    COMMENTS {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        text body
        timestamps
    }

    LIKES {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        timestamps
    }

    PURCHASES {
        bigint id PK
        bigint user_id FK
        bigint product_id FK
        string payment_method
        timestamps
    }

    ADDRESSES {
        bigint id PK
        bigint user_id FK
        string zipcode
        string address
        string building
        timestamps
    }
