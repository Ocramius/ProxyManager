set -x
if [ "$TRAVIS_PHP_VERSION" = 'hhvm' ]; then
    export DEBIAN_FRONTEND=noninteractive
    sudo apt-get remove -q -y hhvm
    sudo add-apt-repository -y ppa:mapnik/boost
    sudo apt-get update -q -y
    sudo apt-get install -q -y hhvm-nightly
    hhvm --version

    curl -sS https://getcomposer.org/installer | hhvm
    hhvm -v ResourceLimit.SocketDefaultTimeout=30 -v Http.SlowQueryThreshold=30000 composer.phar self-update
    hhvm -v ResourceLimit.SocketDefaultTimeout=30 -v Http.SlowQueryThreshold=30000 composer.phar update --prefer-source
    hhvm -v ResourceLimit.SocketDefaultTimeout=30 -v Http.SlowQueryThreshold=30000 composer.phar install --dev --prefer-source
else
    composer self-update
    composer update --prefer-source
    composer install --dev --prefer-source
fi
