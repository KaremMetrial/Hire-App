# Hire-App - Car Rental Management System

A comprehensive Laravel-based car rental platform that connects users with rental shops, featuring advanced booking management, procedure tracking, and vendor dashboard capabilities.

## 🚀 Features

### Core Functionality
- **Multi-user System**: Separate interfaces for users, vendors, and administrators
- **Car Management**: Comprehensive car catalog with images, specifications, and availability
- **Booking System**: Complete booking lifecycle from creation to completion
- **Location Services**: GPS-based pickup and delivery options
- **Payment Integration**: Secure payment processing with multiple methods
- **Review System**: User reviews and ratings for rental shops and cars

### Advanced Features
- **Pickup & Return Procedures**: Image-based procedure tracking for car handovers
- **Real-time Notifications**: Instant updates on booking status changes
- **Admin Dashboard**: Comprehensive analytics and management tools
- **Vendor Management**: Dedicated vendor portal for shop management
- **Mobile Responsive**: Optimized for all device types

## 🛠️ Tech Stack

- **Backend**: Laravel 11.x
- **Database**: MySQL 8.0+
- **Frontend**: Blade templates with Tailwind CSS
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel Storage (local/S3)
- **Queue System**: Laravel Queues
- **Real-time**: Laravel Broadcasting (optional)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0+
- Laravel Sail (recommended) or Docker

## 🚀 Installation

### Using Laravel Sail (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/hire-app.git
   cd hire-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Start Laravel Sail**
   ```bash
   ./vendor/bin/sail up -d
   ```

6. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Seed the database (optional)**
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```


### Manual Installation

1. **Set up your web server** (Apache/Nginx) to point to the `public` directory
2. **Configure your database** in `.env` file
3. **Run migrations**: `php artisan migrate`
4. **Build assets**: `npm run build`

## 🔧 Configuration

### Environment Variables

Copy `.env.example` to `.env` and configure the following:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hire_app
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# File Storage
FILESYSTEM_DISK=public

# Payment Gateway (if applicable)
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### Database Setup

The application uses the following key tables:
- `users` - Application users
- `vendors` - Rental shop owners
- `cars` - Vehicle inventory
- `bookings` - Rental bookings
- `booking_procedures` - Pickup/return procedures
- `booking_procedure_images` - Procedure images
- `rental_shops` - Rental shop information

## 📖 API Documentation

### Authentication

The API uses token-based authentication with separate endpoints for users and vendors.

#### User Authentication
```bash
POST /api/v1/register
POST /api/v1/login
```

#### Vendor Authentication
```bash
POST /vendor/v1/register
POST /vendor/v1/login
```

### Key Endpoints

#### User Endpoints
- `GET /api/v1/cars` - Browse available cars
- `POST /api/v1/bookings` - Create booking
- `POST /api/v1/bookings/{id}/submit-pickup-procedure` - Submit pickup procedure
- `POST /api/v1/bookings/{id}/submit-return-procedure` - Submit return procedure
- `GET /api/v1/bookings/{id}/procedures` - Get booking procedures

#### Vendor Endpoints
- `GET /vendor/v1/bookings` - Get vendor bookings
- `POST /vendor/v1/bookings/{id}/confirm-pickup-procedure` - Confirm pickup procedure
- `POST /vendor/v1/bookings/{id}/confirm-return-procedure` - Confirm return procedure
- `GET /vendor/v1/bookings/{id}/procedures` - Get booking procedures

## 🎯 Usage

### Booking Flow

1. **User Registration/Login**
2. **Browse Cars** - Filter by location, dates, car type
3. **Create Booking** - Select car, dates, pickup location
4. **Submit Pickup Procedure** - Upload car condition images
5. **Vendor Confirmation** - Vendor reviews and confirms pickup
6. **Rental Period** - Active booking status
7. **Submit Return Procedure** - Upload return condition images
8. **Vendor Confirmation** - Vendor reviews and completes booking
9. **Payment Processing** - Final charges calculation

### Pickup & Return Procedures

The system includes comprehensive image-based procedures:

- **Pickup Procedure**: User submits car condition photos, vendor reviews and adds their own images
- **Return Procedure**: User submits return condition photos, vendor reviews and adds their own images
- **Status Tracking**: Real-time status updates and notifications
- **Image Management**: Secure storage with type validation

## 🧪 Testing

```bash
# Run PHP tests
./vendor/bin/sail artisan test


## 📦 Deployment

### Production Deployment

1. **Environment Setup**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. **Database Migration**
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Queue Workers** (if using queues)
   ```bash
   php artisan queue:work
   ```

4. **File Permissions**
   ```bash
   chown -R www-data:www-data storage
   chown -R www-data:www-data bootstrap/cache
   ```

### Docker Deployment

```bash
# Build and run with Docker
docker-compose up -d --build
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Use meaningful commit messages

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- All our contributors and users

## 📞 Support

For support, email support@hire-app.com or join our Discord community.

---

**Made with ❤️ using Laravel**
