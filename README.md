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
### テストコード
    docker compose exec php php artisan test

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

```markdown
## ER図
```mermaid
erDiagram
  USERS ||--o{ PRODUCTS : "owns"
  USERS ||--o{ COMMENTS : "writes"
  USERS ||--o{ LIKES : "likes"
  USERS ||--o{ ADDRESSES : "has"

  PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
  PRODUCTS ||--o{ COMMENTS : "has"
  PRODUCTS ||--o{ LIKES : "has"
  PRODUCTS }o--o{ CATEGORIES : "tagged"

```mermaid
erDiagram
  USERS ||--o{ PRODUCTS : "owns"
  USERS ||--o{ COMMENTS : "writes"
  USERS ||--o{ LIKES : "likes"
  USERS ||--o{ ADDRESSES : "has"

  PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
  PRODUCTS ||--o{ COMMENTS : "has"
  PRODUCTS ||--o{ LIKES : "has"
  PRODUCTS }o--o{ CATEGORIES : "tagged"

  %% テーブル定義
  USERS {
    BIGINT id PK
    VARCHAR name
    VARCHAR email
    VARCHAR password
    VARCHAR avatar
    BOOLEAN is_profile_set
    VARCHAR zipcode
    VARCHAR address
    VARCHAR building
    DATETIME email_verified_at
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PRODUCTS {
    BIGINT id PK
    BIGINT user_id FK
    VARCHAR name
    INT price
    TEXT description
    VARCHAR condition
    VARCHAR image_path
    BIGINT buyer_id
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PRODUCT_IMAGES {
    BIGINT id PK
    BIGINT product_id FK
    VARCHAR image_path
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  CATEGORIES {
    BIGINT id PK
    VARCHAR name
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  COMMENTS {
    BIGINT id PK
    BIGINT user_id FK
    BIGINT product_id FK
    TEXT body
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  LIKES {
    BIGINT id PK
    BIGINT user_id FK
    BIGINT product_id FK
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }


