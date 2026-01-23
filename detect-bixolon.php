<?php
echo "==========================================\n";
echo "BIXOLON SRP-352III+ PRINTER DETECTION\n";
echo "==========================================\n\n";

// Bixolon USB Vendor ID: 0x04E8 (Samsung Electronics)
// Bixolon Product IDs: 0x6868, 0x6869, 0x686A

$detected = false;

// Method 1: Windows Management Instrumentation (WMI)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "Method 1: Checking Windows WMI...\n";

    // Check for Bixolon devices in Windows Registry
    exec('reg query "HKLM\SYSTEM\CurrentControlSet\Enum\USB" /s /f "Bixolon" 2>nul', $output, $return);

    if (!empty($output)) {
        foreach ($output as $line) {
            if (stripos($line, 'Bixolon') !== false || stripos($line, 'SRP-352') !== false) {
                echo "✅ Found in Registry: $line\n";
                $detected = true;

                // Extract COM port from registry
                preg_match('/COM\d+/', $line, $matches);
                if (!empty($matches)) {
                    echo "   ↳ COM Port: {$matches[0]}\n";
                }
            }
        }
    }

    // Check USB Vendor ID (04E8 is Bixolon/Samsung)
    exec('reg query "HKLM\SYSTEM\CurrentControlSet\Enum\USB\VID_04E8" /s 2>nul', $usbOutput, $usbReturn);

    if (!empty($usbOutput)) {
        echo "✅ Found USB device with Vendor ID 04E8 (Bixolon/Samsung)\n";
        $detected = true;

        foreach ($usbOutput as $line) {
            if (preg_match('/PID_686[89A]/', $line)) {
                echo "   ↳ Likely Bixolon SRP Series: $line\n";
            }
        }
    }
}

// Method 2: Try to communicate with printer on various ports
echo "\nMethod 2: Testing communication with printer...\n";

$ports = [];
// Add common ports
for ($i = 1; $i <= 20; $i++) {
    $ports[] = "COM$i";
}

// Also test USB ports
$ports = array_merge($ports, ['USB001', 'USB002', 'USB003', 'USB004']);

foreach ($ports as $port) {
    echo "Testing $port... ";

    $handle = @fopen($port, 'wb');
    if ($handle) {
        // Send Bixolon-specific initialization
        fwrite($handle, "\x1B\x40"); // ESC @ - Initialize

        // Send Bixolon status request
        fwrite($handle, "\x10\x04\x01"); // DLE EOT (Bixolon status request)

        // Try to read response (some printers respond)
        stream_set_timeout($handle, 1);
        $response = @fread($handle, 10);

        if (!empty($response)) {
            echo "✅ RESPONSE RECEIVED! (Hex: " . bin2hex($response) . ")\n";
            echo "   ↳ PRINTER DETECTED ON: $port\n";

            // Send test print
            fwrite($handle, "\x1B\x61\x01"); // Center
            fwrite($handle, "BIXOLON DETECTED\n");
            fwrite($handle, "Port: $port\n");
            fwrite($handle, date('H:i:s') . "\n\n");
            fwrite($handle, "\x1B\x69"); // Cut

            echo "   ↳ Test sent to printer - CHECK PAPER!\n";
            $detected = true;
        } else {
            // No response, but port opened - might still be printer
            echo "✓ PORT OPEN (no response)\n";

            // Try alternative Bixolon command
            fwrite($handle, "\x1B\x76"); // ESC v (Bixolon alternate status)

            $response2 = @fread($handle, 10);
            if (!empty($response2)) {
                echo "   ↳ Bixolon response: " . bin2hex($response2) . "\n";
            }
        }

        fclose($handle);
    } else {
        echo "✗\n";
    }
}

// Method 3: Use Windows PowerShell to detect
echo "\nMethod 3: PowerShell detection...\n";
$psScript = <<<'EOD'
$printers = Get-WmiObject -Class Win32_Printer | Where-Object {$_.Name -like "*Bixolon*" -or $_.Name -like "*SRP*"}
if ($printers) {
    foreach ($printer in $printers) {
        Write-Host "Found Printer: $($printer.Name)"
        Write-Host "Port: $($printer.PortName)"
        Write-Host "Status: $($printer.Status)"
    }
} else {
    Write-Host "No Bixolon printers found in Windows Printer list"
}

# Check USB devices
$usbDevices = Get-WmiObject Win32_PnPEntity | Where-Object {$_.Name -like "*Bixolon*" -or $_.Description -like "*Bixolon*"}
foreach ($device in $usbDevices) {
    Write-Host "USB Device: $($device.Name)"
    Write-Host "Description: $($device.Description)"
}
EOD;

// Save and execute PowerShell
file_put_contents('detect.ps1', $psScript);
exec('powershell -ExecutionPolicy Bypass -File detect.ps1 2>&1', $psOutput, $psReturn);

foreach ($psOutput as $line) {
    echo "PS: $line\n";
    if (stripos($line, 'Bixolon') !== false || stripos($line, 'SRP') !== false) {
        $detected = true;
    }
}
@unlink('detect.ps1');

// Summary
echo "\n" . str_repeat("=", 50) . "\n";
echo "DETECTION SUMMARY:\n";
echo str_repeat("=", 50) . "\n";

if ($detected) {
    echo "✅ BIXOLON PRINTER DETECTED!\n\n";
    echo "NEXT STEPS:\n";
    echo "1. Most likely port: COM3 or COM4\n";
    echo "2. Use this code in Laravel:\n";
    echo "```php\n";
    echo "\$printer = fopen('COM3', 'wb');\n";
    echo "fwrite(\$printer, \"\\x1B\\x40\"); // ESC @\n";
    echo "fwrite(\$printer, \"\\x1B\\x61\\x01BIXOLON PRINT\\n\");\n";
    echo "fwrite(\$printer, \"\\x1B\\x69\"); // Cut\n";
    echo "fclose(\$printer);\n";
    echo "```\n";
} else {
    echo "❌ BIXOLON PRINTER NOT DETECTED\n\n";
    echo "TROUBLESHOOTING:\n";
    echo "1. Make sure printer is TURNED ON\n";
    echo "2. Check USB cable connection\n";
    echo "3. Install Bixolon drivers from:\n";
    echo "   https://www.bixolon.com\n";
    echo "4. Check Windows Device Manager\n";
    echo "5. Try different USB port on computer\n\n";

    echo "QUICK FIX: Manually check COM port\n";
    echo "Run this in Command Prompt:\n";
    echo "  mode | find \"COM\"\n";
    echo "Then test with:\n";
    echo "  php -r \"\$h=fopen('COM3','wb');fwrite(\$h,'TEST');fclose(\$h);\"\n";
}

// Final test command
echo "\n" . str_repeat("=", 50) . "\n";
echo "QUICK TEST COMMANDS:\n";
echo str_repeat("=", 50) . "\n";
echo "1. Test COM3: php -r \"\$h=fopen('COM3','wb');fwrite(\$h,\"\\x1B\\x40\\x1B\\x61\\x01TEST\\n\\n\\x1B\\x69\");fclose(\$h);echo 'Sent!'\"\n";
echo "2. Test COM4: php -r \"\$h=fopen('COM4','wb');fwrite(\$h,\"\\x1B\\x40TEST\\n\\n\\x1B\\x69\");fclose(\$h);echo 'Sent!'\"\n";
echo "3. Test USB001: php -r \"\$h=fopen('USB001','wb');fwrite(\$h,\"\\x1B\\x40TEST\\n\\n\\x1B\\x69\");fclose(\$h);echo 'Sent!'\"\n";
