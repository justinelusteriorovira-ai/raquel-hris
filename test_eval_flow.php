<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set fake session data for Staff (user_id = 4 usually?)
$_SESSION['user_id'] = 4; // Assuming 4 is staff. Let's find real IDs later if needed.
$_SESSION['role'] = 'HR Staff';
$_SESSION['branch_id'] = 2; // Assuming Cabuyao

// Instead of doing hard requires which might redirect, let's use cURL to hit the endpoints with correct cookies.
// We'll write a function to login, get cookie, and post data.

function loginAndPost($email, $password, $url, $postData) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'hris_cookie');
    
    // 1. Login
    $ch = curl_init("http://localhost/raquel-hris/index.php");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => $email, 'password' => $password, 'login' => '1']));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    $response = curl_exec($ch);
    curl_close($ch);
    
    // 2. Post to URL
    $ch2 = curl_init($url);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_POST, true);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieFile);
    // don't follow redirects so we can see the location header
    curl_setopt($ch2, CURLOPT_HEADER, true);
    $response2 = curl_exec($ch2);
    
    $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);
    
    // cleanup
    unlink($cookieFile);
    
    return ['status' => $httpCode, 'response' => $response2];
}

// ---------------------------------------------------------
// STEP 2: STAFF SUBMITS EVALUATION 
// ---------------------------------------------------------
echo "Attempting to submit evaluation as staff...\n";
$evalData = [
    'employee_id' => 512,
    'template_id' => 1,
    'evaluation_type' => 'Annual',
    'period_start' => '2026-01-01',
    'period_end' => '2026-12-31',
    // Let's pass some dummy score data. We need actual criterion IDs for template 1.
    // For testing backend robustness, we'll try to pass criteria 1,2,3 for KRA, 4,5 for beh
    'kra_scores' => [1 => 4, 2 => 4, 3 => 4],
    'beh_scores' => [4 => 4, 5 => 4],
    'career_growth_suited' => 1,
    'desired_position' => 'Senior Tester',
    'current_position' => 'Junior Developer',
    'months_in_position' => 12,
    'dev_area' => ['Test Area'],
    'dev_support' => ['Test Support'],
    'dev_timeframe' => ['1 year'],
    'staff_comments' => 'Automated test sub',
    'submit_action' => 'submit'
];

$res = loginAndPost('staff@raquel.com', 'password', 'http://localhost/raquel-hris/staff/submit-evaluation.php', $evalData);
echo "Staff eval submit response: " . $res['status'] . "\n";
// Extract Location header to see where it redirected
if (preg_match('/^Location: (.*)$/i', $res['response'], $matches)) {
    echo "Redirect: " . trim($matches[1]) . "\n";
} else {
    echo "No redirect! Response might contain an error. Checking contents briefly...\n";
    echo substr($res['response'], 0, 500) . "\n";
}

?>
