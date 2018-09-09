# Speech2Text 

A simple program to convert audio speech to text. 

### How to setup

On local:

1. Add `www.speech2text.com` to your `/etc/hosts`
2. Bring up docker-compose network & setup

    ```bash
    # Bring up network
    docker-compose up -d
    
    # Install dependencies
    composer install
    
    # Move inside app container
    docker-compose exec app sh
    
    # Create database schema
    ./bin/console --no-interaction doctrine:migrations:migrate
    ```

3. Visit `http://www.speech2text.com`.
