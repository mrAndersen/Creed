# Лендинг для фильма Creed

Лендинг сделан как обычный проект composer - подробнее в гугле.

## Для работы необходимо

    1. php >= 5.5
    2. composer.phar - https://getcomposer.org

## Установка

    1. cd /var/www/ (или до директории где будет располагаться проект)
    2. В директорию где будет располагаться проект - скачать composer.phar
    3. git clone https://github.com/mrAndersen/Creed.git
    4. php composer.phar install -d=Creed
    5. Прописать реврайт на web/app.php, пример есть в проекте для nginx - /rewrite.example.conf
    6. А так же не забыть прописать post_max_size у php на уровне хотя бы 50М и такое же значение для веб сервера