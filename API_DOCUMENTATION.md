# Complete API Documentation

## Base URL
```
http://localhost/api/v1
```

## Authentication
Most endpoints require Bearer token authentication:
```
Authorization: Bearer {your_token}
```

## Rate Limiting
- **Auth endpoints**: 5 requests per minute
- **Task endpoints**: 100 requests per minute per user
- **Project endpoints**: No specific rate limiting

---

# 🔐 Authentication APIs

## 1. Register User
```http
POST /auth/register
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z"
  },
  "token": "1|abc123def456..."
}
```

---

## 2. Login User
```http
POST /auth/login
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z"
  },
  "token": "2|xyz789abc123..."
}
```

---

## 3. Logout User
```http
POST /auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Logout successful"
}
```

---

## 4. Logout from All Devices
```http
POST /auth/logout-all
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Logged out from all devices successfully"
}
```

---

## 5. Get User Info
```http
GET /auth/me
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z"
  }
}
```

---

## 6. Refresh Token
```http
POST /auth/refresh
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Token refreshed successfully",
  "token": "3|new123token456..."
}
```

---

## 7. Get Current User
```http
GET /user
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-10-24T10:00:00.000000Z",
  "updated_at": "2024-10-24T10:00:00.000000Z"
}
```

---

# 📋 Task Management APIs

## 1. List Tasks
```http
GET /tasks
```

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by status | `todo`, `in_progress`, `done` |
| `due_date` | date | Filter by due date | `2024-12-31` |
| `search` | string | Search in title/description | `meeting` |
| `overdue` | boolean | Show overdue tasks | `true` |
| `sort_by` | string | Sort field | `title`, `status`, `due_date`, `created_at`, `updated_at` |
| `sort_order` | string | Sort direction | `asc`, `desc` |
| `per_page` | integer | Items per page (max 100) | `15` |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Complete project",
        "description": "Finish the Laravel project",
        "status": "todo",
        "due_date": "2024-12-31",
        "user_id": 1,
        "created_at": "2024-10-24T10:00:00.000000Z",
        "updated_at": "2024-10-24T10:00:00.000000Z",
        "progress_percentage": 0,
        "is_overdue": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 25,
      "last_page": 2,
      "from": 1,
      "to": 15
    }
  },
  "message": "Tasks retrieved successfully"
}
```

---

## 2. Create Task
```http
POST /tasks
```

**Request Body:**
```json
{
  "title": "New Task",
  "description": "Task description (optional)",
  "status": "todo",
  "due_date": "2024-12-31"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "New Task",
    "description": "Task description",
    "status": "todo",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z",
    "progress_percentage": 0,
    "is_overdue": false
  },
  "message": "Task created successfully"
}
```

---

## 3. Get Single Task
```http
GET /tasks/{id}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Task Title",
    "description": "Task description",
    "status": "todo",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z",
    "progress_percentage": 0,
    "is_overdue": false
  },
  "message": "Task retrieved successfully"
}
```

---

## 4. Update Task
```http
PUT /tasks/{id}
PATCH /tasks/{id}
```

**Request Body:**
```json
{
  "title": "Updated Task Title",
  "status": "in_progress"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Updated Task Title",
    "description": "Task description",
    "status": "in_progress",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:30:00.000000Z",
    "progress_percentage": 50,
    "is_overdue": false
  },
  "message": "Task updated successfully"
}
```

---

## 5. Delete Task
```http
DELETE /tasks/{id}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Task deleted successfully"
}
```

---

## 6. Task Statistics
```http
GET /tasks/statistics
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_tasks": 25,
    "todo_tasks": 10,
    "in_progress_tasks": 8,
    "done_tasks": 7,
    "overdue_tasks": 3,
    "status_distribution": {
      "todo": 10,
      "in_progress": 8,
      "done": 7
    }
  },
  "message": "Task statistics retrieved successfully"
}
```

---

## 7. Search Tasks
```http
GET /tasks/search?q={search_term}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Meeting with client",
      "description": "Discuss project requirements",
      "status": "todo",
      "due_date": "2024-12-31",
      "user_id": 1,
      "created_at": "2024-10-24T10:00:00.000000Z",
      "updated_at": "2024-10-24T10:00:00.000000Z",
      "progress_percentage": 0,
      "is_overdue": false
    }
  ],
  "message": "Search completed successfully"
}
```

---

## 8. Filter Tasks by Status
```http
GET /tasks/status/{status}
```

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Todo Task",
      "description": "This is a todo task",
      "status": "todo",
      "due_date": "2024-12-31",
      "user_id": 1,
      "created_at": "2024-10-24T10:00:00.000000Z",
      "updated_at": "2024-10-24T10:00:00.000000Z",
      "progress_percentage": 0,
      "is_overdue": false
    }
  ],
  "message": "Tasks with status 'todo' retrieved successfully"
}
```

