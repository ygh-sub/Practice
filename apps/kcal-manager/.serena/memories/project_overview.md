# Kcal Manager Project Overview

## Project Purpose
Kcal Manager is a calorie tracking web application that helps users monitor their daily food intake. The application features:
- User authentication and registration
- Meal tracking with calorie counting
- Automatic calorie estimation using LLM API
- Daily meal comments and insights
- Analytics and statistics for calorie consumption
- Demo data seeding for testing

## Tech Stack

### Backend (Laravel)
- **Framework**: Laravel 12.x with PHP 8.2+
- **Database**: SQLite (default) with Laravel Eloquent ORM
- **Authentication**: Laravel Sanctum for API authentication
- **Testing**: PHPUnit 11.x
- **Code Quality**: Laravel Pint for formatting
- **Development Tools**: Laravel Sail (Docker), Faker for test data

### Frontend (React)
- **Framework**: React 19.x with Vite
- **Routing**: React Router DOM v7
- **HTTP Client**: Axios for API communication
- **Styling**: Tailwind CSS with PostCSS
- **Charts**: Recharts for data visualization
- **Testing**: Vitest with React Testing Library
- **Code Quality**: ESLint for linting

## Project Structure
```
apps/kcal-manager/
├── backend/          # Laravel API backend
│   ├── app/         # Application logic
│   ├── config/      # Configuration files
│   ├── database/    # Migrations and seeders
│   ├── routes/      # API routes
│   └── tests/       # Unit and feature tests
├── frontend/        # React frontend
│   ├── src/
│   │   ├── api/        # API client services
│   │   ├── components/ # React components
│   │   ├── contexts/   # React contexts (Auth)
│   │   └── hooks/      # Custom React hooks
│   └── public/      # Static assets
└── kcal-frontend/   # Legacy frontend directory
```

## Key Features
1. **Meal Management**: CRUD operations for meals with name, calories, portion, and date
2. **Calorie Estimation**: Automatic calorie estimation for meals using LLM API with fallback values
3. **Daily Comments**: AI-generated daily meal insights based on total calorie intake
4. **Statistics**: Track calorie consumption over date ranges with averages and totals
5. **User Authentication**: Secure login/registration with JWT tokens via Laravel Sanctum
6. **Demo Mode**: Pre-seeded demo user with sample meal data for testing