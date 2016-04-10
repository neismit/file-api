package { "software-properties-common":
  provider => "apt",
  ensure => present,
}
package { "python-software-properties":
  provider => "apt",
  ensure => present,
}

include ::php

package { "php5-curl":
  provider => "apt",
  ensure => present,
  require => Class["::php"],
}

exec { 'composer install package':
  command => "composer install",
  cwd => "/vagrant/service",
  require => Class["::php"],
}
