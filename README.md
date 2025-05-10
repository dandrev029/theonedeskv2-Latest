# TheOneDesk v2

A ticket management system built with Laravel and Vue.js.

## Deployment with Coolify

### Prerequisites

- Coolify server set up and running
- Git repository connected to Coolify

### Deployment Steps

1. **Connect your repository to Coolify**
   - Add your Git repository to Coolify
   - Select the branch you want to deploy (e.g., `main`)

2. **Configure the deployment**
   - Select "Docker Compose" as the deployment type
   - Use the default `docker-compose.yml` file
   - Set the following environment variables in Coolify:
     - `APP_KEY`: Generate a new Laravel application key
     - `DB_DATABASE`: Your database name
     - `DB_USERNAME`: Your database username
     - `DB_PASSWORD`: Your database password
     - `PUSHER_APP_ID`: Your Pusher app ID (if using Pusher)
     - `PUSHER_APP_KEY`: Your Pusher app key
     - `PUSHER_APP_SECRET`: Your Pusher app secret
     - `PUSHER_APP_CLUSTER`: Your Pusher app cluster

3. **Deploy the application**
   - Click "Deploy" in Coolify
   - Wait for the deployment to complete

4. **Post-deployment setup**
   - Run database migrations:
     ```
     docker-compose exec app php artisan migrate --force
     ```
   - Seed the database (if needed):
     ```
     docker-compose exec app php artisan db:seed
     ```
   - Create a storage link:
     ```
     docker-compose exec app php artisan storage:link
     ```

### Manual Deployment

If you prefer to deploy manually, you can use the provided `deploy.sh` script:

1. Copy `.env.docker` to `.env` and update the values
2. Make the deployment script executable:
   ```
   chmod +x deploy.sh
   ```
3. Run the deployment script:
   ```
   ./deploy.sh
   ```

## Environment Variables

The following environment variables are required for the application to function properly:

- `APP_KEY`: Laravel application key
- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password
- `REDIS_HOST`: Redis host
- `REDIS_PORT`: Redis port
- `PUSHER_APP_ID`: Pusher app ID
- `PUSHER_APP_KEY`: Pusher app key
- `PUSHER_APP_SECRET`: Pusher app secret
- `PUSHER_APP_CLUSTER`: Pusher app cluster

## Maintenance

### Queue Worker

The application uses Laravel's queue system for background processing. The queue worker is configured to run as a separate container in Docker Compose.

### Cache

The application uses Redis for caching. To clear the cache:

```
docker-compose exec app php artisan cache:clear
```

### Updates

To update the application:

1. Pull the latest changes from the repository
2. Run the deployment script again:
   ```
   ./deploy.sh
   ```

## Troubleshooting

### Database Connection Issues

If you encounter database connection issues, check the following:

1. Ensure the database container is running:
   ```
   docker-compose ps
   ```
2. Check the database credentials in the `.env` file
3. Try connecting to the database manually:
   ```
   docker-compose exec db mysql -u root -p
   ```

### Queue Worker Issues

If background jobs are not being processed:

1. Check if the queue worker container is running:
   ```
   docker-compose ps
   ```
2. Restart the queue worker:
   ```
   docker-compose restart queue
   ```
3. Check the queue worker logs:
   ```
   docker-compose logs queue
   ```
