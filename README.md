# File api

#### Prerequisites

You will need the following things properly installed on your computer:

* [Vagrant](https://www.vagrantup.com/)
* [VirtualBox](https://www.virtualbox.org/) or other VMs

#### Installation

* `git clone` this repository
* `vagrant up` in the repository directory
* `vagrant ssh` connect to VM
* may need `compser install` in `/vagrant/service` folder
* `php -S 0.0.0.0:8080 -t /vagrant/service/web` run service (it is available in host: localhost:3000/api/v1/file)

#### Running Tests

In a VM follow:
* `cd /vagrant/service/test`
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
