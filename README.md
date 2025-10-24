# Small Business Management System

A comprehensive Laravel-based system for managing tasks and projects with CRUD operations, filtering, pagination, and advanced design patterns.

## 🚀 Features

### Task Management
- **CRUD Operations**: Complete task management
- **Fields**: `title`, `description`, `status` (todo/in-progress/done), `due_date`
- **Filtering**: By status, due date, full-text search, overdue tasks
- **Statistics**: Task analytics and progress tracking
- **Bulk Operations**: Mass status updates

### Project Management
- **CRUD Operations**: Complete project management
- **Fields**: `name`, `description`, `status` (planning/in-progress/on-hold/completed/cancelled), `due_date`
- **Filtering**: By status, due date, full-text search, overdue projects
- **Advanced Features**: Soft delete, restore, duplicate projects
- **Statistics**: Project analytics and progress tracking

### Authentication & Security
- **Laravel Sanctum**: Token-based authentication
- **User Registration/Login**: Complete auth flow
- **Rate Limiting**: 100 requests/minute for tasks, 5 requests/minute for auth
- **User Isolation**: Users can only access their own data

### Design Patterns
- **Repository Pattern**: Clean data access layer
- **Strategy Pattern**: Flexible filtering system
- **Observer Pattern**: Event-driven logging and notifications

## 📋 API Documentation

**[📖 Complete API Documentation](API_DOCUMENTATION.md)**

- **30 API Endpoints** across Authentication, Tasks, and Projects
- **Complete Request/Response Examples**
- **cURL Examples** for testing

## 🧪 Testing

- **18 Tests** covering all functionality
- **Authentication tests**
- **CRUD operations**
- **Filtering and search**
- **Error handling**

```bash
php artisan test
```

## 🚀 Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test
```

## 🔒 Security

- **Authentication required** for all endpoints
- **User isolation** - users only see their own data
- **Rate limiting** prevents abuse
- **Input validation** on all requests
- **SQL injection protection** via Eloquent ORM

## 📈 Performance

- **Database indexes** for optimal queries
- **Pagination** for large datasets
- **Efficient filtering** with Strategy pattern
- **Query optimization** throughout

## 🎯 Tech Stack

- **Laravel 10+** - PHP framework
- **MySQL/PostgreSQL** - Database
- **Laravel Sanctum** - Authentication
- **PHP 8.1+** - Language features
- **Pest PHP** - Testing framework

## 📝 License

MIT License

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📞 Support

For support and questions, please open an issue in the repository.