# File api

#### Prerequisites

You will need the following things properly installed on your computer:

* [Vagrant](https://www.vagrantup.com/)
* [VirtualBox](https://www.virtualbox.org/) or other VMs

#### Installation

* `git clone` this repository
* `compser install` in `file-api/service` folder
* `vagrant up` in the repository folder (download ubunty/trusty64 box)
* `vagrant ssh` connect to VM
* `php -S 0.0.0.0:8080 -t /vagrant/service/web` run service (it is available in host: localhost:3000/api/v1/file)

#### Running Tests

In the VM follow:
* `cd /vagrant/service/test`
* `../vendor/bin/codecept build`
* `../vendor/bin/codecept run unit` run the unit tests
* `../vendor/bin/codecept run api` run the functional/acceptance tests

#### Api documentation

* File metadata: send via X-File-Metadata header in response.
  JSON:
  {
    'Name': 'file name',
    'Size': uncompress file size in bytes,
    'Created': date time created file in ISO8601,
    'Modified': date time modififed file in ISO8601,
    'Owner': owner file,
    'Type': mime type file,
    'Etag': file hash,    
  }
* For authorization in request use bearer token:
  'Authorization: Bearer test1-token'

* /api/v1/file - main resource
* OPTIONS
* GET - return list file of user or file, if specified the file name (or 'name' parameter in uri)
  Return:
  - 200 and file, file metadata in http header (X-File-Metadata)
  - 200 and list file metadata in http header (X-File-Metadata)
  - 304 (request containt If-None-Match: Etag)
  - 403
  - 404
* HEAD - return metadata in http header
  Return:
  - 404
  - 403
  - 200 same than GET
* PUT - create/overwrite file from body request (/<file name> or ?name=<file name>)
  Return:
  - 200 if overwrite, and X-File-Metadata,
  - 201 and X-File-Metadata,
  - 400 if miss file name,
  - 403
* PATH - overwrite part file if specified parameter 'position'
  Return:
  - same that PUT
  - 400 if 'position' more than file size
  - 400 if file not exist, path doesn't create file
* DELETE - delte file and file metadata
  Return:
  - 200
  - 404
  - 403


Support GZIP request and response.
GET maintains cache with Etag.

#### Не реализованный функционал:
* Работа с PHP экосистемой
  - Использование функционала последних версий php(5.5 - 7.0) - пассивно используется возможность загрузки файлов больше 2 Гб, появившаяся в PHP 5.6.
  Переход на PHP 7 добавит производительности без использования HHVM, но потребует перевода используемых инструментов на PHP 7 (поэтому в этом задании не используется т.к. Yii 2.0 ещё не перевели на PHP 7)

* Оптимизация
  - Оптимизация загрузки и отдачи файлов(в первую очередь используемой памяти).
  Сделано: в коде уже используется временный поток php://temp, он позволяет не держать в памяти весь отдаваймый/загружаемый файл
  Что можно ещё добавить: загружать/отдавать файлы кусками

  - Работа с оооочень большими файлами. PHP 5.6 поддерживает загрузку отдачу файлов больше 2 ГБ., надо оптимизировать работы по упаковке/распаковке файлов из GZIP через блочное чтение.

  - Разруливание конкурентного доступа к файлу. Как сделать: можно сделать через консультативные блокировки

* Безопасность
  - Лимиты на размер загружаемых файлов. Как сделать: вычитывать из входящего потока блоками и обрывать при превышении лимита.

  - HTTPS. Выполняю требования по использованию встроенного сервера PHP, он не поддерживает SSL, но можно поднять stunel3(4) и через него пропустить запросы.

  - Ограничения на количество запросов к API для клиента и/или ip адреса. Как сделать: в Yii 2.0 есть поддержка ограничения запросов к api, но надо использовать оперативное хранилище для поддержки работы (поэтому не реализовано, хотел обойтись без какой либо БД)

  - Квоты с местом и лимит для пользователя. ---

  - HTTP заголовки в ответах, для безопасного вызова API со сторонней веб-страницы в браузере. Как сделать: HSTS, проще всего на уровне конфиугарции Web-сервера, здесь используется втроенный PHP сервер без HTTPS, поэтому не реализовано.
