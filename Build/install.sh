#!/usr/bin/env bash

set -o nounset
set -o errexit

PROJECT_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${TYPO3="master"}
: ${REPO="$(basename ${PROJECT_HOME})"}

: ${PHP_BINARY="php"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabasePort="3306"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

# @internal
: ${TRAVIS_PHP_VERSION="7.0"}

source "$PROJECT_HOME/Build/lib.sh";

function run_composer {
    if hash composer.phar 2>/dev/null; then
        composer.phar "$@";
    elif hash composer 2>/dev/null; then
        composer "$@";
    fi
}

# Install the project's dependencies
function install_dependencies {
    print_header "Install dependencies";
    run_composer self-update;
    run_composer install --verbose --ignore-platform-reqs;
}

# Install the TYPO3
function install_typo3 {
    print_header "Get TYPO3 source $TYPO3";

    local typo3_base_path=$(get_typo3_base_path);
    if [[ "$typo3_base_path" != "" ]]; then
        lib::pushd "$typo3_base_path";
        print_info "Update TYPO3 source";
        git pull;
    else
        lib::pushd ..;
        print_info "Install TYPO3 source";
        if [[ ! -e "TYPO3.CMS" ]]; then
            git clone --single-branch --branch ${TYPO3} --depth 1 git://git.typo3.org/Packages/TYPO3.CMS.git;
            cd TYPO3.CMS;
        fi
    fi

    export TYPO3_PATH_WEB="`pwd`";

    if [ "$TRAVIS_PHP_VERSION" == "hhvm" ]; then
        run_composer remove --ignore-platform-reqs --dev friendsofphp/php-cs-fixer;
    fi
    run_composer install --ignore-platform-reqs;
    rm -rf typo3/sysext/compatibility6;

    mkdir -p ./typo3conf/ext/;
    if [[ ! -e "./typo3conf/ext/$REPO" ]]; then
        ln -s ${PROJECT_HOME} "./typo3conf/ext/$REPO";
    fi

    lib::popd;
}

# Prepares the MySQL database
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

# Main entry point
function main {
    cd ${PROJECT_HOME};

    if [ "$#" -eq "0" ];then
        install_dependencies;
        install_typo3;
        prepare_database;
        return;
    fi

    local sub_command="$1";
    shift;
    if hash "$sub_command" 2>/dev/null; then
        ${sub_command} "$@";
    else
        print_error "Subcommand '$sub_command' does not exist";
    fi
}

main "$@";
