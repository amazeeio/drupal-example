ARG CLI_IMAGE
FROM ${CLI_IMAGE} as cli

FROM testlagoon/nginx-drupal:multiarch

COPY --from=cli /app /app

# Define where the Drupal Root is located
ENV WEBROOT=web
