#!/bin/bash

DESTINATION_HELP='-p project_destination_path'
PROJECT_HELP='-r Resets the Project dir'
CWD=$(pwd)

function help()
{
    echo ""
    echo "Opencart Plugin Installer by Jason Clark <mithereal@gmail.com>"
    echo ""
    echo "Usage: install-opencart-plugin $DESTINATION_HELP "
    echo $PROJECT_HELP
}

function install()
{
    if [ -f $CWD/.projdir ]
    then
    pdir="$(<$CWD/.projdir)"
    command="cp -r -v -t $pdir $CWD/upload/"*
    echo $($command)
    elif [ $DESTINATION_PATH ]
    then
    command="cp -r -v -t $DESTINATION_PATH $CWD/upload/"*
    echo $($command)
    else
    read -p "Which directory shall I install to: " LINE 
    echo "$LINE" > "$CWD/.projdir"
    fi
}

while getopts ":p:?:r" opt; do
    case $opt in
        p)
            DESTINATION_PATH=$OPTARG
            ;;
        r)
            rm "$CWD/.projdir"
            exit 0
            ;;
        ?)
            help
            exit 0
            ;;
    esac
done

install