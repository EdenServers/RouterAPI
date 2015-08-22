FROM phusion/baseimage

#Copy router files
COPY . /opt/RouterAPI

# Install Nginx.
RUN \
  add-apt-repository -y ppa:nginx/stable && \
  apt-get update && \
  apt-get install -y nginx php5-fpm php5-sqlite git && \
  rm -rf /var/lib/apt/lists/* && \
  echo "\ndaemon off;" >> /etc/nginx/nginx.conf && \
  chown -R www-data:www-data /opt/RouterAPI && \
  chown -R www-data:www-data /etc/nginx/sites-enabled

COPY ./docker/nginx-config /etc/nginx/sites-enabled/default

# Define working directory.
WORKDIR /opt/RouterAPI

RUN chmod +x docker/start.sh

# Define default command.
CMD ["./docker/start.sh"]

# Expose ports.
EXPOSE 80
EXPOSE 443