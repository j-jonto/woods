# Wooden Broom Handle Manufacturing ERP System

A comprehensive Enterprise Resource Planning (ERP) system designed specifically for wooden broom handle manufacturing factories. This system streamlines and integrates all core business processes including accounting, inventory management, production planning, sales, and purchasing.

## Technology Stack

- **Backend:** PHP 8.1+ with Laravel 10
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3
- **Database:** MySQL 8.0+ (chosen for better concurrent user support)
- **Web Server:** Apache/Nginx
- **PDF Generation:** TCPDF

## System Requirements

- PHP >= 8.1
- MySQL >= 8.0
- Composer
- Node.js >= 16.x and NPM
- Apache/Nginx web server
- PHP Extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML

## Installation

1. Clone the repository:
   ```bash
   git clone [repository-url]
   cd woods
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install frontend dependencies:
   ```bash
   npm install
   ```

4. Create environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure database in .env file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=woods_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

7. Run database migrations:
   ```bash
   php artisan migrate
   ```

8. Seed initial data:
   ```bash
   php artisan db:seed
   ```

9. Compile frontend assets:
   ```bash
   npm run dev
   ```

10. Start the development server:
    ```bash
    php artisan serve
    ```

## Initial Setup

After installation, access the system at `http://localhost:8000` and log in with the default administrator credentials:
- Email: admin@woods.com
- Password: admin123

**Important:** Change the default password immediately after first login.

## System Architecture

The system follows the MVC (Model-View-Controller) architecture pattern and is organized into the following main modules:

1. **General Ledger (GL)**
   - Chart of Accounts
   - Journal Entries
   - Financial Statements
   - Cost Centers

2. **Inventory Management**
   - Item Master
   - Batch/Lot Tracking
   - Inventory Transactions
   - Stock Valuation

3. **Production & Costing**
   - Bill of Materials
   - Work Orders
   - Costing
   - Production Planning

4. **Purchasing & AP**
   - Supplier Management
   - Purchase Orders
   - Goods Receipt
   - Supplier Payments

5. **Sales & AR**
   - Customer Management
   - Sales Orders
   - Invoicing
   - Customer Payments

6. **Fixed Assets**
   - Asset Register
   - Depreciation
   - Asset Disposal

7. **Payroll**
   - Employee Management
   - Salary Processing
   - Payslip Generation

## Security Features

- Role-based access control (RBAC)
- Secure password hashing
- CSRF protection
- SQL injection prevention
- XSS protection
- Input validation
- Secure session handling
- Audit logging

## Backup & Maintenance

### Database Backup
Regular database backups are essential. Use the following command to create a backup:
```bash
php artisan backup:run
```

### Log Files
Log files are stored in `storage/logs/`. Regular rotation is recommended.

## Support

For technical support or feature requests, please contact the system administrator.

## License

This software is proprietary and confidential. Unauthorized copying, distribution, or use is strictly prohibited. 