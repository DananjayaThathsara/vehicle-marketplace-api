# Vehicle Marketplace API

A comprehensive RESTful API for a vehicle marketplace built with Laravel 11. This API enables users to list, search, and purchase vehicles through a modern, scalable architecture.

## Features

- **Vehicle Management**: Complete CRUD operations for vehicle listings
- **User Authentication**: Secure API authentication using Laravel Sanctum
- **Advanced Search**: Full-text search with Algolia integration and filtering capabilities
- **Order Processing**: Complete order lifecycle management with payment integration
- **Review System**: User reviews and ratings for vehicles
- **Caching Layer**: Redis-based caching for improved performance
- **Event-Driven Architecture**: Asynchronous processing with Laravel events and listeners
- **Soft Deletes**: Safe data management with soft delete functionality
- **API Documentation**: Well-structured REST endpoints

## Technology Stack

- **Framework**: Laravel 11
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum
- **Search**: Laravel Scout with Algolia
- **Caching**: Redis
- **Queue**: Database/Redis queues

## Design Patterns Implemented

### Repository Pattern

- Abstract data access layer through repository interfaces
- Clean separation between business logic and data access
- Easy to mock for testing and switch data sources

### Service Layer Pattern

- Business logic encapsulated in service classes
- Transaction management and complex operations
- Dependency injection for loose coupling

### Observer Pattern

- Automatic cache invalidation on model changes
- Logging and audit trails
- Business rule enforcement

### Event-Listener Pattern

- Asynchronous order processing
- Email notifications
- Analytics tracking
- Decoupled system components

### Factory Pattern

- Database seeding with model factories
- Test data generation
- Consistent object creation

## API Endpoints

### Public Endpoints

- `GET /api/vehicles` - List all vehicles
- `GET /api/vehicles/{id}` - Get vehicle details
- `GET /api/vehicles/featured` - Get featured vehicles
- `GET /api/search` - Search vehicles
- `GET /api/search/suggestions` - Search suggestions
- `POST /api/register` - User registration
- `POST /api/login` - User login

### Protected Endpoints (Require Authentication)

- `POST /api/vehicles` - Create vehicle listing
- `PUT /api/vehicles/{id}` - Update vehicle
- `DELETE /api/vehicles/{id}` - Delete vehicle
- `GET /api/orders` - List user orders
- `POST /api/orders` - Create order
- `POST /api/orders/{id}/payment` - Process payment
- `POST /api/logout` - Logout

## Installation

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd vehicle-api
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node dependencies**

    ```bash
    npm install
    ```

4. **Environment Setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Database Setup**

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

6. **Configure Services**
    - Set up Redis for caching
    - Configure Algolia credentials for search
    - Set up mail configuration

7. **Build Assets**

    ```bash
    npm run build
    ```

8. **Start the Server**
    ```bash
    php artisan serve
    ```

## Database Schema

### Vehicles Table

- Vehicle identification (VIN, make, model, year)
- Pricing information
- Status management (draft, listed, sold, etc.)
- Features and images (JSON)
- Seller relationships
- Soft deletes support

### Orders Table

- Order lifecycle management
- Payment processing
- Buyer-seller relationships

### Users Table

- Authentication and profiles
- Role-based access

### Reviews Table

- User reviews and ratings
- Vehicle associations

## Architecture Overview

```
├── Controllers (API Layer)
├── Services (Business Logic)
├── Repositories (Data Access)
├── Models (Data Models)
├── Events & Listeners (Async Processing)
├── Observers (Model Events)
├── Middleware (Request Processing)
└── Jobs (Background Processing)
```

## Key Components

### Service Classes

- `VehicleService`: Handles vehicle business logic
- `OrderService`: Manages order processing
- `SearchService`: Implements search functionality
- `CacheService`: Centralized caching operations

### Repository Classes

- `VehicleRepository`: Vehicle data operations
- `OrderRepository`: Order data operations
- `UserRepository`: User data operations

### Event System

- `OrderEvent`: Triggered on order creation
- Listeners: Email notifications, analytics, seller notifications

## Testing

Run the test suite:

```bash
php artisan test
```

## Performance Features

- **Caching**: Redis-based caching for frequently accessed data
- **Search Optimization**: Algolia for fast, scalable search
- **Database Indexing**: Optimized indexes on searchable fields
- **Queue Processing**: Asynchronous job processing for heavy operations

## Security

- API authentication with Laravel Sanctum
- Input validation and sanitization
- Rate limiting
- CORS configuration
- SQL injection prevention through Eloquent ORM

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request


