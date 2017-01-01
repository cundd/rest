#!/usr/bin/env bash

function _tput {
    if hash tput &>/dev/null; then
        tput $*;
    fi
}

function print_error() {
    >&2 _tput setaf 1;
    >&2 echo "[ERROR] $@";
    >&2 _tput sgr0;
}

function print_header() {
    _tput setaf 2;
    echo "[TASK] $@";
    _tput sgr0;
}

function print_info() {
    _tput setaf 4;
    echo "[INFO] $@";
    _tput sgr0;
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
