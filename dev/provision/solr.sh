#! /bin/bash
### BEGIN INIT INFO
# Provides:          solr
# Required-Start:    $remote_fs $syslog
# Required-Stop:     $remote_fs $syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: solr initscript
# Description:       initscript for solr search engine
#                    placed in /etc/init.d.
### END INIT INFO
#
# Author: Kai Gerszewski <kai.gerszewski@liip.ch>
#
SOLR_HOME="/opt/apache-solr-3.6.2/example"
LOGFILE="/var/log/solr"
PID=$(netstat -anp | grep ':::8983' | sed 's/.*LISTEN\s*\([[:digit:]]*\)\/java/\1/')
JAVA_HOME="/usr/bin/java"
JAVA_STOP_PORT=8079
JAVA_OPTIONS=" -DSTOP.PORT=${JAVA_STOP_PORT} -DSTOP.KEY=stopkey -Xmx128M -Xms128M"

getPid() {
    echo $(netstat -anp | grep ':::8983' | sed 's/.*LISTEN\s*\([[:digit:]]*\)\/java/\1/')
}

start() {
    echo "Starting solr search engine..."
    if [ ! -z getPid ];
    then
        touch ${LOGFILE} && chown root:vagrant ${LOGFILE}
        cd ${SOLR_HOME}
        ${JAVA_HOME} ${JAVA_OPTIONS} -jar start.jar > ${LOGFILE} 2>&1 &
        echo "solr search engine is running"
    else
        echo "solr search engine is already running"
    fi
}

stop() {
    echo "Stopping solr search engine..."
    if [ ! -z getPid ];
    then
        cd ${SOLR_HOME}
        ${JAVA_HOME} ${JAVA_OPTIONS} -jar start.jar --stop > ${LOGFILE} 2>&1 &
        echo "solr search engine stopped"
    else
        echo "solr search engine was not running"
    fi
}

case "$1" in
    start)
        start
        ;;
    stop)
        stop
        ;;
    restart)
        stop
        sleep 5
        start
        ;;
    *)
        echo "Usage: /etc/init.d/solr {start|stop|restart}"
        exit 1
        ;;
esac

exit 0
