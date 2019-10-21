#!/usr/bin/env bash

docker run --rm -it \
        --publish 8080:80 \
        --name graphql-dev-portal \
        -v $(pwd):/drupal/project/web/sites/default/modules/custom/apigee-drupal-graphql-module \
        micovery/apigee-graphql-demo-portal:latest