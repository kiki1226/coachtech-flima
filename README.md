# coachtech-flema

## 環境構築
### Dockerビルド
    GitHab      : git clone git@github.com:kiki1226/coachtech-flema.git
    起動          : docker-compose up -d
    php             : docker-compose exec php bash
    停止            : docker compose down
    
### Laravel環境構築
    インストール    :  php composer install
    APP_KEY 生成    :  php artisan key:generate
    ストレージ公開    :  php artisan storage:link
    .env 用意       :cp .env.example .env 
    マイグレーション  : php artisan migrate
    シーディング     :  php artisan migrate --seed

### テストコード
    Feature / Unit テスト（PHPUnit）: php artisan test
    Feature一部指定                 : php artisan test --filter=*****
    Dusk                          : php artisan dusk
    Dusk一部指定                    : php artisan dusk --filter=*****

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
%% カスタムテーマ（GitHubでも有効）
%% - theme: base をベースに色やフォントを上書き
%% - ダーク/ライト両方で見やすい配色
%% 必要ならカラーコードは好きな色に変えてOK
%% 参考: https://mermaid.js.org/config/theming.html#theme-variables
%%{init: {
  "theme": "base",
  "themeVariables": {
    "fontFamily": "ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Noto Sans, Helvetica Neue, Arial, 'Apple Color Emoji', 'Segoe UI Emoji'",
    "primaryColor":   "#F0F7FF",   /* エンティティ背景 */
    "primaryBorderColor": "#4B9EFF",/* 枠線色 */
    "primaryTextColor":   "#0B306B",/* 表タイトル文字 */
    "secondaryColor": "#FFFFFF",    /* セル背景 */
    "tertiaryColor":  "#E8F2FF",    /* タイトル帯背景 */
    "lineColor":      "#6A8CA6",    /* リレーション線 */
    "edgeLabelBackground":"#ffffff",/* 関係ラベルの背景 */
    "nodeBorder":     "#4B9EFF"
  }
}}%%

erDiagram
  USERS ||--o{ PRODUCTS : "owns"
  USERS ||--o{ COMMENTS : "writes"
  USERS ||--o{ LIKES : "likes"
  USERS ||--o{ ADDRESSES : "has"

  PRODUCTS ||--o{ PRODUCT_IMAGES : "has"
  PRODUCTS ||--o{ COMMENTS : "has"
  PRODUCTS ||--o{ LIKES : "has"
  PRODUCTS }o--o{ CATEGORIES : "tagged"

  USERS {
    BIGINT   id PK
    VARCHAR  name
    VARCHAR  email
    VARCHAR  password
    VARCHAR  avatar
    BOOLEAN  is_profile_set
    VARCHAR  zipcode
    VARCHAR  address
    VARCHAR  building
    DATETIME email_verified_at
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PRODUCTS {
    BIGINT   id PK
    BIGINT   user_id FK
    VARCHAR  name
    INT      price
    TEXT     description
    VARCHAR  condition
    VARCHAR  image_path
    BIGINT   buyer_id
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  PRODUCT_IMAGES {
    BIGINT   id PK
    BIGINT   product_id FK
    VARCHAR  image_path
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  CATEGORIES {
    BIGINT   id PK
    VARCHAR  name
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  COMMENTS {
    BIGINT   id PK
    BIGINT   user_id FK
    BIGINT   product_id FK
    TEXT     body
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  LIKES {
    BIGINT   id PK
    BIGINT   user_id FK
    BIGINT   product_id FK
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }

  ADDRESSES {
    BIGINT   id PK
    BIGINT   user_id FK
    VARCHAR  zipcode
    VARCHAR  address
    VARCHAR  building
    TIMESTAMP created_at
    TIMESTAMP updated_at
  }
```

