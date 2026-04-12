<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "Applying pending career movements...\n";
applyPendingCareerMovements($conn);
echo "Done.\n";
?>
