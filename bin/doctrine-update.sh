if [ -L $0 ] ; then
    DIR=$(dirname $(readlink -f $0)) ;
else
    DIR=$(dirname $0) ;
fi ;

$DIR/../vendor/bin/doctrine-module orm:clear-cache:metadata &&
$DIR/../vendor/bin/doctrine-module orm:generate-entities --filter="Application" ./module/Application/src &&
$DIR/../vendor/bin/doctrine-module orm:schema-tool:update --force &&
$DIR/../vendor/bin/doctrine-module orm:generate-proxies