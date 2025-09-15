# Task Completion Checklist

When completing any development task in the Kcal Manager project, ensure you follow these steps:

## Before Starting
1. Review existing code patterns in similar files
2. Check current git status to understand the working state
3. Read relevant test files to understand expected behavior

## During Development

### Backend (Laravel)
1. Follow PSR-12 coding standards
2. Add appropriate validation for user inputs
3. Use type hints for parameters and return types
4. Implement proper error handling with try-catch blocks
5. Add database transactions where appropriate
6. Use Laravel's built-in helpers and facades

### Frontend (React)
1. Use functional components with hooks
2. Implement proper error boundaries
3. Add loading states for async operations
4. Validate props where necessary
5. Clean up effects in useEffect return functions
6. Use semantic HTML elements

## After Implementation

### Code Quality Checks
**Backend:**
```bash
# Format code
cd backend && ./vendor/bin/pint

# Run tests
cd backend && composer test

# Check for Laravel-specific issues
php artisan optimize:clear
```

**Frontend:**
```bash
# Run linter
cd frontend && npm run lint

# Run tests
cd frontend && npm test

# Build to check for compilation errors
cd frontend && npm run build
```

### Testing Requirements
1. **Write/Update Tests**:
   - Backend: Add feature tests for new API endpoints
   - Backend: Add unit tests for new model methods or services
   - Frontend: Add component tests for new UI components
   - Frontend: Add integration tests for new features

2. **Run All Tests**:
   - Ensure all existing tests still pass
   - Verify new tests cover edge cases
   - Check test coverage if available

### Final Verification
1. **Manual Testing**:
   - Test the feature in the browser
   - Check responsive design on different screen sizes
   - Verify API responses in network tab
   - Test error scenarios

2. **Code Review Checklist**:
   - [ ] No console.log or debug statements left
   - [ ] No commented-out code
   - [ ] No hardcoded values that should be configurable
   - [ ] Proper error messages for users
   - [ ] Database migrations are reversible
   - [ ] API endpoints follow RESTful conventions
   - [ ] React components are reusable where appropriate

3. **Documentation**:
   - Update API documentation if endpoints changed
   - Add JSDoc/PHPDoc for complex functions
   - Update README if setup process changed

## Common Issues to Check
- CORS configuration for API calls
- Authentication tokens properly handled
- Database transactions for data integrity
- Memory leaks in React components
- Proper cleanup of intervals/timeouts
- SQL injection prevention (use Eloquent ORM)
- XSS prevention (React handles this, but be careful with dangerouslySetInnerHTML)

## Performance Considerations
- Use pagination for large data sets
- Implement caching where appropriate
- Optimize database queries (avoid N+1 problems)
- Lazy load React components when beneficial
- Minimize bundle size (check with npm run build)