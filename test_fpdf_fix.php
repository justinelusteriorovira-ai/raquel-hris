<?php
require_once 'includes/plugins/fpdf/fpdf.php';

try {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Test Regular
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Testing Helvetica Regular...', 0, 1);
    
    // Test Bold
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Testing Helvetica Bold!', 0, 1);
    
    // Test Italic
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Testing Helvetica Italic...', 0, 1);
    
    // Test Bold Italic
    $pdf->SetFont('Arial', 'BI', 12);
    $pdf->Cell(0, 10, 'Testing Helvetica Bold Italic!', 0, 1);
    
    echo "SUCCESS: Fonts loaded correctly.\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
?>
