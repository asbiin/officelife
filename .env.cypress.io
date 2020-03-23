# THIS IS THE ENV FILE THAT IS USED TO RUN ON GITHUB ACTIONS
# IN ORDER TO POPULATE THE TESTS RECORDED ON CYPRESS.IO
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:dLfTJCF1l3UWtwEW6HKEF90qT9/tT4qQDdDXFoxl4GI=
APP_DEBUG=false
APP_URL=http://localhost:8000

LOG_CHANNEL=stack

DB_CONNECTION=sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1

MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# API key for geolocation services
# We use LocationIQ (https://locationiq.com/) to translate addresses to
# latitude/longitude coordinates. We could use Google instead but we don't
# want to give anything to Google, ever.
# LocationIQ offers 10,000 free requests per day.
LOCATION_IQ_API_KEY=

# API key for maps displays
# We use Mapbox (https://mapbox.com/) to display static maps.
# Mapbox has a generous 50 000 free requests per month.
# A username is also required, this is the one used upon account creation.
MAPBOX_API_KEY=
MAPBOX_USERNAME=