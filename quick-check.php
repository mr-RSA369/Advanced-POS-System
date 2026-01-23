<?php
// SIMPLE: Just try COM3 (90% of Bixolon printers use COM3 on Windows)
$port = 'COM3';

echo "Testing Bixolon on $port...\n";

$handle = @fopen($port, 'wb');
if ($handle) {
    echo "✅ Port $port opened!\n";

    // Bixolon specific commands
    $cmd = chr(27) . chr(64);  // ESC @ - Initialize
    $cmd .= chr(27) . chr(97) . chr(1);  // ESC a 1 - Center
    $cmd .= "BIXOLON TEST\n";
    $cmd .= date('Y-m-d H:i:s') . "\n";
    $cmd .= "Port: $port\n";
    $cmd .= chr(10) . chr(10);  // Feed lines
    $cmd .= chr(29) . chr(86) . chr(0);  // GS V 0 - Cut

    $bytes = fwrite($handle, $cmd);
    fclose($handle);

    echo "✅ $bytes bytes sent to printer\n";
    echo "👉 CHECK YOUR PRINTER NOW!\n";
    echo "📝 If printed, use '$port' in your Laravel code\n";
} else {
    echo "❌ $port failed, trying COM4...\n";

    $port = 'COM4';
    $handle = @fopen($port, 'wb');
    if ($handle) {
        echo "✅ Port $port opened!\n";

        $cmd = chr(27) . chr(64) . "BIXOLON ON COM4\n" . chr(29) . chr(86) . chr(0);
        fwrite($handle, $cmd);
        fclose($handle);

        echo "✅ Test sent to $port\n";
        echo "👉 CHECK YOUR PRINTER!\n";
    } else {
        echo "❌ No printer found on COM3 or COM4\n";
        echo "🔧 Check Device Manager for COM port number\n";
    }
}