---

## 9. Get Overdue Tasks
```http
GET /tasks/overdue
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Overdue Task",
        "description": "This task is overdue",
        "status": "todo",
        "due_date": "2024-10-20",
        "user_id": 1,
        "created_at": "2024-10-24T10:00:00.000000Z",
        "updated_at": "2024-10-24T10:00:00.000000Z",
        "progress_percentage": 0,
        "is_overdue": true
      }
    ],
    "count": 1
  },
  "message": "Overdue tasks retrieved successfully"
}
```

---

## 10. Bulk Update Task Status
```http
POST /tasks/bulk-update-status
```

**Request Body:**
```json
{
  "task_ids": [1, 2, 3],
  "status": "done"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "updated_count": 3,
    "message": "Successfully updated 3 tasks to 'done' status"
  },
  "message": "Successfully updated 3 tasks to 'done' status"
}
```

---

# 🚀 Project Management APIs

## 1. List Projects
```http
GET /projects
```

**Query Parameters:**
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by status | `planning`, `in_progress`, `on_hold`, `completed`, `cancelled` |
| `due_date` | date | Filter by due date | `2024-12-31` |
| `search` | string | Search in name/description | `website` |
| `overdue` | boolean | Show overdue projects | `true` |
| `sort_by` | string | Sort field | `name`, `status`, `due_date`, `created_at`, `updated_at` |
| `sort_order` | string | Sort direction | `asc`, `desc` |
| `per_page` | integer | Items per page | `15` |

**Response (200):**
```json
{
  "message": "Projects retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Website Development",
        "description": "Build company website",
        "status": "in_progress",
        "due_date": "2024-12-31",
        "user_id": 1,
        "created_at": "2024-10-24T10:00:00.000000Z",
        "updated_at": "2024-10-24T10:00:00.000000Z",
        "progress_percentage": 50,
        "is_overdue": false
      }
    ]
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 10,
      "last_page": 1,
      "from": 1,
      "to": 10
    }
  }
}
```

---

## 2. Create Project
```http
POST /projects
```

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Project description (optional)",
  "status": "planning",
  "due_date": "2024-12-31"
}
```

**Response (201):**
```json
{
  "message": "Project created successfully",
  "data": {
    "id": 1,
    "name": "New Project",
    "description": "Project description",
    "status": "planning",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z",
    "progress_percentage": 0,
    "is_overdue": false
  }
}
```

---

## 3. Get Single Project
```http
GET /projects/{id}
```

**Response (200):**
```json
{
  "message": "Project retrieved successfully",
  "data": {
    "id": 1,
    "name": "Website Development",
    "description": "Build company website",
    "status": "in_progress",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z",
    "progress_percentage": 50,
    "is_overdue": false
  }
}
```

---

## 4. Update Project
```http
PUT /projects/{id}
PATCH /projects/{id}
```

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "status": "completed"
}
```

**Response (200):**
```json
{
  "message": "Project updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Project Name",
    "description": "Build company website",
    "status": "completed",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:30:00.000000Z",
    "progress_percentage": 100,
    "is_overdue": false
  }
}
```

---

## 5. Delete Project
```http
DELETE /projects/{id}
```

**Response (200):**
```json
{
  "message": "Project deleted successfully"
}
```

---

## 6. Project Statistics
```http
GET /projects/statistics
```

**Response (200):**
```json
{
  "message": "Project statistics retrieved successfully",
  "data": {
    "total_projects": 15,
    "planning_projects": 3,
    "in_progress_projects": 5,
    "on_hold_projects": 2,
    "completed_projects": 4,
    "cancelled_projects": 1,
    "overdue_projects": 2,
    "status_distribution": {
      "planning": 3,
      "in_progress": 5,
      "on_hold": 2,
      "completed": 4,
      "cancelled": 1
    }
  }
}
```

---

## 7. Search Projects
```http
GET /projects/search?q={search_term}
```

**Response (200):**
```json
{
  "message": "Search completed successfully",
  "data": [
    {
      "id": 1,
      "name": "Website Development",
      "description": "Build company website",
      "status": "in_progress",
      "due_date": "2024-12-31",
      "user_id": 1,
      "created_at": "2024-10-24T10:00:00.000000Z",
      "updated_at": "2024-10-24T10:00:00.000000Z",
      "progress_percentage": 50,
      "is_overdue": false
    }
  ]
}
```

