FROM php:8.2-apache

#usuário (web) do sistema
ARG WEB_USER=kumon

# Instalação de dependências
RUN apt-get update && \
	apt-get install --no-install-recommends --assume-yes \
	libicu-dev libssl-dev libcurl4-openssl-dev \
	sudo cron unzip git libzip-dev && \
	apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalação de extensões PHP
RUN	docker-php-ext-configure zip && \
	docker-php-ext-install zip ftp pdo pdo_mysql intl

# Ativação de módulos do Apache
RUN a2enmod rewrite headers ssl socache_shmcb mime

# Configuração de timezone
RUN ln -sf /usr/share/zoneinfo/America/Sao_Paulo /etc/localtime && \
	echo "America/Sao_Paulo" > /etc/timezone

# Segurança do Apache
RUN	echo "ServerTokens Prod" >> /etc/apache2/apache2.conf && \
	echo "ServerSignature Off" >> /etc/apache2/apache2.conf && \
	echo "TraceEnable Off" >> /etc/apache2/apache2.conf && \
	echo "Header always unset X-Powered-By" >> /etc/apache2/apache2.conf

# Criação do usuário kumon (com senha aleatória)
RUN	WEB_USER_PWD=$(openssl rand -hex 16) && \
	useradd -u 1000 ${WEB_USER} && \
	echo "${WEB_USER}:${WEB_USER_PWD}" | chpasswd && \
	echo ${WEB_USER_PWD} >> /etc/web_user_pwd && \
	#echo "${WEB_USER} ALL=(ALL) /bin/chown, /bin/chmod, /usr/sbin/cron" >> /etc/sudoers && \
	echo "${WEB_USER} ALL=(ALL)" >> /etc/sudoers && \
	# criação da pasta para as chaves de criptografia
	mkdir -p /var/www/cert && \
	chown -R ${WEB_USER}:${WEB_USER} /var/www/cert && \
	# pasta da aplicação para o usuário do sistema
	chown -R ${WEB_USER}:${WEB_USER} /var/www/html

# tarefas agendadas no CRON
COPY ./infra/php/cronjob /etc/cron.d/cronjob
VOLUME /var/log/cron

# Adiciona o Composer no PHP
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define um diretório para o projeto
WORKDIR /var/www/html

# Copia o composer.json e instala dependências
COPY composer.json /var/www/html/
RUN composer install --no-interaction --prefer-dist --optimize-autoloader && \
    composer require minishlink/web-push

# script de inicialização (executado após UP do contêiner)
COPY ./infra/php/entrypoint.sh /usr/local/bin/
RUN tr -d '\r' < /usr/local/bin/entrypoint.sh > /tmp/entrypoint.sh && \
    mv /tmp/entrypoint.sh /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Inicia o servidor Apache
CMD service cron start && apache2-foreground

# O usuário do sistema assume daqui
USER 1000
