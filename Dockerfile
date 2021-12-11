ARG PHP_VERSION

FROM php:${PHP_VERSION}-cli-alpine as build

ARG CACHETOOL_VERSION
ENV CACHETOOL_VERSION ${CACHETOOL_VERSION}

WORKDIR /opt/cachetool

RUN apk add git
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet --install-dir=/usr/local/bin --filename=composer

RUN composer global config minimum-stability dev
RUN composer global config prefer-stable true
RUN composer global require humbug/box

COPY ./ ./
RUN composer install --prefer-dist
RUN $(composer config home)/vendor/bin/box compile --config=box.json

FROM php:${PHP_VERSION}-cli-alpine as runner

ARG CACHETOOL_VERSION
ARG COMMIT_SHA
ARG BUILD_DATE

LABEL org.opencontainers.image.title="CacheTool"
LABEL org.opencontainers.image.authors="https://github.com/gordalina/cachetool/graphs/contributors"
LABEL org.opencontainers.image.created="${BUILD_DATE}"
LABEL org.opencontainers.image.description="CLI App and library to manage apc & opcache"
LABEL org.opencontainers.image.documentation="https://github.com/gordalina/cachetool"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.ref.name="gordalina/cachetool"
LABEL org.opencontainers.image.revision="${COMMIT_SHA}"
LABEL org.opencontainers.image.source="https://github.com/gordalina/cachetool"
LABEL org.opencontainers.image.url="https://github.com/gordalina/cachetool"
LABEL org.opencontainers.image.vendor="gordalina"
LABEL org.opencontainers.image.version="${CACHETOOL_VERSION}"

COPY --from=build /opt/cachetool/cachetool.phar /usr/local/bin/cachetool

ENTRYPOINT [ "cachetool" ]
