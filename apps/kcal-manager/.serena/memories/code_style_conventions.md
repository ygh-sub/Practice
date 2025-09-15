# Code Style and Conventions

## Backend (Laravel/PHP)

### PHP Style Guide
- **PHP Version**: 8.2+
- **PSR Standards**: PSR-12 coding standard
- **Formatting**: Use Laravel Pint for automatic formatting
- **Naming Conventions**:
  - Classes: PascalCase (e.g., `MealController`)
  - Methods: camelCase (e.g., `generateComment()`)
  - Variables: camelCase (e.g., `$totalCalories`)
  - Constants: UPPER_SNAKE_CASE
  - Database tables: snake_case plural (e.g., `meals`)
  - Model properties: snake_case (e.g., `is_estimated`)

### Laravel Patterns
- **Controllers**: Keep controllers thin, use services for business logic
- **Models**: Use `$fillable` for mass assignment protection
- **Validation**: Use Form Request classes or inline validation rules
- **API Responses**: Consistent JSON structure with `message` and `data` keys
- **Testing**: Feature tests for API endpoints, Unit tests for models/services
- **Type Hints**: Always use type declarations for parameters and return types where possible

### Database
- **Migrations**: Use descriptive names with timestamps
- **Eloquent**: Leverage relationships and query scopes
- **Seeders**: Create realistic demo data for testing

## Frontend (React/JavaScript)

### JavaScript Style Guide
- **ECMAScript**: ES2020+
- **Module System**: ES6 modules
- **React Version**: 19.x with functional components and hooks
- **Formatting**: Configured via ESLint
- **Naming Conventions**:
  - Components: PascalCase (e.g., `MealList`)
  - Functions/Variables: camelCase (e.g., `handleSubmit`)
  - Constants: UPPER_SNAKE_CASE
  - Files: Components in PascalCase.jsx, utilities in camelCase.js

### React Patterns
- **Components**: Functional components with hooks only (no class components)
- **State Management**: React Context for auth, local state for components
- **Custom Hooks**: Prefix with `use` (e.g., `useAuth`)
- **Props**: Destructure props in function parameters
- **Effects**: Clean up side effects in useEffect
- **Error Boundaries**: Implement for production resilience

### Styling
- **Framework**: Tailwind CSS with utility-first approach
- **Component Styling**: Use Tailwind classes directly in JSX
- **Responsive Design**: Mobile-first with Tailwind responsive prefixes
- **Custom Styles**: Minimal custom CSS in index.css

## Testing Conventions

### Backend Tests
- **Naming**: `test_` prefix for test methods
- **Structure**: Arrange-Act-Assert pattern
- **Database**: Use in-memory SQLite for tests
- **Factories**: Use Laravel factories for test data

### Frontend Tests
- **Framework**: Vitest with React Testing Library
- **Focus**: User behavior over implementation details
- **Mocking**: Mock API calls and external dependencies
- **Coverage**: Aim for critical path coverage

## Git Conventions
- **Commit Messages**: Clear, concise descriptions in present tense
- **Branch Names**: feature/*, fix/*, refactor/*
- **PR Size**: Keep pull requests focused and reviewable

## Documentation
- **Comments**: Only when necessary for complex logic
- **JSDoc/PHPDoc**: For public APIs and complex functions
- **README**: Keep updated with setup and deployment instructions