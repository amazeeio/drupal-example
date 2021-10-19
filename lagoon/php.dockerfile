ARG CLI_IMAGE
FROM ${CLI_IMAGE} as cli

FROM testlagoon/php-7.4-fpm:multiarch

COPY --from=cli /app /app
