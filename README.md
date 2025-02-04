# kumon
Desempenho Kumon

## Criando as redes externas no ambiente docker ##
```
docker network create proxy-network
docker network create db-network
```

##  Gerando as chaves VAPID para notificações PUSH ##

Acesse o shell do conteiner e vá para a pasta do certificado
docker exec -it kumon bash
cd /var/www/html/push


Crie as chaves
web-push generate-vapid-keys > /var/www/html/push/vapid_keys.env

```
=======================================

Public Key:
BKRC...

Private Key:
Wyd...

=======================================
```

Copie os valores das chaves e cole-os em suas respectivas posições no arquivo .env.

As chaves são permanentes.