FROM php:8.2-apache

#usuário (web) do sistema
ARG WEB_USER=kumon

RUN apt-get update && \
	apt-get install --no-install-recommends --assume-yes libicu-dev libssl-dev libcurl4-openssl-dev && \
	apt-get install -y sudo && \
	apt-get clean && \
	docker-php-ext-install ftp && \
	docker-php-ext-install pdo pdo_mysql && \
	docker-php-ext-enable pdo pdo_mysql && \
	docker-php-ext-install intl  && \
	docker-php-ext-enable intl && \
	rm -rf /var/lib/apt/lists/* && \
	a2enmod rewrite headers ssl socache_shmcb mime && \
	ln -sf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && \
	echo "America/Sao_Paulo" > /etc/timezone && \
	echo "ServerTokens Prod" >> /etc/apache2/apache2.conf && \
	echo "ServerSignature Off" >> /etc/apache2/apache2.conf && \
	echo "TraceEnable Off" >> /etc/apache2/apache2.conf && \
	echo "Header always unset X-Powered-By" >> /etc/apache2/apache2.conf && \
	# Instalar o Node.js
	curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
	apt-get install -y nodejs && \
	# Instalar o pacote web-push
	npm install -g web-push && \
	# Criação do usuário kumon (com senha aleatória)
	WEB_USER_PWD=$(openssl rand -hex 16) && \
	useradd -u 1000 ${WEB_USER} && \
	echo "${WEB_USER}:${WEB_USER_PWD}" | chpasswd && \
	echo ${WEB_USER_PWD} >> /etc/web_user_pwd && \
	echo "${WEB_USER} ALL=(ALL) /bin/chown, /bin/chmod, /usr/sbin/cron" >> /etc/sudoers && \
	# criação da pasta para as chaves de criptografia
	mkdir -p /var/www/cert && \
	chown -R ${WEB_USER}:${WEB_USER} /var/www/cert

# Inicia o servidor Apache
CMD apache2-foreground

USER 1000
