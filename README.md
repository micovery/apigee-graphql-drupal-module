## Apigee Drupal GraphQL Module

This repo contains a Drupal 8 module. The module allows you to embed the GraphQL Playground into a Drupal page.

The playground that ships with this module is a slightly modified version of the [original GraphQL Playground](https://github.com/prisma-labs/graphql-playground) by [Prisma](https://www.prisma.io/).

Go over to the [apigee-graphql-demo-playground](https://github.com/micovery/apigee-graphql-demo-playground) repo to see the source of the modified playground.

### How to install it (with composer and drush)


1. First install the module:
    ```bash
    $ composer config repositories.repo-name vcs git@github.com:micovery/apigee-graphql-drupal-module.git
    $ composer require micovery/apigee-graphql-drupal-module:dev-master
    ```

2. Then enable it
    ```bash
    $ drush en apigee_drupal8_graphql
    ```

### Not Google Product Clause

This is not an officially supported Google product.