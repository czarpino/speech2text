# Speech2Text 

A simple program to convert audio speech to text. 

### How to setup

```bash
docker-compose up -d
composer install
docker-composer exec app bash
./bin/console --no-interaction doctrine:migrations:migrate
```

Visit `http://localhost:8000/` in your localhost.
