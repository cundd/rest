#!/usr/bin/env bash

PROJECT_HOME="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )";

function lib::_tput {
    : ${TERM="dumb"}
    if [ "$TERM" != "dumb" ] && hash tput &>/dev/null; then
        tput $*;
    fi
}

# Print a error message
function print_error() {
    >&2 lib::_tput setaf 1;
    >&2 echo "[ERROR] $@";
    >&2 lib::_tput sgr0;
}

# Print a header message
function print_header() {
    lib::_tput setaf 2;
    echo "[TASK] $@";
    lib::_tput sgr0;
}

# Print a info message
function print_info() {
    lib::_tput setaf 4;
    echo "[INFO] $@";
    lib::_tput sgr0;
}

# Print a warning
function print_warning() {
    lib::_tput setaf 3;
    echo "[WARNING] $@";
    lib::_tput sgr0;
}

# Retrieve the path to the MySQL client
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

# Silent pushd
function lib::pushd {
    pushd $1 > /dev/null;
}

# Silent popd
function lib::popd {
    popd > /dev/null;
}

# Detects the path to the TYPO3 base
function get_typo3_base_path {
    if [ -d "$PROJECT_HOME/../TYPO3.CMS" ]; then
        echo "$PROJECT_HOME/../TYPO3.CMS";
    elif [ -d "$PROJECT_HOME/../../../typo3" ]; then
        dirname "$PROJECT_HOME/../../../typo3";
    fi
}