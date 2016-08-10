#!/usr/bin/env bash

set -o nounset
set -o errexit
set +e


: ${FUNCTIONAL_TESTS="yes"}
: ${UNIT_TESTS="yes"}

: ${TYPO3_PATH_WEB="not set"}

: ${typo3DatabaseName="typo3"}
: ${typo3DatabaseHost="127.0.0.1"}
: ${typo3DatabaseUsername="root"}
: ${typo3DatabasePassword="root"}

function get_phpunit_path {
    if [ -x ${TYPO3_PATH_WEB}/bin/phpunit ]; then
        echo "${TYPO3_PATH_WEB}/bin/phpunit";
    elif [ -x "${TYPO3_PATH_WEB}/../vendor/bin/phpunit" ]; then
        echo "${TYPO3_PATH_WEB}/../vendor/bin/phpunit";
    else
        echo "phpunit not found";
    fi
}

function get_mysql_client_path {
    if [[ `which mysql > /dev/null` ]]; then
        which mysql;
    elif [[ -x /Applications/MAMP/Library/bin/mysql ]]; then
        echo /Applications/MAMP/Library/bin/mysql;
    else
        return 1;
    fi
}

function run_functional_tests_parallel {
    local phpunit_path=$(get_phpunit_path);
    if hash parallel 2> /dev/null; then
        time find -L "$1" -name \*Test.php | parallel --halt-on-error 2 --gnu "echo; echo 'Running functional {} test case';  $phpunit_path --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml {}";
    else
        echo "Command 'parallel' not found. Will run sequential";
        time find -L "$1" -name \*Test.php -exec sh -c "echo; echo 'Running functional {} test case';  $phpunit_path --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml {}" \; ;
    fi
}

function check_mysql_credentials {
    `get_mysql_client_path` -u${typo3DatabaseUsername} -p${typo3DatabasePassword} -h${typo3DatabaseHost} -D${typo3DatabaseName} -e "exit" 2> /dev/null;
    if [ $? -ne 0 ]; then
        echo "ERROR: Could not connect to MySQL";
        exit 1;
    fi

    php -r '@mysqli_connect("'${typo3DatabaseHost}'", "'${typo3DatabaseUsername}'", "'${typo3DatabasePassword}'", "'${typo3DatabaseName}'") or die(mysqli_connect_error());';
    if [ $? -ne 0 ]; then
        echo "ERROR: Could not connect to MySQL";
        exit 1;
    fi
}

function init_database {
    # Export database credentials
    export typo3DatabaseName=${typo3DatabaseName};
    export typo3DatabaseHost=${typo3DatabaseHost};
    export typo3DatabaseUsername=${typo3DatabaseUsername};
    export typo3DatabasePassword=${typo3DatabasePassword};
    echo "Connect to database '$typo3DatabaseName' at '$typo3DatabaseHost' using '$typo3DatabaseUsername' '$typo3DatabasePassword'";
}

function init_typo3 {
	local baseDir=`pwd`;
	if [[ $(get_phpunit_path) == "phpunit not found" ]]; then
		cd ${TYPO3_PATH_WEB};
		composer install;
		cd ${baseDir};
	fi
}

function init {
    # Test the environment
    if [ "$TYPO3_PATH_WEB" == "not set" ]; then
        echo "Please set the TYPO3_PATH_WEB environment variable";
        exit 1;
    elif [[ ! -d ${TYPO3_PATH_WEB} ]]; then
        echo "The defined TYPO3_PATH_WEB does not seem to be a directory";
        exit 1;
    fi;

	init_typo3;
    init_database;
}

function unit_tests {
    if [[ ! -z ${1+x} ]] && [[ -e "$1" ]]; then
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/UnitTests.xml "$@";
    else
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/UnitTests.xml ./Tests/Unit "$@";
    fi
}

function functional_tests {
    if [ "$#" == "0" ]; then
        run_functional_tests_parallel ./Tests/Functional;
    elif [ "$#" == "1" ] && [[ -d "$1" ]]; then
        run_functional_tests_parallel "$1";
    elif [ "$#" -gt "1" ]; then
        # Can not run parallel with more arguments
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml "$@";
    else
        $(get_phpunit_path) --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml ./Tests/Functional "$@";
    fi
}

function main {
    init;
    if [[ "$UNIT_TESTS" == "yes" ]]; then
        echo "Run Unit Tests";
        unit_tests "$@";
    fi

    if [[ "$FUNCTIONAL_TESTS" == "yes" ]]; then
        echo "Run Functional Tests";
        check_mysql_credentials;
        functional_tests "$@";
    fi
}

main $@;
