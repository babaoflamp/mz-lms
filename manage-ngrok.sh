#!/bin/bash

# Persistent ngrok setup script for Moodle
# This script sets up ngrok to run as a service

MOODLE_DIR="/home/scottk/Projects/moodle"
NGROK_PID_FILE="/tmp/ngrok.pid"
NGROK_LOG_FILE="/tmp/ngrok.log"

function start_ngrok() {
    echo "🚀 Starting ngrok..."
    
    # Kill any existing ngrok processes
    pkill -f "ngrok http" 2>/dev/null
    sleep 1
    
    # Start ngrok in background with static domain
    cd $MOODLE_DIR
    ngrok http 8888 --domain=mz-lms.ngrok.app > $NGROK_LOG_FILE 2>&1 &
    echo $! > $NGROK_PID_FILE
    
    # Wait for ngrok to start
    sleep 3
    
    # Get the HTTPS URL
    NGROK_URL=$(curl -s http://localhost:4040/api/tunnels 2>/dev/null | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4 | head -1)
    
    if [ -z "$NGROK_URL" ]; then
        echo "❌ Failed to start ngrok. Retrying..."
        sleep 2
        NGROK_URL=$(curl -s http://localhost:4040/api/tunnels 2>/dev/null | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4 | head -1)
    fi
    
    if [ -n "$NGROK_URL" ]; then
        echo "✅ ngrok started successfully"
        echo "🔗 HTTPS URL: $NGROK_URL"
        echo $NGROK_URL > /tmp/ngrok_url.txt
        update_moodle_config "$NGROK_URL"
    else
        echo "❌ Failed to get ngrok URL"
        echo "Log:"
        tail -20 $NGROK_LOG_FILE
        exit 1
    fi
}

function update_moodle_config() {
    local NGROK_URL=$1
    local CONFIG_FILE="$MOODLE_DIR/config.php"
    
    echo "📝 Updating Moodle configuration..."
    
    # Check if config already has ngrok URL
    if grep -q "NGROK_URL = '$NGROK_URL'" "$CONFIG_FILE"; then
        echo "✅ Config already updated with this URL"
        return
    fi
    
    # Backup config.php
    cp "$CONFIG_FILE" "${CONFIG_FILE}.backup.$(date +%s)" 2>/dev/null
    
    # Update config
    sed -i "s|\$NGROK_URL = '[^']*'|\$NGROK_URL = '$NGROK_URL'|g" "$CONFIG_FILE"
    
    echo "✅ Config updated with ngrok URL"
}

function stop_ngrok() {
    echo "🛑 Stopping ngrok..."
    if [ -f "$NGROK_PID_FILE" ]; then
        kill $(cat "$NGROK_PID_FILE") 2>/dev/null
        rm -f "$NGROK_PID_FILE"
    else
        pkill -f "ngrok http" 2>/dev/null
    fi
    echo "✅ ngrok stopped"
}

function show_url() {
    if [ -f "/tmp/ngrok_url.txt" ]; then
        cat "/tmp/ngrok_url.txt"
    else
        NGROK_URL=$(curl -s http://localhost:4040/api/tunnels 2>/dev/null | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4 | head -1)
        if [ -n "$NGROK_URL" ]; then
            echo $NGROK_URL
        else
            echo "ngrok is not running"
        fi
    fi
}

# Main script logic
case "${1:-start}" in
    start)
        start_ngrok
        ;;
    stop)
        stop_ngrok
        ;;
    restart)
        stop_ngrok
        sleep 1
        start_ngrok
        ;;
    url)
        show_url
        ;;
    logs)
        tail -f $NGROK_LOG_FILE
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|url|logs}"
        exit 1
        ;;
esac
