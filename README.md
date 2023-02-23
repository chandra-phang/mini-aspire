
# Mini Aspire API

This app that allows authenticated users to go through a loan application. All the loans will be assumed to have a “weekly” repayment frequency.


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
After all dependencies are installed, you need to create new database based on `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini-aspire
DB_USERNAME=root
DB_PASSWORD=
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
