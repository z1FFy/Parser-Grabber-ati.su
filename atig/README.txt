[ ati.su data parser ]
[ contributers : Yegor Chuperka , Denis Kuschenko (ziffyweb@gmail.com) ]

Граббер/Парсер данных с сайта ati.su
Форкнуто из https://bitbucket.org/ychuperka/grabber-ati.su/
Добавлен функционал:
- Сбор id компаний
- Сохранение происходит в data/idlist.json
- Проход по этим id и получение конктактной информации о компании
- Сохранение страницы контактной информации в data/cards/****.html ( Где **** id компании )
- Вывод списка контактов, пагинация

[ Системные требования ]
PHP, phantomjs, js

[ Использование ]
Для начала нужно получить айдишники компаний.
В main.php :
require_once 'loader.php';
$app = new Application('bc8d1b20be02269616068e1a0ca15832');
$api = new \Ychuperka\AtiApi();
$api->parseIdList() . PHP_EOL;

В консоли php main.php
Пойдет процесс сбора всех айди.
В итоге должен создастся файл data/idlist.json
В main.php :
$fileList = $api->getFileList();
$list = array();
$i=0;
foreach ($fileList as $key => $item) {
	foreach ($item as $key2 => $item2) {
		$list[$i]=$key2;
		$i++;
	}
}
echo $api->getItem($list) . PHP_EOL;

Пойдет процесс сбора контактной инфы и сохранение в папку data/***.html

Для просмотра результатов :
Зайти на /compaigns-list.php

[ Установка phantomjs на linux]
Перейдите в каталог SHARE

cd /usr/local/share
Скачайте архив с исходными файлами и выполните команды

Для 32-битной архитектуры

Скачиваем архив

sudo wget https://phantomjs.googlecode.com/files/phantomjs-1.9.0-linux-i686.tar.bz2
Распаковываем архив

sudo tar xjf phantomjs-1.9.0-linux-i686.tar.bz2
Создаём символические ссылки

sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/local/share/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/local/bin/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-i686/bin/phantomjs /usr/bin/phantomjs
Для 64-битной архитектуры

Скачиваем архив

sudo wget https://phantomjs.googlecode.com/files/phantomjs-1.9.0-linux-x86_64.tar.bz2
Распаковываем архив

sudo tar xjf phantomjs-1.9.0-linux-x86_64.tar.bz2
Создаём символические ссылки

sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x8664/bin/phantomjs /usr/local/share/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x8664/bin/phantomjs /usr/local/bin/phantomjs;
sudo ln -s /usr/local/share/phantomjs-1.9.0-linux-x86_64/bin/phantomjs /usr/bin/phantomjs
Чтобы проверить, что всё успешно установилось, введите в консоли:

phantomjs --version
# 1.9.0
