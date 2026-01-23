@echo off
echo ==========================================
echo BIXOLON PRINTER DETECTOR
echo ==========================================
echo.

echo Step 1: Checking Device Manager for Bixolon...
wmic path Win32_PnPEntity where "Name like '%%Bixolon%%'" get Name,Status /value
wmic path Win32_PnPEntity where "Name like '%%SRP-352%%'" get Name,Status /value
wmic path Win32_PnPEntity where "Name like '%%SRP352%%'" get Name,Status /value

echo.
echo Step 2: Checking USB devices...
wmic path Win32_USBControllerDevice get Dependent /format:list | findstr "BIXOLON\|SRP-352"

echo.
echo Step 3: Finding COM port for Bixolon...
reg query "HKLM\HARDWARE\DEVICEMAP\SERIALCOMM" /s | findstr "BIXOLON\|SRP"

echo.
echo Step 4: List ALL USB devices with Vendor ID...
powershell -Command "Get-WmiObject Win32_USBControllerDevice | ForEach-Object { [Wmi]$_.Dependent } | Select-Object Name,DeviceID | Where-Object { \$_.DeviceID -like '*04E8*' } | Format-List"

echo.
echo ==========================================
echo MANUAL CHECK:
echo ==========================================
echo 1. Open Device Manager (Win + X > Device Manager)
echo 2. Look for:
echo    - "Ports (COM & LPT)"
echo    - Check each "USB Serial Port (COMx)"
echo 3. Look for:
echo    - "Universal Serial Bus controllers"
echo    - Find "BIXOLON" or "USB Printing Support"
echo.
pause
