#!/bin/bash

# Script to setup ngrok and configure Moodle

echo "🚀 Setting up ngrok for Moodle..."

# Check if ngrok auth token is set
if ! ngrok config check > /dev/null 2>&1; then
    echo "⚠️  ngrok auth token not configured"
    echo "Please run: ngrok authtoken <your-token>"
    exit 1
fi

# Get the port where Moodle is running
MOODLE_PORT=8888
echo "📡 Starting ngrok on port $MOODLE_PORT..."

# Start ngrok and capture the public URL
ngrok http $MOODLE_PORT --log stdout > /tmp/ngrok.log 2>&1 &
NGROK_PID=$!
echo "ngrok started with PID: $NGROK_PID"
echo $NGROK_PID > /tmp/ngrok.pid

# Wait for ngrok to start
sleep 3

# Extract the HTTPS URL from ngrok
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*' | cut -d'"' -f4 | head -1)

if [ -z "$NGROK_URL" ]; then
    echo "❌ Failed to get ngrok URL. Checking logs..."
    cat /tmp/ngrok.log
    exit 1
fi

echo "✅ ngrok HTTPS URL: $NGROK_URL"

# Update Moodle config.php
CONFIG_FILE="/home/scottk/Projects/moodle/config.php"
echo "📝 Updating $CONFIG_FILE..."

# Backup config.php
cp $CONFIG_FILE ${CONFIG_FILE}.backup.$(date +%s)

# Replace the wwwroot configuration
sed -i "s|if (isset(\$_SERVER\['HTTP_HOST'\])) {|// ngrok configuration\n\$NGROK_URL = '$NGROK_URL';\nif (!empty(\$NGROK_URL)) {\n    \$CFG->wwwroot = \$NGROK_URL;\n} elseif (isset(\$_SERVER\['HTTP_HOST'\])) {|g" $CONFIG_FILE

echo "✅ Configuration updated!"
echo ""
echo "=========================================="
echo "📍 Moodle is now accessible at:"
echo "🔗 $NGROK_URL"
echo "=========================================="
echo ""
echo "To stop ngrok, run: kill $(cat /tmp/ngrok.pid) 2>/dev/null"
echo "Or: pkill ngrok"
