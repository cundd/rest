#!/usr/bin/env bash

set -o nounset
set -o errexit

PROJECT_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

: ${TYPO3_PATH_WEB=""}
: ${PHP_BINARY="php"}
: ${PHPUNIT_BINARY=""}
: ${CHECK_MYSQL_CREDENTIALS="yes"}
: ${DEBUG=""}
: ${TEST_MODE="yes"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabasePort="3306"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

if [[ -e "lib.sh" ]]; then source "lib.sh"; fi
source "$PROJECT_HOME/Build/lib.sh";

# Detect the phpunit path to use for Functional Tests
function get_phpunit_path_for_functional_tests() {
    init_typo3_path_web;
    lib::print_debug "Check phpunit at $TYPO3_PATH_WEB/bin/phpunit";
    if [[ -e "$TYPO3_PATH_WEB/bin/phpunit" ]]; then
        echo "$TYPO3_PATH_WEB/bin/phpunit";
        return;
    fi

    lib::print_debug "Check phpunit at $TYPO3_PATH_WEB/vendor/bin/phpunit";
    if [[ -e "$TYPO3_PATH_WEB/vendor/bin/phpunit" ]]; then
        echo "$TYPO3_PATH_WEB/vendor/bin/phpunit";
    else
        return 1;
    fi
}

# Check the phpunit path to use for Functional Tests
function check_phpunit_path_for_functional_tests() {
    $(get_phpunit_path_for_functional_tests > /dev/null) || {
        lib::print_error "Could not find phpunit to run functional tests";
        exit 1;
    };
}

# Detect the phpunit path to use for Unit Tests
function get_phpunit_path_for_unit_tests() {
    lib::print_debug "Check if \$PHPUNIT_BINARY is set";
    if [[ "$PHPUNIT_BINARY" != "" ]]; then
        echo "$PHPUNIT_BINARY";
        return;
    fi

    lib::print_debug "Check phpunit at `pwd`/bin/phpunit";
    if [[ -e "`pwd`/bin/phpunit" ]]; then
        echo "`pwd`/bin/phpunit";
        return;
    fi

    lib::print_debug "Check phpunit at `pwd`/vendor/bin/phpunit";
    if [[ -e "`pwd`/vendor/bin/phpunit" ]]; then
        echo "`pwd`/vendor/bin/phpunit";
    else
        get_phpunit_path_for_functional_tests;
    fi
}

# Check the phpunit path to use for Unit Tests
function check_phpunit_path_for_unit_tests() {
    $(get_phpunit_path_for_unit_tests &> /dev/null) || {
        lib::print_error "Could not find phpunit to run unit tests";
        exit 1;
    };
}

# Check the provided MySQL credentials
function check_mysql_credentials {
    lib::print_debug "Check MySQL credentials";
    php -r '@mysqli_connect("'${typo3DatabaseHost}'", "'${typo3DatabaseUsername}'", "'${typo3DatabasePassword}'", "'${typo3DatabaseName}'", "'${typo3DatabasePort}'") or exit(1);' || {
        lib::print_error "Could not connect to MySQL ($typo3DatabaseUsername:$typo3DatabasePassword@$typo3DatabaseHost:$typo3DatabasePort / $typo3DatabaseName)";
    }
}

# Prepare the system for database connections
function init_database {
    # Export database credentials
    export typo3DatabaseName=${typo3DatabaseName};
    export typo3DatabaseHost=${typo3DatabaseHost};
    export typo3DatabasePort=${typo3DatabasePort};
    export typo3DatabaseUsername=${typo3DatabaseUsername};
    export typo3DatabasePassword=${typo3DatabasePassword};
    if [[ "$CHECK_MYSQL_CREDENTIALS" == "yes" ]]; then
        check_mysql_credentials;
    fi
    lib::print_info "Connect to database '$typo3DatabaseName' at '$typo3DatabaseHost:$typo3DatabasePort' using '$typo3DatabaseUsername' '$typo3DatabasePassword'";
}   

# Prepare the TYPO3 system
function init_typo3 {
   local baseDir=`pwd`;
    lib::print_debug "Check for phpunit at ${TYPO3_PATH_WEB}/bin/phpunit";
    if [[ ! -x ${TYPO3_PATH_WEB}/bin/phpunit ]]; then
        lib::print_debug "Check for composer.json in '$TYPO3_PATH_WEB'";
        if [[ -f "$TYPO3_PATH_WEB/composer.json" ]]; then
            cd ${TYPO3_PATH_WEB};
            lib::print_debug "Run composer install in '$TYPO3_PATH_WEB'";
            lib::composer install;

            lib::print_debug "Go back into '$baseDir'";
            cd ${baseDir};
        fi
    fi
}

# Test the TYPO3_PATH_WEB environment
function init_typo3_path_web {
    if [[ "${TYPO3_PATH_WEB}" == "" ]]; then
        TYPO3_PATH_WEB=$(get_typo3_base_path);
        if [[ "${TYPO3_PATH_WEB}" == "" ]]; then
            lib::print_error "Please set the TYPO3_PATH_WEB environment variable";
            exit 1;
        fi
    elif [[ ! -d ${TYPO3_PATH_WEB} ]]; then
        lib::print_error "The defined TYPO3_PATH_WEB '$TYPO3_PATH_WEB' does not seem to be a directory";
        exit 1;
    else
        lib::print_debug "TYPO3_PATH_WEB is '$TYPO3_PATH_WEB'";
    fi;
}

# Check the system environment
function init {
    init_typo3_path_web;
    init_typo3;
    init_database;
}

# Run Unit Tests
function unit_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c "$PROJECT_HOME/Tests/Unit/phpunit.xml" "$@";
    else
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c "$PROJECT_HOME/Tests/Unit/phpunit.xml" "$PROJECT_HOME/Tests/Unit" "$@";
    fi
}

# Run Manual Tests
function manual_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c "$PROJECT_HOME/Tests/Manual/phpunit.xml" "$@";
    else
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_unit_tests) -c "$PROJECT_HOME/Tests/Manual/phpunit.xml" "$PROJECT_HOME/Tests/Manual" "$@";
    fi
}

# Run Functional Tests
function functional_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_functional_tests) -c "$PROJECT_HOME/Tests/Functional/phpunit.xml" "$@";
    else
        TEST_MODE="$TEST_MODE" ${PHP_BINARY} $(get_phpunit_path_for_functional_tests) -c "$PROJECT_HOME/Tests/Functional/phpunit.xml" "$PROJECT_HOME/Tests/Functional" "$@";
    fi
}

# Run Documentation Tests (using test-flight)
function documentation_tests {
    ${PHP_BINARY} vendor/bin/test-flight "$@";
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
            lib::print_error "Unknown argument '$1'";
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
        check_phpunit_path_for_unit_tests;
        lib::print_header "Run Unit Tests (using $(get_phpunit_path_for_unit_tests))";
        unit_tests "$@";
    fi

    if [[ "$FUNCTIONAL_TESTS" == "yes" ]]; then
        check_phpunit_path_for_functional_tests;
        init;
        lib::print_header "Run Functional Tests (using $(get_phpunit_path_for_functional_tests))";
        functional_tests "$@";
    fi

    if [[ "$MANUAL_TESTS" == "yes" ]]; then
        check_phpunit_path_for_unit_tests;
        init;
        lib::print_header "Run Manual Tests (using $(get_phpunit_path_for_unit_tests))";
        manual_tests "$@";
    fi

    if [[ "$DOCUMENTATION_TESTS" == "yes" ]]; then
        lib::print_header "Run Documentation Tests";
        documentation_tests "$@";
    fi
}

main $@;
