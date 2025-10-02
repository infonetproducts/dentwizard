@echo off
echo ====================================
echo Starting DentWizard React App
echo ====================================
echo.
echo This will connect to the LIVE API at:
echo https://dentwizard.lgstore.com/lg/API
echo.
echo Using development authentication mode (no SSO required)
echo.

cd /d "%~dp0"

if not exist "node_modules" (
    echo Installing dependencies...
    echo This may take a few minutes...
    echo.
    call npm install
    echo.
    echo Dependencies installed!
    echo.
)

if not exist ".env" (
    echo ERROR: .env file not found!
    echo Please ensure .env file exists with API configuration
    pause
    exit /b 1
)

echo ====================================
echo Starting the application...
echo The app will open at http://localhost:3000
echo.
echo Press Ctrl+C to stop the server
echo ====================================
echo.

npm start