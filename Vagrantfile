# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "phusion/ubuntu-14.04-amd64"

  #ports

  #mysqld
  config.vm.network "forwarded_port", guest: 3306, host: 3306

  #shared dir
  config.vm.synced_folder ".", "/vagrant"

  config.vm.provision "puppet" do |puppet|
     puppet.manifests_path = "puppet/manifests"
     puppet.manifest_file  = "app.pp"
     puppet.module_path = 'puppet/modules'
  end

end
