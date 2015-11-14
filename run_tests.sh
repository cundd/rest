#!/usr/bin/env bash

set -o nounset
set -o errexit
set +e

function init {
    # Test the environment
    if [ -z ${TYPO3_PATH_WEB+x} ]; then
        echo "Please set the TYPO3_PATH_WEB environment variable";
        exit 1;
    elif [[ ! -d ${TYPO3_PATH_WEB} ]]; then
        echo "The defined TYPO3_PATH_WEB does not seem to be a directory";
        exit 1;
    fi;

    # Export database credentials
    if [ -z ${typo3DatabaseName+x} ]; then
        export typo3DatabaseName="typo3";
    else
        export typo3DatabaseName=${typo3DatabaseName};
    fi

    if [ -z ${typo3DatabaseHost+x} ]; then
        export typo3DatabaseHost="127.0.0.1";
    else
        export typo3DatabaseHost=${typo3DatabaseHost};
    fi

    if [ -z ${typo3DatabaseUsername+x} ]; then
        export typo3DatabaseUsername="root";
    else
        export typo3DatabaseUsername=${typo3DatabaseUsername};
    fi

    if [ -z ${typo3DatabasePassword+x} ]; then
        export typo3DatabasePassword="root";
    else
        export typo3DatabasePassword=${typo3DatabasePassword};
    fi

    echo "Connect to database '$typo3DatabaseName' at '$typo3DatabaseHost' using '$typo3DatabaseUsername' '$typo3DatabasePassword'";
}

function unitTests {
    ${TYPO3_PATH_WEB}/bin/phpunit --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/UnitTests.xml ./Tests/Unit "$@";
}

function functionalTests {
    ${TYPO3_PATH_WEB}/bin/phpunit --colors -c ${TYPO3_PATH_WEB}/typo3/sysext/core/Build/FunctionalTests.xml ./Tests/Functional "$@";
}

function run {
    performFunctionalTests="yes";
    performUnitTests="yes";

    if [ ! -z ${FUNCTIONAL_TESTS+x} ]; then
        performFunctionalTests="$FUNCTIONAL_TESTS";
    fi

    if [ ! -z ${UNIT_TESTS+x} ]; then
        performUnitTests="$UNIT_TESTS";
    fi


#    echo "HALLO $@";


    if [[ "$performFunctionalTests" == "yes" ]]; then
        functionalTests "$@";
    fi

    if [[ "$performUnitTests" == "yes" ]]; then
        unitTests "$@";
    fi

}

init;
run $@;
