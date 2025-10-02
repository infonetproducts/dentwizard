@echo off
echo ====================================
echo DentWizard React Demo App
echo ====================================
echo.
echo This is a standalone demo with mock data.
echo No backend API or Azure AD required!
echo.

cd /d "%~dp0"

if not exist "node_modules" (
    echo Installing dependencies...
    echo This may take a few minutes...
    echo.
    call npm install
    echo.
    echo ✅ Dependencies installed!
    echo.
)

echo ====================================
echo Starting the demo app...
echo The app will open automatically at http://localhost:3000
echo.
echo Press Ctrl+C to stop the server
echo ====================================
echo.

npm start