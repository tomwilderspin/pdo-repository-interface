FROM php:5.6-cli

MAINTAINER Tom Wilderspin [tom@flashtalking.com]

RUN apt-get update -y && apt-get install -y \
    php5-pdo \

