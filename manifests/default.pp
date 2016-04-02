package { "software-properties-common":
  provider => "apt",
  ensure => present,
}
package { "python-software-properties":
  provider => "apt",
  ensure => present,
}

include ::php
