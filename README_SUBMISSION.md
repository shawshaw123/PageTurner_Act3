# PageTurner Online Bookstore Management System

## Laboratory Activity 3: Online Bookstore Management System

### Project Overview
PageTurner is a comprehensive online bookstore management system built with Laravel 12. This application demonstrates full-stack web development capabilities including user authentication, database management, CRUD operations, and responsive web design.

### Features Implemented
- **User Authentication**: Registration, login, logout with Laravel Breeze
- **Role-Based Access Control**: Admin and customer roles
- **Book Management**: Add, edit, delete, search, and filter books
- **Category Management**: Organize books by categories
- **Order Processing**: Customers can place and track orders
- **Review System**: Users can rate and review books
- **Responsive Design**: Mobile-friendly interface using Tailwind CSS
- **Database Seeding**: Realistic test data with factories and seeders

### Technical Stack
- **Backend**: Laravel 12 (PHP 8.5.1)
- **Frontend**: Blade Templates, Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Breeze
- **ORM**: Eloquent with relationships
- **Validation**: Form requests and validation rules

## Setup Instructions

### Prerequisites
- PHP 8.5.1 or higher
- Composer
- MySQL Database
- XAMPP/WAMP/MAMP (for local development)

### Installation Steps

1. **Extract the Project**
   ```bash
   # Extract the zip file to your web server directory
   # For XAMPP: C:\xampp\htdocs\Actvity3\
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   ```bash
   # Create MySQL database
   mysql -u root -e "CREATE DATABASE pageturner_bookstore;"
   
   # Import the database (if SQL dump is provided)
   mysql -u root pageturner_bookstore < database_export.sql
   ```

4. **Environment Configuration**
   ```bash
   # Copy environment file
   copy .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

5. **Configure .env file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pageturner_bookstore
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run Migrations and Seeders** (if not using SQL dump)
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Start the Application**
   ```bash
   php artisan serve
   ```

8. **Access the Application**
   - URL: http://localhost:8000
   - Admin Login: admin@pageturner.com / password
   - Customer Registration: Available on the login page

### Test Accounts

#### Administrator Account
- **Email**: admin@pageturner.com
- **Password**: password
- **Access**: Full administrative privileges including:
  - Add/Edit/Delete books
  - Add/Edit/Delete categories
  - View all orders
  - Manage reviews

#### Customer Accounts
Register to Login

### Database Schema

#### Tables
1. **users** - User accounts with roles (admin/customer)
2. **categories** - Book categories
3. **books** - Book information with foreign key to categories
4. **orders** - Customer orders with status tracking
5. **order_items** - Individual items within orders
6. **reviews** - Book reviews with ratings

#### Relationships
- User has many Orders and Reviews
- Category has many Books
- Book belongs to Category, has many Reviews and OrderItems
- Order belongs to User, has many OrderItems
- OrderItem belongs to Order and Book
- Review belongs to User and Book

### Application Routes

#### Public Routes
- `/` - Homepage with featured books
- `/books` - Book listing with search and filter
- `/books/{book}` - Book details with reviews
- `/categories` - Categories listing
- `/categories/{category}` - Category with books

#### Authentication Routes
- `/register` - User registration
- `/login` - User login
- `/logout` - User logout
- `/password/reset` - Password reset

#### Authenticated User Routes
- `/orders` - User order history
- `/orders/{order}` - Order details
- `/reviews` - Review management

#### Admin Routes
- `/admin/books` - Book management (CRUD)
- `/admin/categories` - Category management (CRUD)
- `/admin/orders` - Order management

### Key Features Demonstrated

#### Laravel Concepts
- **MVC Architecture**: Proper separation of concerns
- **Eloquent Relationships**: hasMany, belongsTo relationships
- **Resource Controllers**: Full CRUD operations
- **Route Model Binding**: Automatic model resolution
- **Middleware**: Authentication and authorization
- **Form Requests**: Input validation
- **Blade Templates**: Dynamic content rendering
- **Components**: Reusable UI components

#### Advanced Features
- **Search and Filtering**: Dynamic book search
- **Image Upload**: Book cover image handling
- **Pagination**: Large dataset handling
- **Authorization Gates**: Role-based access control
- **Database Factories**: Realistic test data generation
- **Seeders**: Database population with meaningful data

### File Structure

```
Actvity3/
├── app/
│   ├── Http/Controllers/          # Application controllers
│   ├── Models/                     # Eloquent models
│   ├── Http/Requests/              # Form request validation
│   └── Providers/                  # Service providers
├── database/
│   ├── migrations/                 # Database schema migrations
│   ├── seeders/                    # Database seeders
│   └── factories/                  # Model factories
├── resources/
│   ├── views/                      # Blade templates
│   │   ├── layouts/               # Layout templates
│   │   ├── partials/              # Reusable partials
│   │   ├── components/            # Blade components
│   │   ├── books/                 # Book-related views
│   │   ├── categories/            # Category-related views
│   │   └── orders/                # Order-related views
│   └── css/                       # Compiled CSS
├── routes/
│   ├── web.php                    # Web routes
│   └── auth.php                   # Authentication routes
├── public/                        # Public assets
├── storage/                       # File storage
└── config/                        # Configuration files
```

### Additional Notes

#### Custom Fiction Books
The Fiction category has been populated with specific books as requested:
1. **The Night Circus** by Erin Morgenstern
2. **Project Hail Mary** by Andy Weir
3. **Piranesi** by Susanna Clarke
4. **The Seven Husbands of Evelyn Hugo** by Taylor Jenkins Reid
5. **Klara and the Sun** by Kazuo Ishiguro

#### Database Configuration
The application is configured to use MySQL by default. The bootstrap configuration ensures MySQL connection regardless of .env file settings.

#### Security Features
- Password hashing with bcrypt
- CSRF protection
- Input validation and sanitization
- Authorization checks for admin functions
- SQL injection prevention through Eloquent ORM

### Troubleshooting

#### Common Issues
1. **Database Connection Error**: Ensure MySQL is running and database exists
2. **Permission Errors**: Check file permissions for storage directory
3. **Cache Issues**: Run `php artisan optimize:clear`
4. **Migration Errors**: Drop and recreate database, then run migrations again

#### Useful Commands
```bash
# Clear all caches
php artisan optimize:clear

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new admin user
php artisan tinker
> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password'), 'role' => 'admin']);
```

---

**Project Completion Date**: February 2026  
**Laravel Version**: 12.50.0  
**PHP Version**: 8.5.1  
**Database**: MySQL 8.0
