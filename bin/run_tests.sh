#!/bin/sh

set -e

# run phpunit tests


SCRIPT_HOME="$( cd "$( dirname "$0" )" && pwd )"

cd $SCRIPT_HOME/..


# vagrant ssh -c "cd /vagrant && sudo fig run composer update"

case "$1" in
	unit)
		vagrant ssh -c "cd /vagrant && sudo fig run phpunit unit"
		;;
	functional)
		vagrant ssh -c "cd /vagrant && sudo fig run phpunit functional"
		;;
	all)
		vagrant ssh -c "cd /vagrant && sudo fig run phpunit all"
		;;
	*)
		echo "options are: all, unit, functional"
		;;
esac

