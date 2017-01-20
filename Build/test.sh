#!/usr/bin/env bash

set -o nounset
set -o errexit

CLI_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${FUNCTIONAL_TESTS="yes"}
: ${UNIT_TESTS="yes"}

: ${TYPO3_PATH_WEB="$CLI_HOME/../TYPO3.CMS"}
: ${PHP_BINARY="php"}
: ${CHECK_MYSQL_CREDENTIALS="yes"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

source "$CLI_HOME/Build/lib.sh";

function get_phpunit_path() {
    if [ -e "$TYPO3_PATH_WEB/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/bin/phpunit";
    elif [ -e "$TYPO3_PATH_WEB/vendor/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/vendor/bin/phpunit";
    else
        print_error "Could not find phpunit";
        exit 1;
    fi
}

function check_mysql_credentials {
    php -r '@mysqli_connect("'${typo3DatabaseHost}'", "'${typo3DatabaseUsername}'", "'${typo3DatabasePassword}'", "'${typo3DatabaseName}'") or exit(1);' || {
        print_error "Could not connect to MySQL ($typo3DatabaseUsername:$typo3DatabasePassword@$typo3DatabaseHost / $typo3DatabaseName)";
        exit 1;
    }
}

function init_database {
    # Export database credentials
    export typo3DatabaseName=${typo3DatabaseName};
    export typo3DatabaseHost=${typo3DatabaseHost};
    export typo3DatabaseUsername=${typo3DatabaseUsername};
    export typo3DatabasePassword=${typo3DatabasePassword};
    if [[ "$CHECK_MYSQL_CREDENTIALS" == "yes" ]]; then
        check_mysql_credentials;
    fi
    print_info "Connect to database '$typo3DatabaseName' at '$typo3DatabaseHost' using '$typo3DatabaseUsername' '$typo3DatabasePassword'";
}

function init_typo3 {
	local baseDir=`pwd`;
	if [[ ! -x ${TYPO3_PATH_WEB}/bin/phpunit ]]; then
		cd ${TYPO3_PATH_WEB};
		composer install;
		cd ${baseDir};
	fi
}

function init {
    # Test the environment
    if [ "${TYPO3_PATH_WEB}" == "" ]; then
        print_error "Please set the TYPO3_PATH_WEB environment variable";
        exit 1;
    elif [[ ! -d ${TYPO3_PATH_WEB} ]]; then
        print_error "The defined TYPO3_PATH_WEB '$TYPO3_PATH_WEB' does not seem to be a directory";
        exit 1;
    fi;

	init_typo3;
}

function unit_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        ${PHP_BINARY} $(get_phpunit_path) -c ./Tests/Unit/phpunit.xml "$@";
    else
        ${PHP_BINARY} $(get_phpunit_path) -c ./Tests/Unit/phpunit.xml ./Tests/Unit "$@";
    fi
}

function functional_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        ${PHP_BINARY} $(get_phpunit_path) -c ./Tests/Functional/phpunit.xml "$@";
    else
        ${PHP_BINARY} $(get_phpunit_path) -c ./Tests/Functional/phpunit.xml ./Tests/Functional "$@";
    fi
}

function main {
    init;
    if [[ "$UNIT_TESTS" == "yes" ]]; then
        print_header "Run Unit Tests";
        unit_tests "$@";
    fi

    if [[ "$FUNCTIONAL_TESTS" == "yes" ]]; then
        print_header "Run Functional Tests";
        init_database;
        functional_tests "$@";
    fi
}

main $@;
