# Первый проект на PHP & Laravel с применением API

Мой первый проект, написанный на PHP & Laravel с применением API стороннего сервиса.
Используется API с сервиса https://kontur.ru , информация, полученная из него, формируется в html отчёт.

## Скачивание проекта

Для скачивания проекта необходимо прописать в консоли

```sh
$ git clone https://github.com/BondusS/Kontur-API-Report.git
```

## Создание файла окружения

Создать в папке проекта файл .env (путём копирования .env.example)

## Создание зависимостей

В консоли в папке проекта необходимо прописать

```sh
$ composer install
``` 

## Генерация ключа

В консоли в папке проекта необходимо прописать

```sh
$ php artisan key:generate
```

## Запуск проекта

Для запуска проекта необходимо в консоли в папке проекта и прописать

```sh
$ php artisan serve
```