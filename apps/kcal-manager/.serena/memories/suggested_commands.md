# Suggested Commands for Kcal Manager Development

## Backend (Laravel) Commands

### Development
```bash
# Start development server (includes server, queue, logs, and vite)
cd backend && composer run dev

# Start Laravel server only
php artisan serve

# Run database migrations
php artisan migrate

# Seed database with demo data
php artisan db:seed --class=DemoUserSeeder

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Testing
```bash
# Run all tests
cd backend && composer test

# Run specific test file
php artisan test tests/Feature/MealApiTest.php

# Run with coverage
php artisan test --coverage
```

### Code Quality
```bash
# Format code with Laravel Pint
cd backend && ./vendor/bin/pint

# Check code without fixing
cd backend && ./vendor/bin/pint --test
```

## Frontend (React) Commands

### Development
```bash
# Start development server
cd frontend && npm run dev

# Build for production
cd frontend && npm run build

# Preview production build
cd frontend && npm run preview
```

### Testing
```bash
# Run tests
cd frontend && npm test

# Run tests in watch mode
cd frontend && npm test -- --watch
```

### Code Quality
```bash
# Run ESLint
cd frontend && npm run lint

# Fix ESLint issues
cd frontend && npm run lint -- --fix
```

## System Commands (macOS/Darwin)

### Git
```bash
# Check status
git status

# View diff
git diff

# Commit changes
git commit -m "message"

# View log
git log --oneline -10
```

### File Navigation
```bash
# List files
ls -la

# Find files
find . -name "*.php"

# Search in files (using ripgrep)
rg "pattern" --type php
```

## Full Stack Development
```bash
# Start both frontend and backend simultaneously
# Terminal 1:
cd backend && composer run dev

# Terminal 2:
cd frontend && npm run dev
```

## API Endpoints
- Base URL: `http://localhost:8000/api`
- Auth endpoints: `/login`, `/register`, `/logout`
- Meal endpoints: `/meals`, `/meals/statistics`, `/meals/comment`, `/meals/estimate-calories`