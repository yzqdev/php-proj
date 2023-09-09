## How to run this Task API
1. Install docker desktop
2. Adjust the .env file accordingly(database credentials can be found in the docker-compose.yaml file)
3. Go to the root directory of the backend project and run `docker compose up --build`
4. Run `docker-compose exec app php artisan migrate:fresh` to initialize the database
5. Run the db seeder in terminal `docker-compose exec app php artisan db:seed`

## Generate the swagger API
- Run the following command in terminal on the frontend folder `php artisan l5-swagger:generate`
- Go to `http://localhost/api/documentation#`



