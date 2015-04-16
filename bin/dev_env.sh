#!/bin/sh

# run dev environment actions

set -e

SCRIPT_HOME="$( cd "$( dirname "$0" )" && pwd )"

cd $SCRIPT_HOME/..

sqlScriptLocation = "/databases/"

case "$1" in
	start)
		vagrant up
		vagrant ssh -c "cd /vagrant && sudo fig up -d"
		vagrant ssh -c "cd /vagrant && sudo fig ps"
		;;
	stop)
		vagrant ssh -c "cd /vagrant && sudo fig stop"
		vagrant halt
		;;
	ps)
		vagrant ssh -c "cd /vagrant && sudo fig ps"
		;;
    rebuild)
        vagrant ssh -c "cd /vagrant && sudo fig stop $2 && sudo fig rm $2 && sudo fig build $2"
        ;;
    composer-update)
        vagrant ssh -c "cd /vagrant && sudo fig run composer update"
		;;
    composer-install)
        vagrant ssh -c "cd /vagrant && sudo fig run composer install"
		;;
    run-server)
        vagrant ssh -c "cd /vagrant && sudo fig run phpCli"
		;;
	database-init)
        vagrant ssh -c "cd /vagrant && sudo fig run mysqlClient sh -c 'chmod -R 777 /databases &&  exec mysql -hmysql -P3306 -uroot -padminuser < /databases/init.sql'$2"
		;;
	database-teardown)
        vagrant ssh -c "cd /vagrant && sudo fig run mysqlClient sh -c 'chmod -R 777 /databases &&  exec mysql -hmysql -P3306 -uroot -padminuser < /databases/tearDown.sql'$2"
		;;
	*)
		echo "options are: start, stop, run-server, ps [container status], rebuild <containerName>, composer-update, composer-install"
		;;
esac

