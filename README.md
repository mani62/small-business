# Small Business Task Management System

A comprehensive Laravel-based task management system with CRUD operations, filtering, pagination, and advanced design patterns implementation.

## 🚀 Features Implemented

### Core Functionality
- **CRUD Operations**: Complete Create, Read, Update, Delete operations for tasks
- **Task Fields**: `title`, `description`, `status` (todo/in-progress/done), `due_date`
- **Filtering**: By status, due date, full-text search, overdue tasks
- **Pagination**: Configurable pagination with metadata
- **Sorting**: Sort by title, status, due date, created_at, updated_at
- **User Isolation**: Users can only access their own tasks
- **Rate Limiting**: 100 requests per minute per user
- **Statistics**: Task statistics and analytics
- **Bulk Operations**: Bulk status updates

### Design Patterns Applied

#### 1. Repository Pattern
- **Interface**: `TaskRepositoryInterface`
- **Implementation**: `TaskRepository`
- **Purpose**: Abstracts data access logic from business logic
- **Benefits**: Testability, maintainability, flexibility

#### 2. Strategy Pattern
- **Interface**: `FilterStrategyInterface`
- **Implementations**:
  - `StatusFilterStrategy` - Filter by task status
  - `DueDateFilterStrategy` - Filter by due date
  - `SearchFilterStrategy` - Full-text search
  - `OverdueFilterStrategy` - Filter overdue tasks
- **Context**: `FilterContext` - Applies multiple strategies
- **Purpose**: Encapsulates filtering algorithms, makes them interchangeable

#### 3. Observer Pattern
- **Observer**: `TaskObserver`
- **Events**: `created`, `updated`, `deleted`
- **Purpose**: Handles side effects when task state changes
- **Benefits**: Decoupled logging, extensible for notifications

### Architecture Components

#### Models
- **Task Model**: Eloquent model with relationships, scopes, and computed properties
- **User Model**: Extended with task relationship

#### Services
- **TaskService**: Business logic layer with comprehensive task operations
- **FilterContext**: Strategy pattern implementation for filtering

#### DTOs (Data Transfer Objects)
- **TaskDTO**: Single task representation
- **CreateTaskDTO**: Task creation data
- **UpdateTaskDTO**: Task update data
- **TaskListDTO**: Paginated task list
- **TaskStatisticsDTO**: Statistics data

#### Controllers
- **TaskController**: HTTP request handling with proper validation and error handling

#### Requests
- **CreateTaskRequest**: Validation for task creation
- **UpdateTaskRequest**: Validation for task updates

#### Middleware
- **TaskRateLimitMiddleware**: Custom rate limiting for task endpoints

#### Enums
- **TaskStatus**: Type-safe status constants with helper methods

## 🛠️ Technical Implementation

### Key Features
- **Type Safety**: PHP 8+ features with proper type hints
- **Validation**: Comprehensive request validation
- **Error Handling**: Structured error responses with proper HTTP status codes
- **Logging**: Detailed logging for all operations
- **Testing**: Comprehensive test coverage (18 tests)
- **Security**: Authentication required, user isolation
- **Performance**: Database indexes, efficient queries

## 📋 API Documentation

For complete API documentation including all endpoints, request/response examples, and testing instructions, please refer to:

**[📖 Complete API Documentation](API_DOCUMENTATION.md)**

This documentation includes:
- **30 API Endpoints** across Authentication, Tasks, and Projects
- **Complete Request/Response Examples** with JSON samples
- **Query Parameters** and validation rules
- **Error Handling** with all HTTP status codes
- **Rate Limiting** information
- **cURL Examples** for testing
- **Status Values** and enums

## 🧪 Testing

The system includes comprehensive test coverage:

### Test Files
- `tests/Feature/TaskTest.php` - Main functionality tests (14 tests)
- `tests/Feature/TaskAuthTest.php` - Authentication tests (4 tests)

### Test Coverage
- ✅ CRUD operations
- ✅ Authentication requirements
- ✅ User isolation
- ✅ Validation
- ✅ Filtering and search
- ✅ Pagination
- ✅ Sorting
- ✅ Statistics
- ✅ Bulk operations
- ✅ Error handling

### Running Tests
```bash
# Run all task tests
php artisan test tests/Feature/TaskTest.php tests/Feature/TaskAuthTest.php

# Run with coverage
php artisan test --coverage
```

## 🚀 Installation & Setup

### Prerequisites
- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL
- Composer

### Installation Steps
1. Clone the repository
2. Install dependencies: `composer install`
3. Copy environment file: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Configure database in `.env`
6. Run migrations: `php artisan migrate`
7. Run tests: `php artisan test`

## 📊 Rate Limiting

- **Limit**: 100 requests per minute per user
- **Scope**: All task-related endpoints
- **Headers**: Includes rate limit information in responses
- **Fallback**: IP-based limiting for unauthenticated requests

## 🔒 Security Features

- **Authentication**: Laravel Sanctum token-based authentication
- **Authorization**: User isolation (users can only access their own tasks)
- **Validation**: Comprehensive input validation
- **Rate Limiting**: Prevents abuse and ensures fair usage
- **SQL Injection Protection**: Eloquent ORM with parameterized queries
- **XSS Protection**: Proper output escaping

## 📈 Performance Optimizations

- **Database Indexes**: Optimized for common query patterns
- **Eager Loading**: Prevents N+1 query problems
- **Pagination**: Limits data transfer
- **Query Optimization**: Efficient filtering and sorting
- **Caching**: Ready for implementation (Redis/Memcached)

## 🎯 Future Enhancements

- **Soft Deletes**: Implement soft delete functionality
- **Task Categories**: Add categorization system
- **File Attachments**: Support for task attachments
- **Notifications**: Real-time notifications for task updates
- **Collaboration**: Multi-user task assignment
- **Time Tracking**: Track time spent on tasks
- **Reporting**: Advanced analytics and reporting
- **API Versioning**: Support for multiple API versions

## 📝 License

This project is licensed under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📞 Support

For support and questions, please open an issue in the repository.