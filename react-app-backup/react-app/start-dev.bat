@echo off
echo ====================================
echo Starting DentWizard React App
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
    echo Please create .env file from .env.example
    echo.
    pause
)

echo Starting development server...
echo App will open at http://localhost:3000
echo.
echo Press Ctrl+C to stop the server
echo ====================================
echo.

npm start