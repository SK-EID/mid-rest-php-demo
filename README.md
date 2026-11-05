# MID REST php demo application

> [!WARNING]
> **Security Notice!** Critical vulnerabilities CVE-2026-109618 and CVE-2026-109619 have been identified in PHP and may allow unauthenticated remote code execution in applications serving web requests. These vulnerabilities are in the PHP runtime and not in the Mobile-ID PHP client library or this demo application.
>
> As of October 21, 2026, the vulnerabilities are fixed in PHP 8.2.35, 8.3.36, 8.4.27 and 8.5.12. This demo requires PHP 8.4.27 or later; its Docker image is based on a pinned PHP 8.4.27 image. If you use this demo as a starting point for your own application, make sure it runs on a patched and supported PHP version and apply all available security updates.

This application demonstrates how to use the [mid-rest-php-client](https://github.com/SK-EID/mid-rest-php-client) in a symfony application to authenticate and authorize users.

It runs on PHP 8.4 (8.4.27 or newer, which includes the PHP2Shell fix) with Symfony 7.4 LTS and mid-rest-php-client 1.3.

## Building and running with Docker

### Requirements

- You must have Docker with Docker Compose installed in order to use the application.

### Building and running

Issue the next command in the application folder.
It builds the application image (based on the official `php:8.4-apache` image) and starts it together with a MySQL database.

`docker compose up --build`

The database tables are created on startup. The application should start up in about 30 seconds.

### Accessing the application

For accessing the application go to the following url in your browser

[http://localhost:8001/login](http://localhost:8001/login)

Now you can try to log in to the application using [test numbers](https://github.com/SK-EID/MID/wiki/Test-number-for-automated-testing-in-DEMO).

## Configuring Mobile-ID

By default the application uses the SK MID demo environment. The settings can be changed with environment variables
(in `compose.yaml` or in a `.env.local` file):

| Variable                          | Default                                | Description                                                            |
| --------------------------------- | -------------------------------------- | ---------------------------------------------------------------------- |
| `MID_HOST_URL`                    | `https://tsp.demo.sk.ee/mid-api`       | MID API address                                                        |
| `MID_RELYING_PARTY_UUID`          | `00000000-0000-0000-0000-000000000000` | Relying Party UUID                                                     |
| `MID_RELYING_PARTY_NAME`          | `DEMO`                                 | Relying Party name                                                     |
| `MID_SSL_PINNED_PUBLIC_KEYS`      | hash of the tsp.demo.sk.ee key         | `sha256//...` hashes of the MID API SSL public keys, separated by `;`  |
| `MID_TRUSTED_CA_CERTIFICATES_DIR` | `src/trusted_certificates`             | Folder of CA certificates that issue the users' Mobile-ID certificates |

The SSL public key hash of the MID API host and the trusted CA certificates are mandatory since mid-rest-php-client 1.1.
See the [mid-rest-php-client README](https://github.com/SK-EID/mid-rest-php-client#checking-if-mid-api-host-is-trusted)
for how to calculate the hashes. `src/trusted_certificates` contains the TEST CA certificates of the demo environment;
replace them with the production CA certificates when going live.

## Running the application without Docker

### Requirements

- php >= 8.4 (8.4.27 or newer), including curl, gmp, intl, openssl and pdo_mysql extensions
- [composer](https://getcomposer.org/)
- [symfony cli tool](https://symfony.com/download)
- mysql server installed
  (or run it from Docker: `docker run -e MYSQL_ROOT_PASSWORD=my-secret-pw -p 3306:3306 mysql:8.4`)

### Installing dependencies

`composer install`

### Database migration

- create database mid_rest_demo
  - `CREATE DATABASE mid_rest_demo;`
- create user mid_rest_demo, with password mid_rest_demo
  - `CREATE USER 'mid_rest_demo' IDENTIFIED BY 'mid_rest_demo';`
- grant the new user all privileges on the database
  - `GRANT ALL PRIVILEGES ON mid_rest_demo.* TO 'mid_rest_demo';`
- Run migration script

  `php bin/console --no-interaction doctrine:migrations:migrate`

### Configuring the application

- You might need to change the server name in DATABASE_URL constant in the .env file (or override it in .env.local) to match your sql server host

### Running the application

Start Symfony in the project folder:

`symfony serve`

### Accessing the application

Access the application from [localhost:8000](http://localhost:8000)

Now you can try to log in to the application using test numbers.
