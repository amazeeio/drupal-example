ARG CLI_IMAGE
FROM ${CLI_IMAGE:-builder} as builder

FROM amazeeiolagoon/develop-php:7.1-fpm

COPY --from=builder /app /app
