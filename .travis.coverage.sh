set -x
if [ "$TRAVIS_PHP_VERSION" = 'hhvm' ] || [ "$TRAVIS_PHP_VERSION" = 'hhvm-nightly' ] ; then
else
    wget https://scrutinizer-ci.com/ocular.phar
    php ocular.phar code-coverage:upload --format=php-clover ./clover.xml
fi
