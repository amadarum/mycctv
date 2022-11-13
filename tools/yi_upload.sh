#!/bin/sh

RECORD_PATH=/tmp/sd/record
INTERVAL=10
USER=
HOST=
HOSTNAME=$(hostname)


last=

if [ -f ~/.yi_last ]; then
    last=$(cat ~/.yi_last)
    echo "last $last"
fi

MIN=$(date '+%M')

if [ $MIN -le $INTERVAL ]; then
    TS=$(date '+%s')
    TS=$(($TS - 3600))
    NOW1=$(date -d @$TS '+%YY%mM%dD%HH')
    NOW2=$(date '+%YY%mM%dD%HH')
    NOWS="$NOW1 $NOW2"
else
    NOWS=$(date '+%YY%mM%dD%HH')
fi


for NOW in $NOWS; do
    REC_PATH="$RECORD_PATH/$NOW"
    if ! [ -d "$REC_PATH" ]; then
        echo "$REC_PATH not found"
        continue
    fi
    echo $REC_PATH
    IFS=$'\n'
    for f in $(ls -1 $REC_PATH); do
        if [ -d "$f" ]; then
            continue
        fi
        filename=${NOW}${f}
        if [ "$last" \> "$filename" ]; then
            continue
        fi
        scp -i ~/.ssh/id_rsa $REC_PATH/$f $USER@$HOST:/var/www/yihome/storage/upload/${HOSTNAME}_${filename}
        retVal=$?
        if [ $retVal -eq 0 ]; then
            echo $filename > ~/.yi_last
            echo "$f [ok]"
        else
            echo "$f [failed]"
        fi
    done
done