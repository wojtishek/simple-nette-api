FROM dockette/web:php-82

ENV TZ=Europe/Prague
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY --chown=www-data:www-data ./composer.json /srv/
COPY ./.docker/php.ini /etc/php/8.2/fpm/conf.d/99-docker.ini

WORKDIR /srv

COPY ./.docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]