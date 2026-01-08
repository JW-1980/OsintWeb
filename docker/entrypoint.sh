#!/bin/bash
set -e

# Wait for DB to be ready
echo "Waiting for database..."
until mysql -h db -u osintuser -ppassword -e 'select 1' > /dev/null 2>&1; do
  echo "Database is unavailable - sleeping"
  sleep 5
done
echo "Database is up!"

# Setup env if missing
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    # Update DB config in .env to match docker-compose
    sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/' .env
    sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/' .env
    sed -i 's/DB_USERNAME=root/DB_USERNAME=osintuser/' .env
fi

# Generate key
php artisan key:generate

# Migrate and Seed
echo "Running migrations and seeders..."
php artisan migrate:fresh --seed --force

echo "Starting server..."
php artisan serve --host=0.0.0.0 --port=8000
