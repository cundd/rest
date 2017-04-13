#!/usr/bin/env bash

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
