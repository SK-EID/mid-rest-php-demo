# MID-REST PHP DEMO APPLICATION

Demonstrates authentication with Mobile-ID using [mid-rest-php-client](https://github.com/SK-EID/mid-rest-php-client).

## Run in docker

```
docker build -t mid-rest-php-demo .
docker run -it -p 2080:80/tcp --rm mid-rest-php-demo
```

After that open application with your browser at http://localhost:2080

# Test numbers

Demo is initially configured to send requests to a public demo environment hosted by SK ID Solutions AS.
There are [test numbers](https://github.com/SK-EID/MID/wiki/Test-number-for-automated-testing-in-DEMO)
that can be used to simulate different scenarios.
