# Project BE orders

## Initial considerations

I have made some small considerations before and during the developement process, this is a comprehensive list:

1. The API supports two user roles to make it more realistic.
2. ADMIN and USER have different permissions.
3. Authentication is required for any action on the Orders API.

## Technical choices

In this section you will find some short explanation on the technical choices i have made to setup the project

### Laravel

Chosen for its elegance, scalability, and strong community. It provides a solid architecture and powerful tools for database management, authentication, and job queues.

### Sanctum

A lightweight API authentication solution based on tokens, perfect for SPAs and mobile apps, ensuring security and simplicity.
is inspired by GitHub and other applications which issue "personal access tokens"

### Scout (Meilisearch)

Fast and efficient full-text search engine. Meilisearch enhances user experience with rapid, typo-tolerant searches.
Scout can also be configured with other full-text search engines, such as Algolia, Typesense, MySql and Postgres with very little effort

### Docker

Ensures a consistent and portable development environment, simplifying dependency management and deployment.

## Docs

In the docs folder you will find some more detailed documentation

## Starting the project

```bash
docker compose up -d
docker compose exec app bash
./docker/app/docker-start-dev.sh
```

## Launching the tests

```bash
docker compose up -d
docker compose exec app bash
./docker/app/docker-start-test.sh
```
