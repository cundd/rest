#!/usr/bin/env bash

set -o nounset
set -o errexit

PROJECT_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${TYPO3_PATH_WEB="$PROJECT_HOME/../TYPO3.CMS"}
: ${PHP_BINARY="php"}
: ${CHECK_MYSQL_CREDENTIALS="yes"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

source "$PROJECT_HOME/Build/lib.sh";

# Detect the phpunit path to use for Functional Tests
function get_phpunit_path_for_functional_tests() {
    if [ -e "$TYPO3_PATH_WEB/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/bin/phpunit";
    elif [ -e "$TYPO3_PATH_WEB/vendor/bin/phpunit" ]; then
        echo "$TYPO3_PATH_WEB/vendor/bin/phpunit";
    else
        print_error "Could not find phpunit";
        exit 1;
    fi
}

# Detect the phpunit path to use for Unit Tests
function get_phpunit_path_for_unit_tests() {
    if [ -e "`pwd`/bin/phpunit" ]; then
        echo "`pwd`/bin/phpunit";
    elif [ -e "`pwd`/vendor/bin/phpunit" ]; then
        echo "`pwd`/vendor/bin/phpunit";
    else
        get_phpunit_path_for_functional_tests;
    fi
}

# Check the provided MySQL credentials
function check_mysql_credentials {
    php -r '@mysqli_connect("'${typo3DatabaseHost}'", "'${typo3DatabaseUsername}'", "'${typo3DatabasePassword}'", "'${typo3DatabaseName}'") or exit(1);' || {
        print_error "Could not connect to MySQL ($typo3DatabaseUsername:$typo3DatabasePassword@$typo3DatabaseHost / $typo3DatabaseName)";
    }
}

# Prepare the system for database connections
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

# Prepare the TYPO3 system
function init_typo3 {
	local baseDir=`pwd`;
	if [[ ! -x ${TYPO3_PATH_WEB}/bin/phpunit ]]; then
		cd ${TYPO3_PATH_WEB};
		composer install;
		cd ${baseDir};
	fi
}

# Check the system environment
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

# Run Unit Tests
function unit_tests {
    set +e;
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c ./Tests/Unit/phpunit.xml "$@";
    else
        ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c ./Tests/Unit/phpunit.xml ./Tests/Unit "$@";
    fi
    set -e;
}

# Run Manual Tests
function manual_tests {
    set +e;
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c ./Tests/Manual/phpunit.xml "$@";
    else
        ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c ./Tests/Manual/phpunit.xml ./Tests/Manual "$@";
    fi
    set -e;
}

# Run Functional Tests
function functional_tests {
    set +e;
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        ${PHP_BINARY} $(get_phpunit_path_for_functional_tests) -c ./Tests/Functional/phpunit.xml "$@";
    else
        ${PHP_BINARY} $(get_phpunit_path_for_functional_tests) -c ./Tests/Functional/phpunit.xml ./Tests/Functional "$@";
    fi
    set -e;
}

# Run Documentation Tests (using test-flight)
function documentation_tests {
    set +e;
    ${PHP_BINARY} vendor/bin/test-flight "$@";
    set -e;
}

# Print the help
function show_help() {
    echo "Usage $0 [options] -- [phpunit-options] [<directory>]

Example $0 --functional
Example $0 -- Tests/Unit/

  --unit                    Run Unit tests
  --functional              Run Functional tests
  --manual                  Run manual tests
  --doc|--documentation     Run documentation tests
  -h|--help                 Print this information
";
}

# Main entry point
function main {
    init;

    local _functional_tests="no";
    local _unit_tests="no";
    local _manual_tests="no";
    local _documentation_tests="no";

    local _tests_selected="no";

    # Consume all arguments until "--" is found
    while [[ "$#" -gt "0" ]]; do
        if [[ "$1" == "--help" || "$1" == "-h" ]]; then
            show_help;
            exit 0;
        elif [[ "$1" == "--unit" ]]; then
            _tests_selected="yes";
            _unit_tests="yes";
        elif [[ "$1" == "--functional" ]]; then
            _tests_selected="yes";
            _functional_tests="yes";
        elif [[ "$1" == "--manual" ]]; then
            _tests_selected="yes";
            _manual_tests="yes";
        elif [[ "$1" == "--doc" ]] || [[ "$1" == "--documentation" ]]; then
            _tests_selected="yes";
            _documentation_tests="yes";
        elif [[ "$1" == "--" ]]; then
            shift;
            break;
        else
            print_error "Unknown argument '$1'";
            echo;
            show_help;
            exit 1;
        fi

        shift;
    done

    # If the next argument is a directory or a file look if it tells us what kind of tests to run
    if [[ "$#" -gt "0" ]] && [[ -e "$1" ]]; then
        if [[ "$1" == Tests/Functional* ]] || [[ "$1" == `pwd`/Tests/Functional* ]]; then
            _tests_selected="yes";
            _functional_tests="yes";
        elif [[ "$1" == Tests/Unit* ]] || [[ "$1" == `pwd`/Tests/Unit* ]]; then
            _tests_selected="yes";
            _unit_tests="yes";
        elif [[ "$1" == Tests/Manual* ]] || [[ "$1" == `pwd`/Tests/Manual* ]]; then
            _tests_selected="yes";
            _manual_tests="yes";
        elif [[ "$1" == Documentation* ]] || [[ "$1" == `pwd`/Documentation* ]]; then
            _tests_selected="yes";
            _documentation_tests="yes";
        fi
    fi

    # If no tests have been selected (either by '--unit' or passing 'Tests/Unit') run all tests
    if [[ "$_tests_selected" == "no" ]]; then
        _functional_tests="yes";
        _unit_tests="yes";
        _manual_tests="yes";
        _documentation_tests="yes";
    fi

    # Environmental variables will override the value
    : ${FUNCTIONAL_TESTS="$_functional_tests"}
    : ${UNIT_TESTS="$_unit_tests"}
    : ${MANUAL_TESTS="$_manual_tests"}
    : ${DOCUMENTATION_TESTS="$_documentation_tests"}

	export TYPO3_PATH_WEB="$TYPO3_PATH_WEB";
	export CUNDD_TEST="yes";

    if [[ "$UNIT_TESTS" == "yes" ]]; then
        print_header "Run Unit Tests (using $(get_phpunit_path_for_unit_tests))";
        unit_tests "$@";
    fi

    if [[ "$FUNCTIONAL_TESTS" == "yes" ]]; then
        print_header "Run Functional Tests (using $(get_phpunit_path_for_functional_tests))";
        init_database;
        functional_tests "$@";
    fi

    if [[ "$MANUAL_TESTS" == "yes" ]]; then
        print_header "Run Manual Tests (using $(get_phpunit_path_for_unit_tests))";
        init_database;
        manual_tests "$@";
    fi

    if [[ "$DOCUMENTATION_TESTS" == "yes" ]]; then
        print_header "Run Documentation Tests";
        documentation_tests "$@";
    fi
}

main $@;
