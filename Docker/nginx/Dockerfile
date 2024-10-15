#FROM nginx
#RUN  rm /etc/nginx/conf.d/default.conf
#RUN rm /etc/nginx/nginx.conf
#RUN mkdir -p "/u/apps/my_app/{logs,tmp}"
#RUN mkdir -p "/etc/nginx/sites-enabled"
#COPY /Docker/nginx/php-docker.local.conf /etc/nginx/sites-available/php-docker.local.conf
#COPY /Docker/nginx/nginx.conf /etc/nginx/nginx.conf
#RUN ln -s /etc/nginx/sites-available/php-docker.local.conf /etc/nginx/sites-enabled/php-docker.local.conf
#CMD ["nginx", "-g", "daemon off;"]

FROM nginx
RUN  rm /etc/nginx/conf.d/default.conf
COPY /Docker/nginx/custom.conf /etc/nginx/conf.d/custom.conf