# Prograde Oort Setup Script
# This script automates backend and frontend dependency installation.

Write-Host "--- Prograde Oort: Starting Creation Act ---" -ForegroundColor Cyan

# 0. Check PHP Version
$phpOutput = php -r "echo PHP_VERSION;" 2>$null
$phpVersionMatch = [regex]::Match($phpOutput, '\d+\.\d+\.\d+')
if (-not $phpVersionMatch.Success) {
    Write-Error "Could not determine PHP version. Output: $phpOutput"
    exit 1
}
$phpVersion = $phpVersionMatch.Value
if ([version]$phpVersion -lt [version]"8.2.0") {
    Write-Error "PHP 8.2.0 or higher is required. Found: $phpVersion"
    exit 1
}

# 1. Install Composer dependencies
if (Get-Command "composer" -ErrorAction SilentlyContinue) {
    Write-Host "[1/3] Running Composer Install..." -ForegroundColor Yellow
    composer install --no-dev --optimize-autoloader
} else {
    Write-Warning "Composer not found! Please install it to use backend enhancements (Monolog)."
}

# 2. Install NPM dependencies
if (Get-Command "npm" -ErrorAction SilentlyContinue) {
    Write-Host "[2/3] Running NPM Install..." -ForegroundColor Yellow
    npm install
} else {
    Write-Warning "NPM not found! Please install it to manage admin assets."
}

# 3. Build Assets
if (Test-Path "package.json") {
    Write-Host "[3/3] Building Assets..." -ForegroundColor Yellow
    npm run build
}

Write-Host "--- Setup Completed! ---" -ForegroundColor Green
