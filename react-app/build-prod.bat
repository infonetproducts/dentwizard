@echo off
echo ====================================
echo Building DentWizard React App for Production
echo ====================================
echo.

REM Check if node_modules exists
if not exist "node_modules" (
    echo Installing dependencies...
    echo.
    call npm install
    echo.
    echo Dependencies installed!
    echo.
)

REM Check if .env file exists
if not exist ".env" (
    echo WARNING: .env file not found!
    echo Please create .env file with production values
    echo.
    pause
)

echo Building production bundle...
echo.

call npm run build

echo.
echo ====================================
echo Build complete!
echo Production files are in the 'build' folder
echo ====================================
echo.
echo To deploy:
echo 1. Copy contents of 'build' folder to your web server
echo 2. Ensure server is configured for single-page apps
echo    (all routes should serve index.html)
echo.
pause