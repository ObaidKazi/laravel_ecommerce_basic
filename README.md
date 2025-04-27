
# Laravel E-commerce

A simple e-commerce platform built with Laravel.

## Getting Started

Follow these steps to set up the project locally:

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/laravel-ecommerce.git
cd laravel-ecommerce
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Copy Environment File

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Configure Environment

Edit the `.env` file and set your database credentials and any other necessary environment variables.

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed the Database

```bash
php artisan db:seed
```

### 8. Generate Swagger Documentation

If you are using **l5-swagger**, generate the API documentation with:

```bash
php artisan l5-swagger:generate
```

### 9. Serve the Application

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000) in your browser to view the application.
