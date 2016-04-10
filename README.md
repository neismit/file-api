# File api

#### Prerequisites

You will need the following things properly installed on your computer:

* [Vagrant](https://www.vagrantup.com/)
* [VirtualBox](https://www.virtualbox.org/) or other VMs

#### Installation

* `git clone` this repository
* `vagrant up` in the repository directory
* `vagrant ssh` connect to VM
* `php -S 0.0.0.0:8080 -t /vagrant/service/web` run service

#### Running Tests

In a VM follow:
* `cd /vagrant/service/test`
* `../vendor/bin/codecept run unit` run the unit tests
* `../vendor/bin/codecept run api` run the functional tests
