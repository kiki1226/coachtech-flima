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

### キャッシュ(仮ナンバー)
    カード番号：4242 4242 4242 4242

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
  USERS {
    bigint id PK
    varchar name
    varchar email
    timestamp email_verified_at
    varchar password
    varchar avatar
    varchar zipcode
    varchar address
    varchar building
    tinyint  is_profile_set
    varchar remember_token
    timestamp created_at
    timestamp updated_at
  }

  CATEGORIES {
    bigint id PK
    varchar name
    timestamp created_at
    timestamp updated_at
  }

  PRODUCTS {
    bigint id PK
    bigint user_id FK  "seller"
    bigint category_id FK "※単一カテゴリ用(任意)"
    varchar name
    varchar brand
    int     price
    text    description
    varchar image_path
    text    features
    varchar condition
    tinyint is_sold
    bigint  buyer_id FK "※購入者(任意)"
    timestamp created_at
    timestamp updated_at
  }

  PRODUCT_IMAGES {
    bigint id PK
    bigint product_id FK
    varchar image_path
    smallint sort_order
    timestamp created_at
    timestamp updated_at
  }

  CATEGORY_PRODUCTS {
    bigint product_id FK
    bigint category_id FK
  }

  LIKES {
    bigint id PK
    bigint user_id FK
    bigint product_id FK
    timestamp created_at
    timestamp updated_at
  }

  COMMENTS {
    bigint id PK
    bigint user_id FK
    bigint product_id FK
    text   comment
    timestamp created_at
    timestamp updated_at
  }

  ADDRESSES {
    bigint id PK
    bigint user_id FK
    varchar recipinet_name  "※typo: recipient_name"
    varchar phone
    varchar zipcode
    varchar address
    varchar building
    tinyint is_default
    timestamp created_at
    timestamp updated_at
  }

  PURCHASES {
    bigint id PK
    bigint user_id FK         "buyer"
    bigint product_id FK
    bigint shipping_address_id FK
    varchar payment_method
    varchar status
    timestamp purchased_at
    timestamp created_at
    timestamp updated_at
  }

  %% 1対多
  USERS ||--o{ PRODUCTS         : sells
  USERS ||--o{ ADDRESSES        : has
  USERS ||--o{ LIKES            : likes
  USERS ||--o{ COMMENTS         : writes
  USERS ||--o{ PURCHASES        : buys

  PRODUCTS ||--o{ PRODUCT_IMAGES : has
  PRODUCTS ||--o{ LIKES          : gets
  PRODUCTS ||--o{ COMMENTS       : receives
  PRODUCTS ||--o{ PURCHASES      : sold_in

  %% 多対多（中間テーブル）
  PRODUCTS ||--o{ CATEGORY_PRODUCTS : ""
  CATEGORIES ||--o{ CATEGORY_PRODUCTS : ""

  %% 参照
  PURCHASES }o--|| ADDRESSES : ships_to
  PRODUCTS  }o--|| USERS     : buyer_via_buyer_id
  PRODUCTS  }o--|| CATEGORIES: single_category_via_category_id

```

