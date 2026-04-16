# PageTurner Online Bookstore Management System

A comprehensive Laravel-based online bookstore management system developed for ITSD 82 Web Software Tools laboratory activity.

## Features

### Core Functionality
- **User Authentication**: Complete registration, login, and profile management using Laravel Breeze
- **Role-Based Access Control**: Admin and Customer roles with different permissions
- **Book Management**: Full CRUD operations for books with categories, pricing, and inventory
- **Category Management**: Organize books into categories with descriptions
- **Review System**: Customers can rate and review books they've purchased
- **Order Management**: Track customer orders and order status

### Technical Features
- **Database Operations**: Complete Eloquent ORM implementation with relationships
- **Blade Templating**: Advanced template system with layouts, components, and partials
- **Responsive Design**: Mobile-friendly UI using Tailwind CSS
- **Form Validation**: Comprehensive input validation and error handling
- **Data Seeding**: Realistic test data generation using factories

## Installation

### Prerequisites
- PHP 8.2+
- MySQL/MariaDB
- Composer
- Node.js & NPM
- XAMPP (for local development)

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   cd c:\Users\User\MARK\xammp\htdocs\Actvity3
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Database Configuration**
   - Create a MySQL database named `pageturner_bookstore`
   - Configure `.env` file with your database credentials:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DATABASE=pageturner_bookstore
   USERNAME=root
   PASSWORD=your_password
   ```

4. **Run Migrations and Seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start Development Server**
   ```bash
   php artisan serve
   ```

6. **Access the Application**
   - URL: http://localhost:8000
   - Admin Login: admin@pageturner.com / password
   - Customer Accounts: Created during seeding

## Database Schema

### Tables
- **users**: User accounts with role-based access (admin/customer)
- **categories**: Book categories with descriptions
- **books**: Book inventory with category relationships
- **orders**: Customer orders with status tracking
- **order_items**: Individual items within orders
- **reviews**: Customer book ratings and comments

### Relationships
- Users have many Orders and Reviews
- Categories have many Books
- Books belong to Categories, have many Reviews and OrderItems
- Orders belong to Users, have many OrderItems
- Reviews belong to Users and Books

## Route Structure

### Public Routes
- `/` - Homepage with featured books and categories
- `/books` - Book listing with search and filtering
- `/books/{book}` - Book details with reviews
- `/categories` - Category listing
- `/categories/{category}` - Category-specific books

### Authenticated Routes
- `/profile` - User profile management
- `/orders` - User order history
- `/books/{book}/reviews` - Submit book reviews

### Admin Routes
- `/admin/categories/*` - Category management (CRUD)
- `/admin/books/*` - Book management (CRUD)

## Controllers

- **HomeController**: Homepage with featured content
- **BookController**: Book CRUD operations and search
- **CategoryController**: Category management
- **OrderController**: Order processing and tracking
- **ReviewController**: Review submission and management

## Blade Components

- **layouts/app.blade.php**: Main application layout
- **components/alert.blade.php**: Reusable alert component
- **components/book-card.blade.php**: Book display card
- **partials/navigation.blade.php**: Site navigation
- **partials/footer.blade.php**: Site footer
- **partials/flash-messages.blade.php**: Success/error messages

## Testing Data

The application includes comprehensive seeders that create:
- 1 Admin user (admin@pageturner.com)


## Security Features

- CSRF protection on all forms
- Input validation and sanitization
- Role-based authorization
- Password hashing
- SQL injection prevention via Eloquent ORM

## Browser Compatibility

- Chrome/Chromium (recommended)
- Firefox
- Safari
- Edge

## Development Notes

- Uses Laravel 12 with PHP 8.2+
- Tailwind CSS for styling
- Laravel Breeze for authentication
- Responsive design for mobile compatibility
- Clean, maintainable code following Laravel conventions

## Future Enhancements

Potential bonus features to implement:
- Shopping cart functionality
- Advanced search with filters
- Image upload and processing
- Order status notifications
- Payment integration
- User dashboard with statistics

## Support

For technical support or questions regarding this laboratory activity, please refer to the course materials or contact your instructor.

---

**Developed for:** ITSD 82 Web Software Tools  
**Laboratory Activity 3:** Online Bookstore Management System  
**Date:** February 2026
