#!/usr/bin/env bash

set -o nounset
set -o errexit

REST_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${TYPO3="master"}
: ${REPO="rest"}

: ${FUNCTIONAL_TESTS="yes"}
: ${UNIT_TESTS="yes"}

: ${TYPO3_PATH_WEB=""}
: ${PHP_BINARY="php"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

: ${TRAVIS_PHP_VERSION="7.0"}

source "$REST_HOME/Build/lib.sh";

function get_mysql_client_path {
    if hash mysql 2>/dev/null; then
        echo mysql;
    elif [[ `which mysql > /dev/null` ]]; then
        which mysql;
    elif [[ -x /Applications/MAMP/Library/bin/mysql ]]; then
        echo /Applications/MAMP/Library/bin/mysql;
    else
        return 1;
    fi
}

function install_dependencies {
    print_header "Install dependencies";
    composer self-update;
    composer install --verbose --no-dev --ignore-platform-reqs;
}

function install_typo3 {
    print_header "Get TYPO3 source $TYPO3";
    cd ..;

    if [[ ! -e "TYPO3.CMS" ]]; then
        git clone --single-branch --branch ${TYPO3} --depth 1 git://git.typo3.org/Packages/TYPO3.CMS.git;
        cd TYPO3.CMS;
    else
        cd TYPO3.CMS;
        git pull;
    fi
    export TYPO3_PATH_WEB="`pwd`";

    if [ "$TRAVIS_PHP_VERSION" == "hhvm" ]; then
        composer remove --dev friendsofphp/php-cs-fixer;
    fi
    composer install --ignore-platform-reqs;
    rm -rf typo3/sysext/compatibility6;

    mkdir -p ./typo3conf/ext/;
    if [[ ! -e "./typo3conf/ext/$REPO" ]]; then
        ln -s ${REST_HOME} "./typo3conf/ext/$REPO";
    fi
    cd ..;
}

function prepare_database {
    if [[ "$(get_mysql_client_path)" != "" ]]; then
        if [[ "$typo3DatabasePassword" != "" ]]; then
            $(get_mysql_client_path) \
                -h${typo3DatabaseHost} \
                -u${typo3DatabaseUsername} \
                -p${typo3DatabasePassword} \
                -e "CREATE DATABASE IF NOT EXISTS $typo3DatabaseName;" || {
                print_warning "Database $typo3DatabaseName not created";
            };
        else
            $(get_mysql_client_path) \
                -h${typo3DatabaseHost} \
                -u${typo3DatabaseUsername} \
                -e "CREATE DATABASE IF NOT EXISTS $typo3DatabaseName;" || {
                print_warning "Database $typo3DatabaseName not created";
            };
        fi
    else
        print_warning "MySQL client not found";
    fi
}

function main {
    cd ${REST_HOME};

    install_dependencies;
    install_typo3;
    prepare_database;
}

main;
