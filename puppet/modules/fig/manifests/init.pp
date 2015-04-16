# Public: Install Fig
#
class fig {

  package { ['docker.io', 'curl']:
    ensure => present
  }

  file { '/usr/local/bin/docker':
    ensure => 'link',
    target => '/usr/bin/docker.io',
    require => Package['docker.io'],
    notify => Exec['install-fig']
  }

  exec { 'install-fig':
      require => Package['curl', 'docker.io'],
      command => "curl -L https://github.com/docker/fig/releases/download/1.0.0/fig-${::kernel}-${::hardwaremodel} > /usr/local/bin/fig",
      notify  => Exec['fix-fig-permissions']
  }

  exec { 'fix-fig-permissions':
    command     => "chmod a+x /usr/local/bin/fig",
    require     => Exec['install-fig']
  }

}