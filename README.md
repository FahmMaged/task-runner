# Valeo Task

## Fahmy Maged

#### Task Runner

## Project Branches

    master

## Backend

    Laravel/Voyager

## Fontend

     Laravel Blade
     
## Auth Test Account

   email: admin@admin.com
   password: password

## Steps to start

    1- rename .env.example to .env
    2- import count.sql (attached to the email)to your database then update these at .env file

    DB_DATABASE=count
    DB_USERNAME=root

    3- open terminal at project folder then run "composer install"
    4- then run "php artisan migrate"
    5- then run "php artisan key:generate"
    6- then run "php artisan serve" then go to "http://127.0.0.1:8000/admin" (if port 8000 is empty)
    7- Login credentials:
        email: admin@admin.com
        password: password
    8- if you didn't want the tasks to run till you add all the tasks then set QUEUE_CONNECTION at .env file to "database" instead of "sync".
    9- if you change QUEUE_CONNECTION to "database", then after adding projects and assign tasks to it run "php artisan queue:work".

    NOTE: projects and project details pages will be refresh every second if there is any job at the jobs table.

    Numbers refers to statuses:
    0 => Task failed
    1 => Task success
    2 => Task is not running yet
    3 => Task is running
    5 => No tasks yet 