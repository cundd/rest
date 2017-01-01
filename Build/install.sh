#!/usr/bin/env bash

set -o nounset
set -o errexit

CLI_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${TYPO3="TYPO3_7-6"}
: ${REPO="rest"}

: ${FUNCTIONAL_TESTS="yes"}
: ${UNIT_TESTS="yes"}

: ${TYPO3_PATH_WEB=""}
: ${PHP_BINARY="php"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

source "$CLI_HOME/Build/lib.sh";

function get_mysql_client_path {
    if [[ `which mysql > /dev/null` ]]; then
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
    composer install --verbose;
}

function install_typo3 {
    print_header "Get TYPO3 source $TYPO";
    cd ..;

    if [[ ! -e "TYPO3.CMS" ]]; then
        git clone --single-branch --branch ${TYPO3} --depth 1 git://git.typo3.org/Packages/TYPO3.CMS.git;
        cd TYPO3.CMS;
    else
        cd TYPO3.CMS;
        git pull;
    fi

    composer install;
    rm -rf typo3/sysext/compatibility6;

    mkdir -p ./typo3conf/ext/;
    if [[ ! -e "./typo3conf/ext/$REPO" ]]; then
        ln -s ${CLI_HOME} "./typo3conf/ext/$REPO";
    fi
    cd ..;
}

function prepare_database {
    if [[ "$(get_mysql_client_path)" != "" ]]; then
        $(get_mysql_client_path) -e "create database $typo3DatabaseName;" || {
            print_info "Database $typo3DatabaseName not created";
        };
    fi
}

function main {
    cd ${CLI_HOME};

    install_dependencies;
    install_typo3;
    prepare_database;
}

main;
