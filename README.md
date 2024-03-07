# Attendance System

Attendance system API developed using Laravel 10.

## Setup Instructions

### Prerequisites

- PHP >= 7.4
- Composer
- PostgreSQL

### Clone the Repository

git clone <https://github.com/hafizulf/attendance_system.git>

### Installation

1. Navigate to the project directory:

    ```bash
    cd attendance-system
    ```

2. Install dependencies using Composer

    ```bash
    composer install
    ```

### Configuration

1. Rename the `.env.example` file to `.env`.
2. Set up your database connection in the `.env` file.
3. Generate application key:

    ```bash
    php artisan key:generate
    ```

### Database Migration

Run database migrations to create necessary tables:

   ```bash
    php artisan migrate
```

### Serve The Application

You can use Laravel's built-in development server to serve the application:

   ```bash
    php artisan serve
```

The application will be accessible at <http://localhost:8000>.
