## About test App

Test app for fetching articles from tes [API](https://api-server-test-task.thinkdemo.co.uk/)

## Install

1. `composer install`
2. Configure .env file
3. Add additional variables to .env
   - ARTICLE_API_URL
   - ARTICLE_CLIENT_ID
   - ARTICLE_CLIENT_SECRET
4. `php atisan migrate`
5. Voila!

## Usage
1. Use `php artisan app:sync-articles` for fetching all articles
2. Use `php artisan app:sync-articles {id}` for fetching specific article
3. Running the scheduler ` * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`
4. Running the scheduler locally `php artisan schedule:work` for sync actual articles

