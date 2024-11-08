#!/bin/bash
cd ../db

echo "Creating database schema..."
php schema.php
if [ $? -ne 0 ]; then
    echo "Failed to create database schema"
    exit
else
    echo "Successfully creating database schema"
fi

echo "Seeding database..."
php seed.php
if [ $? -ne 0 ]; then
    echo "Failed to seed database"
else
    echo "Successfully seeding database"
fi