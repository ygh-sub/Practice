.PHONY: start stop restart install setup help

# デフォルトターゲット
help:
	@echo "使用可能なコマンド:"
	@echo "  make start    - フロントエンド・バックエンドを起動"
	@echo "  make stop     - すべてのサーバーを停止"
	@echo "  make restart  - サーバーを再起動"
	@echo "  make install  - 依存関係をインストール"
	@echo "  make setup    - 初期セットアップ"

# フロントエンドとバックエンドを同時に起動
start:
	@echo "🚀 サーバーを起動中..."
	@make stop > /dev/null 2>&1 || true
	@cd apps/kcal-manager/backend && php artisan serve &
	@cd apps/kcal-manager/frontend && npm run dev &
	@echo "✅ Laravel: http://localhost:8000"
	@echo "✅ React: http://localhost:5173"
	@echo "停止するには: make stop"

# すべてのサーバーを停止（ポート開放）
stop:
	@echo "🛑 サーバーを停止中..."
	@lsof -ti:8000 | xargs kill -9 2>/dev/null || true
	@lsof -ti:5173 | xargs kill -9 2>/dev/null || true
	@lsof -ti:5174 | xargs kill -9 2>/dev/null || true
	@echo "✅ すべてのポートを開放しました"

# サーバーを再起動
restart:
	@make stop
	@sleep 2
	@make start

# 依存関係のインストール
install:
	@echo "📦 依存関係をインストール中..."
	@cd apps/kcal-manager/backend && composer install
	@cd apps/kcal-manager/frontend && npm install
	@echo "✅ インストール完了"

# 初期セットアップ
setup:
	@echo "🔧 初期セットアップ中..."
	@cd apps/kcal-manager/backend && cp .env.example .env 2>/dev/null || true
	@cd apps/kcal-manager/backend && php artisan key:generate
	@cd apps/kcal-manager/backend && touch database/database.sqlite
	@cd apps/kcal-manager/backend && php artisan migrate
	@cd apps/kcal-manager/backend && php artisan db:seed
	@make install
	@echo "✅ セットアップ完了"

# Laravel専用コマンド
backend-start:
	@cd apps/kcal-manager/backend && php artisan serve

# React専用コマンド
frontend-start:
	@cd apps/kcal-manager/frontend && npm run dev

# キャッシュクリア
clear:
	@cd apps/kcal-manager/backend && php artisan config:clear
	@cd apps/kcal-manager/backend && php artisan cache:clear
	@cd apps/kcal-manager/backend && php artisan route:clear
	@echo "✅ キャッシュをクリアしました"

# データベースリセット
db-reset:
	@cd apps/kcal-manager/backend && php artisan migrate:fresh --seed
	@echo "✅ データベースをリセットしました"

# ログ確認
logs:
	@tail -f apps/kcal-manager/backend/storage/logs/laravel.log

# テスト実行
test:
	@cd apps/kcal-manager/backend && php artisan test
	@cd apps/kcal-manager/frontend && npm test

# ビルド
build:
	@cd apps/kcal-manager/frontend && npm run build
	@echo "✅ ビルド完了"