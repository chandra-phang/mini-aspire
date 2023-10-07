
# Mini Aspire API

This app will allows authenticated users to go through a loan application. All the loans will be assumed to have a “weekly” repayment frequency.
- Adding cash_balance in users table, so overpaid loan can still be stored in user account
- Adding validation so approver can't approve their own loan

### Run Locally

Clone the project

```bash
  git clone https://github.com/chandra-phang/mini-aspire
```

Go to the project directory

```bash
  cd mini-aspire
```

### Install dependencies

Before running this application please make sure you have these dependencies installed:
```
- PHP
- Composer
- Laravel
- MySQL
```

### Setup DB
After all dependencies are installed, you need to create two new databases based on `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini-aspire
DB_USERNAME=root
DB_PASSWORD=

TESTING_DB_DATABASE=mini-aspire-testing-env
TESTING_DB_USERNAME=root
TESTING_DB_PASSWORD=

```

### Run Migration
```
php artisan migrate
```

### Seed Records
```
php artisan db:seed
```

### Run Locally
```
php artisan serve
```

### Running Test
```
php artisan test
```
