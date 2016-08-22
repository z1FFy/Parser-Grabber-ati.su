### Граббер/Парсер данных с сайта ati.su
 - Рабочую версию веб морды для просмотра результатов парсинга можно смотреть тут - z1web.ru/projects-ati 
 - Форкнуто из https://bitbucket.org/ychuperka/grabber-ati.su/

##### Функционал:
- Сбор id компаний
- Сохранение списка происходит в data/idlist.json
- Проход по этим id и получение конктактной информации о компании
- Сохранение страницы контактной информации в data/cards
- Вывод списка компаний,их контакты,пагинация

##### Зависимости
PHP, phantomjs

##### Использование
Данные для авторизации в lib/phantom_scripts/auth.js 41 и 42 строка (логин и пароль в ati.su)

Для начала нужно получить айдишники компаний.

*В main.php :*
>echo $api->parseIdList() . PHP_EOL;

*В консоли*
>php main.php

Пойдет процесс сбора всех айди.

В итоге должен создастся файл *data/idlist.json*

Для парсинга контактных данных из карточек компаний пропишем main.php :

> $api->saveItemsFromList();

Пойдет процесс сбора контактной инфы и сохранение в папку data/***.html

Для просмотра результатов :
Зайти на /compaigns-list.html

##### Установка phantomjs на linux

Перейдите в каталог SHARE

>cd /usr/local/share

Скачайте архив с исходными файлами и выполните команды

###### Для 32-битной архитектуры

Скачиваем архив

> sudo wget https://phantomjs.googlecode.com/files/phantomjs-1.9.0-linux-i686.tar.bz2

Распаковываем архив

> sudo tar xjf phantomjs-1.9.0-linux-i686.tar.bz2

Создаём символические ссылки

>sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/local/share/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/local/bin/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/bin/phantomjs

###### Для 64-битной архитектуры

Скачиваем архив

> sudo wget https://phantomjs.googlecode.com/files/phantomjs-1.9.0-linux-x86_64.tar.bz2

Распаковываем архив

> sudo tar xjf phantomjs-1.9.0-linux-x86_64.tar.bz2

Создаём символические ссылки

>sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x8664/bin/phantomjs /usr/local/share/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x8664/bin/phantomjs /usr/local/bin/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x86_64/bin/phantomjs /usr/bin/phantomjs

Чтобы проверить, что всё успешно установилось, введите в консоли:

>phantomjs --version

Должна показаться версия # 1.9.0 или др.

##### Contributers :
* Yegor Chuperka
* Denis Kuschenko (ziffyweb@gmail.com)

