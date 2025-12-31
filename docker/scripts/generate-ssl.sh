#!/bin/bash
#
# Script tự động tạo SSL certificate cho Nginx
# Sử dụng: ./generate-ssl.sh [domain]
#

set -e

# Lấy domain từ argument hoặc sử dụng default
DOMAIN=${1:-localhost}
SSL_DIR="docker/nginx/ssl"

echo "========================================"
echo "SSL Certificate Generator"
echo "========================================"
echo "Domain: $DOMAIN"
echo "SSL Directory: $SSL_DIR"
echo ""

# Tạo thư mục SSL nếu chưa tồn tại
mkdir -p "$SSL_DIR"

# Kiểm tra xem certificate đã tồn tại chưa
if [ -f "$SSL_DIR/nginx-selfsigned.crt" ] && [ -f "$SSL_DIR/nginx-selfsigned.key" ]; then
    echo "⚠️  SSL certificates already exist!"
    read -p "Do you want to regenerate? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Skipping SSL generation."
        exit 0
    fi
fi

# Generate self-signed certificate
echo "📝 Generating self-signed SSL certificate..."
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout "$SSL_DIR/nginx-selfsigned.key" \
  -out "$SSL_DIR/nginx-selfsigned.crt" \
  -subj "/C=VN/ST=HCM/L=HoChiMinh/O=KhaoSat/OU=IT/CN=$DOMAIN"

if [ $? -eq 0 ]; then
    echo "✅ SSL certificate created successfully!"
else
    echo "❌ Failed to create SSL certificate"
    exit 1
fi

# Generate DH parameters for stronger security
echo ""
echo "🔐 Generating DH parameters (this may take a while)..."
openssl dhparam -out "$SSL_DIR/dhparam.pem" 2048

if [ $? -eq 0 ]; then
    echo "✅ DH parameters created successfully!"
else
    echo "❌ Failed to create DH parameters"
    exit 1
fi

# Set appropriate permissions
chmod 600 "$SSL_DIR/nginx-selfsigned.key"
chmod 644 "$SSL_DIR/nginx-selfsigned.crt"
chmod 644 "$SSL_DIR/dhparam.pem"

# Display certificate information
echo ""
echo "========================================"
echo "Certificate Information"
echo "========================================"
openssl x509 -in "$SSL_DIR/nginx-selfsigned.crt" -noout -subject -dates

echo ""
echo "========================================"
echo "✅ SSL Setup Complete!"
echo "========================================"
echo "Certificate: $SSL_DIR/nginx-selfsigned.crt"
echo "Private Key: $SSL_DIR/nginx-selfsigned.key"
echo "DH Params:   $SSL_DIR/dhparam.pem"
echo ""
echo "⚠️  Note: This is a self-signed certificate."
echo "   For production, use Let's Encrypt or a trusted CA."
echo ""
