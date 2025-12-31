# PowerShell Script để tạo SSL Certificate cho Nginx
# Sử dụng: .\generate-ssl.ps1 [-Domain "example.com"]

param(
    [string]$Domain = "localhost"
)

$ErrorActionPreference = "Stop"

$SSL_DIR = "docker\nginx\ssl"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SSL Certificate Generator" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Domain: $Domain" -ForegroundColor Yellow
Write-Host "SSL Directory: $SSL_DIR" -ForegroundColor Yellow
Write-Host ""

# Kiểm tra OpenSSL
try {
    $null = openssl version
} catch {
    Write-Host "❌ Error: OpenSSL is not installed or not in PATH" -ForegroundColor Red
    Write-Host "Please install OpenSSL from: https://slproweb.com/products/Win32OpenSSL.html" -ForegroundColor Yellow
    exit 1
}

# Tạo thư mục SSL nếu chưa tồn tại
if (-not (Test-Path -Path $SSL_DIR)) {
    New-Item -ItemType Directory -Path $SSL_DIR -Force | Out-Null
    Write-Host "✅ Created SSL directory" -ForegroundColor Green
}

# Kiểm tra certificates đã tồn tại
$certPath = Join-Path $SSL_DIR "nginx-selfsigned.crt"
$keyPath = Join-Path $SSL_DIR "nginx-selfsigned.key"

if ((Test-Path $certPath) -and (Test-Path $keyPath)) {
    Write-Host "⚠️  SSL certificates already exist!" -ForegroundColor Yellow
    $response = Read-Host "Do you want to regenerate? (y/N)"
    if ($response -ne 'y' -and $response -ne 'Y') {
        Write-Host "Skipping SSL generation." -ForegroundColor Yellow
        exit 0
    }
}

# Generate self-signed certificate
Write-Host ""
Write-Host "📝 Generating self-signed SSL certificate..." -ForegroundColor Cyan

$subject = "/C=VN/ST=HCM/L=HoChiMinh/O=KhaoSat/OU=IT/CN=$Domain"

try {
    & openssl req -x509 -nodes -days 365 -newkey rsa:2048 `
        -keyout "$SSL_DIR\nginx-selfsigned.key" `
        -out "$SSL_DIR\nginx-selfsigned.crt" `
        -subj $subject

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ SSL certificate created successfully!" -ForegroundColor Green
    } else {
        throw "OpenSSL command failed"
    }
} catch {
    Write-Host "❌ Failed to create SSL certificate: $_" -ForegroundColor Red
    exit 1
}

# Generate DH parameters
Write-Host ""
Write-Host "🔐 Generating DH parameters (this may take a while)..." -ForegroundColor Cyan

try {
    & openssl dhparam -out "$SSL_DIR\dhparam.pem" 2048

    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ DH parameters created successfully!" -ForegroundColor Green
    } else {
        throw "OpenSSL command failed"
    }
} catch {
    Write-Host "❌ Failed to create DH parameters: $_" -ForegroundColor Red
    exit 1
}

# Display certificate information
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Certificate Information" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

& openssl x509 -in "$SSL_DIR\nginx-selfsigned.crt" -noout -subject -dates

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "✅ SSL Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Certificate: $SSL_DIR\nginx-selfsigned.crt" -ForegroundColor Yellow
Write-Host "Private Key: $SSL_DIR\nginx-selfsigned.key" -ForegroundColor Yellow
Write-Host "DH Params:   $SSL_DIR\dhparam.pem" -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  Note: This is a self-signed certificate." -ForegroundColor Yellow
Write-Host "   For production, use Let's Encrypt or a trusted CA." -ForegroundColor Yellow
Write-Host ""
