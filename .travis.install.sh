set -x

IGNORE_PLATFORM_REQUIREMENTS=""

if [ "$TRAVIS_PHP_VERSION" = 'nightly' ] ; then
    IGNORE_PLATFORM_REQUIREMENTS="--ignore-platform-reqs"
fi

composer update $IGNORE_PLATFORM_REQUIREMENTS

if [ "$DEPENDENCIES" = 'low' ] ; then
    composer update --prefer-lowest --prefer-stable $IGNORE_PLATFORM_REQUIREMENTS
fi
