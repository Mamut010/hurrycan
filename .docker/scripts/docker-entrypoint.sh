#!/bin/bash
cd ../db

echo "Creating database schema..."
php schema.php

echo "Seeding database..."
php seed.php

#End with running the original command
exec /usr/sbin/apache2ctl -D FOREGROUND