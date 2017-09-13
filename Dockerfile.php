ARG BUILDER_IMAGE
FROM ${BUILDER_IMAGE:-builder} as builder

FROM amazeeio/centos7-php7.0-drupal

COPY --from=builder /app /app