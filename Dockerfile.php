ARG CLI_IMAGE
FROM ${CLI_IMAGE:-builder} as builder

FROM amazeeio/centos7-php7.0-drupal

COPY --from=builder /app /app

ENV DRUPALCON=TRUE