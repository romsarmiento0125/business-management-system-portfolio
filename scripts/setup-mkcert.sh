#!/usr/bin/env bash
# Run this on the machine where Docker runs. It creates a cert and places it in ./certs/.
# Requires mkcert: https://github.com/FiloSottile/mkcert

set -e

# Adjust these SANs if you use different hostnames/IPs
HOST_IP="192.168.254.109"
SAN_LIST="$HOST_IP localhost 127.0.0.1"

echo "Generating mkcert cert for: $SAN_LIST"
mkdir -p certs
mkcert -install
mkcert -cert-file certs/dev.pem -key-file certs/dev.key $SAN_LIST

echo "Created certs/dev.pem and certs/dev.key"
echo "Now run: docker compose up -d"
