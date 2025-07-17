@echo off
REM Windows Development Setup Script for CI4-API-JWT

echo 🚀 Setting up Development Environment...

REM Check if Composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Composer not found. Please install Composer first.
    echo Download from: https://getcomposer.org/download/
    pause
    exit /b 1
)

REM Check if PHP is available
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHP not found. Please install PHP or configure PATH.
    echo Suggestion: Use XAMPP or download from https://php.net/downloads
    pause
    exit /b 1
)

echo ✅ PHP and Composer found

REM Install dependencies
echo 📦 Installing Composer dependencies...
composer install

REM Copy environment file if not exists
if not exist ".env" (
    echo ⚙️ Creating environment file...
    copy "env-example" ".env"
    echo ✅ Environment file created. Please edit .env with your local configuration.
) else (
    echo ⚙️ Environment file already exists.
)

REM Set writable permissions (Windows style)
echo 🔒 Setting directory permissions...
if exist "writable" (
    attrib -R writable\* /S /D
    echo ✅ Writable directory permissions set.
)

REM Generate application key if needed
echo 🔑 Generating application key...
php spark key:generate

REM Create database file for SQLite (development)
if not exist "database" mkdir database
if not exist "database\database.sqlite" (
    echo 🗃️ Creating SQLite database for development...
    type nul > database\database.sqlite
)

REM Run database migrations
echo 🗃️ Running database migrations...
php spark migrate

REM Clear cache
echo 🧹 Clearing cache...
php spark cache:clear

echo.
echo ✅ Development environment setup complete!
echo.
echo 📋 Next steps:
echo 1. Edit .env file with your local database configuration
echo 2. Start development server: php spark serve
echo 3. Access your app at: http://localhost:8080
echo.
echo 🔧 Development commands:
echo   php spark serve           # Start development server
echo   php spark migrate         # Run database migrations  
echo   php spark db:seed         # Seed database with test data
echo   composer test             # Run PHPUnit tests
echo   php spark cache:clear     # Clear application cache
echo.
pause
