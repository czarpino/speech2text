FROM czarpino/symfony:4.1

RUN apk upgrade -U \
 && apk add ca-certificates ffmpeg libva-intel-driver \
 && rm -rf /var/cache/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