---

## 8. Filter Projects by Status
```http
GET /projects/status/{status}
```

**Response (200):**
```json
{
  "message": "Projects with status 'in_progress' retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Website Development",
      "description": "Build company website",
      "status": "in_progress",
      "due_date": "2024-12-31",
      "user_id": 1,
      "created_at": "2024-10-24T10:00:00.000000Z",
      "updated_at": "2024-10-24T10:00:00.000000Z",
      "progress_percentage": 50,
      "is_overdue": false
    }
  ]
}
```

---

## 9. Get Overdue Projects
```http
GET /projects/overdue
```

**Response (200):**
```json
{
  "message": "Overdue projects retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Overdue Project",
        "description": "This project is overdue",
        "status": "in_progress",
        "due_date": "2024-10-20",
        "user_id": 1,
        "created_at": "2024-10-24T10:00:00.000000Z",
        "updated_at": "2024-10-24T10:00:00.000000Z",
        "progress_percentage": 50,
        "is_overdue": true
      }
    ],
    "count": 1
  }
}
```

---

## 10. Restore Project (Soft Delete)
```http
POST /projects/{id}/restore
```

**Response (200):**
```json
{
  "message": "Project restored successfully"
}
```

---

## 11. Force Delete Project
```http
DELETE /projects/{id}/force-delete
```

**Response (200):**
```json
{
  "message": "Project permanently deleted"
}
```

---

## 12. Bulk Update Project Status
```http
POST /projects/bulk-update-status
```

**Request Body:**
```json
{
  "project_ids": [1, 2, 3],
  "status": "completed"
}
```

**Response (200):**
```json
{
  "message": "Projects status updated successfully",
  "data": {
    "updated_count": 3,
    "message": "Successfully updated 3 projects to 'completed' status"
  }
}
```

---

## 13. Duplicate Project
```http
POST /projects/{id}/duplicate
```

**Response (200):**
```json
{
  "message": "Project duplicated successfully",
  "data": {
    "id": 2,
    "name": "Website Development (Copy)",
    "description": "Build company website",
    "status": "planning",
    "due_date": "2024-12-31",
    "user_id": 1,
    "created_at": "2024-10-24T10:00:00.000000Z",
    "updated_at": "2024-10-24T10:00:00.000000Z",
    "progress_percentage": 0,
    "is_overdue": false
  }
}
```

---

# 🚨 Error Responses

## Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email must be a valid email address."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

## Not Found (404)
```json
{
  "message": "Resource not found"
}
```

## Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

## Rate Limit Exceeded (429)
```json
{
  "message": "Too many requests. Please try again in 45 seconds.",
  "retry_after": 45
}
```

## Server Error (500)
```json
{
  "message": "Internal server error"
}
```

---

# 📋 Status Values

## Task Status
| Status | Label | Progress |
|--------|-------|----------|
| `todo` | To Do | 0% |
| `in_progress` | In Progress | 50% |
| `done` | Done | 100% |

## Project Status
| Status | Label | Progress |
|--------|-------|----------|
| `planning` | Planning | 0% |
| `in_progress` | In Progress | 50% |
| `on_hold` | On Hold | 25% |
| `completed` | Completed | 100% |
| `cancelled` | Cancelled | 0% |

---

# 🔧 HTTP Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Internal Server Error |

---

# 🧪 Testing Examples

## Authentication Flow
```bash
# Register
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'

# Use token in subsequent requests
curl -X GET http://localhost/api/v1/user \
  -H "Authorization: Bearer your_token_here"
```

## Task Management
```bash
# Create task
curl -X POST http://localhost/api/v1/tasks \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Task",
    "description": "Task description",
    "status": "todo",
    "due_date": "2024-12-31"
  }'

# Get tasks with filters
curl -X GET "http://localhost/api/v1/tasks?status=todo&per_page=10" \
  -H "Authorization: Bearer your_token"

# Search tasks
curl -X GET "http://localhost/api/v1/tasks/search?q=meeting" \
  -H "Authorization: Bearer your_token"
```

## Project Management
```bash
# Create project
curl -X POST http://localhost/api/v1/projects \
  -H "Authorization: Bearer your_token" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New Project",
    "description": "Project description",
    "status": "planning",
    "due_date": "2024-12-31"
  }'

# Get project statistics
curl -X GET http://localhost/api/v1/projects/statistics \
  -H "Authorization: Bearer your_token"

# Duplicate project
curl -X POST http://localhost/api/v1/projects/1/duplicate \
  -H "Authorization: Bearer your_token"
```
