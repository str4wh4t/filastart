* Installation
You better run this code in docker, you can easyly install all depedencies with Dockerfile.
Build the image and container via : docker compose up -d --build
After your container ready, you can login into your App container and prepare the project.
1.  composer install
2.  npm install
3.  php artisan migrate
4.  php artisan db:seed
5.  php artisan shield:generate --all
6.  php artisan storage:link


* TODO
-   ~add confirmation to reload on page general~
-   ace editor not working on spa
-   global search not working on spa

