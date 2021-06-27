#!/bin/bash
#
# Available environment variable to use
#
# - QURAN_JSON_DIR
# - QURAN_APP_NAME
# - QURAN_BASE_URL
# - QURAN_BASE_MUROTTAL_URL
# - QURAN_ANALYTICS_ID
# - QURAN_DOWNLOAD_JSON
#
[ -z "$QURAN_DOWNLOAD_JSON" ] && QURAN_DOWNLOAD_JSON="no"

[ "$QURAN_DOWNLOAD_JSON" = "yes" ] && {
    echo "Downloading quran-json project to /tmp..."

    [ ! -f "/tmp/quran-json.tar.gz" ] && {
        curl -s -L -o /tmp/quran-json.tar.gz 'https://github.com/rioastamal/quran-json/archive/refs/heads/master.tar.gz'
    }

    tar xvf /tmp/quran-json.tar.gz -C /tmp
}

php src/generator/generator.php