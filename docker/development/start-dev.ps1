Param(
    [string]$certs = "unsigned"
)

$composeCmd = @("docker", "compose", "-f", "docker-compose.dev.yml")

$GREEN = "`e[32m"
$RED   = "`e[31m"
$NC    = "`e[0m"

$certsDir = "./certs"

Write-Host "${GREEN}Installing Evently...${NC}"

# Ensure certs directory exists
if (-not (Test-Path $certsDir)) {
    New-Item -ItemType Directory -Path $certsDir | Out-Null
}

function Generate-UnsignedCerts {
    if (-not (Test-Path "$certsDir/localhost.crt") -or -not (Test-Path "$certsDir/localhost.key")) {
        Write-Host "${GREEN}Generating unsigned SSL certificates...${NC}"
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 `
            -keyout "$certsDir/localhost.key" `
            -out "$certsDir/localhost.crt" `
            -subj "/CN=localhost"
    } else {
        Write-Host "${GREEN}SSL certificates already exist, skipping...${NC}"
    }
}

function Generate-SignedCerts {
    if (-not (Test-Path "$certsDir/localhost.crt") -or -not (Test-Path "$certsDir/localhost.key")) {
        if (-not (Get-Command mkcert -ErrorAction SilentlyContinue)) {
            Write-Host "${RED}mkcert not installed. Install from https://github.com/FiloSottile/mkcert#installation${NC}"
            exit 1
        }
        Write-Host "${GREEN}Generating signed SSL certificates with mkcert...${NC}"
        mkcert -key-file "$certsDir/localhost.key" -cert-file "$certsDir/localhost.crt" localhost 127.0.0.1 ::1
    } else {
        Write-Host "${GREEN}SSL certificates already exist, skipping...${NC}"
    }
}

switch ($certs) {
    "signed" { Generate-SignedCerts }
    default  { Generate-UnsignedCerts }
}

# Start docker compose
& $composeCmd "up" "-d"
if ($LASTEXITCODE -ne 0) {
    Write-Host "${RED}Failed to start services with docker-compose.${NC}"
    exit 1
}

# Install composer deps inside backend
Write-Host "${GREEN}Running composer install in backend...${NC}"
& $composeCmd "exec" "-T" "backend" "composer" "install" "--ignore-platform-reqs" "--no-interaction" "--optimize-autoloader" "--prefer-dist"
if ($LASTEXITCODE -ne 0) {
    Write-Host "${RED}Composer install failed.${NC}"
    exit 1
}

# Wait for Postgres
Write-Host "${GREEN}Waiting for the database...${NC}"
do {
    Start-Sleep -Seconds 2
    $logs = & $composeCmd "logs" "pgsql"
} until ($logs -match "ready to accept connections")

# Ensure .env files exist
& $composeCmd "exec" "backend" sh -c "test -f .env || cp .env.example .env"
& $composeCmd "exec" "frontend" sh -c "test -f .env || cp .env.example .env"

# Laravel setup
& $composeCmd "exec" "backend" "php" "artisan" "key:generate"
& $composeCmd "exec" "backend" "php" "artisan" "migrate" "--force"
& $composeCmd "exec" "backend" "php" "artisan" "storage:link"

if ($LASTEXITCODE -ne 0) {
    Write-Host "${RED}Migrations failed.${NC}"
    exit 1
}

Write-Host "${GREEN}Evently is now running at:${NC} https://localhost:8443"

Start-Process "https://localhost:8443/auth/register"
