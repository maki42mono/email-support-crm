# CRM запросов в почту

Это движок, который парсит входящие письма, формирует из ниах запросы и отправляет автоответы.
Есть визуализация всех входящих, а также запросов и ответов

## Требования

- php 7.4 и выше
- apache / nginx
- composer
- docker

## Что доделать
- парсинг почты по крону

## Как запустить

Настройте корень сайта на `public/`

Установите пакеты

```sh
composer install
```
Запустите docker (с .env.local или .env.prod)

```sh
docker-compose --env-file .env up -d
```


Запустите обработчики очередей

```sh
symfony run -d --watch=src symfony console messenger:consume async async_delayed
```


Добавьте в бд логин и пароль для админа:
[Создание пароля для администратора][PlDb]


Спарсите письма (++переделать по крону)
```sh
symfony console app:
```

Откройте в брайзере панель администратора `ваш_домен/admin`

Наслаждайтесь



[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

[PlDb]: <https://symfony.com/doc/current/the-fast-track/ru/15-security.html#sozdanie-parola-dla-administratora>
 
