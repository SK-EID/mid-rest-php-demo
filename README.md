# MID REST php demo application

This application demonstrates how to use the [mid-rest-php-client](https://github.com/SK-EID/mid-rest-php-client) in a symfony application to authenticate and authorize users.

## Building and running with docker

### Requirements

- You must have docker installed in order to use the application.

### Building the image

This step needs to be runned only on the initial build of the application

First build the docker image, by issuing the next command in the application folder

`docker build -t mid-rest-php-demo:1.0 ./`

### Running the application

For running the previously built image issue the following command

`docker run -p 8001:8000 --env-file docker.env -it mid-rest-php-demo:1.0`

The application should start up in about 30 seconds

### Accessing the application

For accessing the application go to the following url in your browser

[http://localhost:8001/login](http://localhost:8001/login)

Now you can try to log in to the application

- Enter the phone number without country code prefix.


## Running the application without Docker

### Requirements

- php >= 7.3, including curl, mysql, dom extensions
- [symfony cli tool](https://symfony.com/download)
- mysql server installed

### Database migration
- create database mid_rest_demo
    - `CREATE DATABASE mid_rest_demo;`
- create user mid_rest_demo, with password mid_rest_demo
    - `CREATE USER 'mid_rest_demo' IDENTIFIED BY 'mid_rest_demo;`
- grant the new user all privileges on the database
    - `GRANT ALL PRIVILEGES ON mid_rest_demo.* TO 'mid_rest_demo';`
- Run migration scripts

    1. `php bin/console make:migrate`
    1. `php bin/console doctrine:migrations:migrate`

### Configuring the application

- Change the DB url in the .env file to match your sql server

### Running the application
1. run `symfony serve` in the project folder

### Accessing the aplication

Access the application from [localhost:8000](localhost:8000)
