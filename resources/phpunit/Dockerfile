FROM nubs/phpunit

MAINTAINER Tom Wilderspin [tom@flashtalking.com]

USER root

RUN sed -i 's/;extension=mysql.so/extension=mysql.so/g' /etc/php/php.ini
RUN sed -i 's/;extension=pdo_mysql.so/extension=pdo_mysql.so/g' /etc/php/php.ini

ENTRYPOINT ["phpunit","--testsuite"]
