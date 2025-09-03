## 練習用リポジトリ

新しい技術スタックや試したい内容がある場合はこのリポジトリでブランチを作成して行う
似たようなリポジトリが乱立することを防ぐため

亀井さん作成の divcontainer をベースにしているため ClaudeCode も使用可能、詳細は以下参照

---

# カロリー管理 Web アプリ

食事ごとのカロリーを記録・合計し、1日の摂取カロリーを簡単に把握できるWebアプリケーションです。

## 機能

- ✅ 食事内容とカロリーの入力
- ✅ 入力データの一覧表示
- ✅ 1日の合計カロリー自動計算
- ✅ データ永続化（データベース保存）
- ✅ レスポンシブデザイン（PC/スマホ対応）

## 技術スタック

### バックエンド
- **Laravel** - REST API
- **SQLite** - データベース
- **PHPUnit** - テストフレームワーク

### フロントエンド
- **React + Vite** - UIフレームワーク
- **Tailwind CSS** - スタイリング
- **Axios** - HTTP クライアント
- **Vitest** - テストフレームワーク

## セットアップ手順

### 前提条件

- PHP 8.2以上
- Composer
- Node.js 18以上
- npm

### バックエンド（Laravel）のセットアップ

1. バックエンドディレクトリへ移動
```bash
cd kcal-backend
```

2. 依存関係のインストール
```bash
composer install
```

3. 環境設定ファイルのコピー
```bash
cp .env.example .env
```

4. アプリケーションキーの生成
```bash
php artisan key:generate
```

5. データベースのマイグレーション実行
```bash
php artisan migrate
```

6. 開発サーバーの起動
```bash
php artisan serve
```

サーバーは `http://localhost:8000` で起動します。

### フロントエンド（React）のセットアップ

1. 新しいターミナルを開き、フロントエンドディレクトリへ移動
```bash
cd kcal-frontend
```

2. 依存関係のインストール
```bash
npm install
```

3. 開発サーバーの起動
```bash
npm run dev
```

アプリケーションは `http://localhost:5173` で起動します。

## テストの実行

### バックエンドのテスト
```bash
cd kcal-backend
php artisan test
```

### フロントエンドのテスト
```bash
cd kcal-frontend
npm test
```

## API エンドポイント

| メソッド | エンドポイント | 説明 |
|---------|--------------|------|
| GET | `/api/meals` | 全ての食事記録を取得 |
| GET | `/api/meals?date=YYYY-MM-DD` | 特定日付の食事記録を取得 |
| POST | `/api/meals` | 新しい食事記録を作成 |
| PUT | `/api/meals/{id}` | 食事記録を更新 |
| DELETE | `/api/meals/{id}` | 食事記録を削除 |

### リクエストボディの例（POST/PUT）
```json
{
  "name": "朝食",
  "calories": 500,
  "date": "2024-01-01"
}
```

## プロジェクト構成

```
Practice/
├── kcal-backend/         # Laravel バックエンド
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   └── MealController.php
│   │   └── Models/
│   │       └── Meal.php
│   ├── database/
│   │   └── migrations/
│   ├── routes/
│   │   └── api.php
│   └── tests/
│       ├── Feature/
│       │   └── MealApiTest.php
│       └── Unit/
│           └── MealTest.php
│
└── kcal-frontend/        # React フロントエンド
    ├── src/
    │   ├── api/
    │   │   └── mealApi.js
    │   ├── components/
    │   │   ├── MealForm.jsx
    │   │   ├── MealList.jsx
    │   │   └── CaloriesSummary.jsx
    │   └── App.jsx
    └── tests/
        └── (各コンポーネントのテストファイル)
```

## 開発アプローチ

このプロジェクトはテスト駆動開発（TDD）で実装されています：

1. **テスト作成** - 各機能に対してテストを先に作成
2. **実装** - テストを通すための最小限のコード実装
3. **リファクタリング** - コードの品質向上
4. **テスト再実行** - 全てのテストが通ることを確認

## 今後の拡張アイデア

- 栄養素（タンパク質・脂質・炭水化物）の記録機能
- グラフ表示機能（日別・週別・月別）
- ユーザー認証機能
- 食品データベースAPIとの連携
- カロリー目標設定機能

---

## Devcontainer Boilerplate

Claude Code 等コーディングエージェントが環境を破壊しても問題ないように、Devcontainer を使って開発環境を構築するためのボイラープレートです。

Zsh や rg, fd などモダンな開発環境ツールは一通りインストールされています。

言語ランタイムについては、post-create.sh 内の`mise use --global`を拡張するかたちで適宜追加してください。
uv, Node.js, Bun は Claude Code や ccusage のために導入済みです。
Python 3 ランタイムについては uv があるため意図的にボイラープレートではインストールしていません。
必要に応じて追加してください。

vimrc は好みで変更してください。
