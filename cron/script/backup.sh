#!/bin/sh
# System + MySQL backup script
# ---------------------------------------------------------------------
### System Setup ###
BACKUP=/cron/backup
NOW=$(date +"%d-%m-%Y")
### MySQL Setup ###
MUSER="opencarburant_user"
MPASS="pass*9876"
MHOST="dbserver"
MYSQLDUMP="$(which mysqldump)"
GZIP="$(which gzip)"
### Start Backup for cron file system ###
DIRS="/cron/script"
FILE="cron-backup-$NOW.tar.gz"
tar -zcf $BACKUP/$FILE $DIRS
### Start Backup for webserver file system ###
DIRS="/html"
FILE="html-backup-$NOW.tar.gz"
tar -zcf $BACKUP/$FILE $DIRS
### Start MySQL Backup ###
FILE=$BACKUP/db-opencarburant.$NOW-$(date +"%T").gz
$MYSQLDUMP -u $MUSER -h $MHOST -p$MPASS opencarburant | $GZIP -9 > $FILE
