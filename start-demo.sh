#!/bin/bash

# Check if docker is installed
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed. Please install Docker and Docker Compose to use this script."
    exit 1
fi

echo "🚀 Starting OsintWeb Demo..."
echo "This will build the container, set up the database, and seed mock data."
echo "This process may take a few minutes the first time."

docker-compose up --build -d

echo ""
echo "⏳ Waiting for services to initialize (database migrations & seeding)..."
echo "You can follow the logs with: docker-compose logs -f app"
echo ""
echo "Once ready, the application will be available at: http://localhost:8000"
