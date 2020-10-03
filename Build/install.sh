#!/usr/bin/env bash

set -o nounset
set -o errexit

PROJECT_HOME="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# The branch of TYPO3 to checkout (e.g. 'TYPO3_8-7', 'TYPO3_7-6')
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

if [[ -e "lib.sh" ]]; then source "lib.sh"; fi
source "$PROJECT_HOME/Build/lib.sh"

# Install the project's dependencies
function install_dependencies() {
    lib::print_header "Install dependencies"
    lib::composer self-update
    if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]; then
        lib::composer install --verbose --ignore-platform-reqs
    else
        lib::composer install --verbose
    fi
}

# Install TYPO3
function install_typo3() {
    lib::print_header "Get TYPO3 source $TYPO3"

    local typo3_base_path=$(get_typo3_base_path)
    if [[ "$typo3_base_path" != "" ]]; then
        lib::pushd "$typo3_base_path"
        if [[ -d ".git" ]]; then
            lib::print_info "Update TYPO3 source"
            git pull

            if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]; then
                lib::composer remove --ignore-platform-reqs --dev friendsofphp/php-cs-fixer --no-scripts
                lib::composer install --ignore-platform-reqs --prefer-dist --no-scripts
            else
                lib::composer install --prefer-dist --no-scripts
            fi
        fi
    else
        lib::pushd "./"
        lib::print_info "Install TYPO3 source $TYPO3 via composer"

        if [[ "$TRAVIS_PHP_VERSION" == "hhvm" ]]; then
            lib::composer require --ignore-platform-reqs typo3/minimal=$TYPO3 --no-scripts
        else
            lib::composer require typo3/minimal=$TYPO3 --no-scripts
        fi
    fi

    export TYPO3_PATH_WEB="$(pwd)"

    rm -rf typo3/sysext/compatibility6

    #    mkdir -p ./typo3conf/ext/;
    #    if [[ ! -e "./typo3conf/ext/$REPO" ]]; then
    #        ln -s ${PROJECT_HOME} "./typo3conf/ext/$REPO";
    #    fi

    lib::popd
}

# Prepare the MySQL database
function prepare_database() {
    if [[ "$(get_mysql_client_path)" != "" ]]; then
        if [[ "$typo3DatabasePassword" != "" ]]; then
            $(get_mysql_client_path) \
                -h${typo3DatabaseHost} \
                -u${typo3DatabaseUsername} \
                -p${typo3DatabasePassword} \
                -e "CREATE DATABASE IF NOT EXISTS $typo3DatabaseName;" || {
                lib::print_warning "Database $typo3DatabaseName not created"
            }
        else
            $(get_mysql_client_path) \
                -h${typo3DatabaseHost} \
                -u${typo3DatabaseUsername} \
                -e "CREATE DATABASE IF NOT EXISTS $typo3DatabaseName;" || {
                lib::print_warning "Database $typo3DatabaseName not created"
            }
        fi
    else
        lib::print_warning "MySQL client not found"
    fi
}

# Main entry point
function main() {
    cd ${PROJECT_HOME}

    if [[ "$#" -eq "0" ]]; then
        install_typo3
        prepare_database
        return
    fi

    local sub_command="$1"
    shift
    if hash "$sub_command" 2>/dev/null; then
        ${sub_command} "$@"
    else
        lib::print_error "Subcommand '$sub_command' does not exist"
    fi
}

main "$@"
