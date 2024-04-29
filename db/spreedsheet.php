<?php
// Include the PHPExcel library
require '../PHPExcel-v7.4/PHPExcel.php';
require 'db_connection.php';

// Create a new Spreadsheet object
$spreadsheet = new PHPExcel();
$spreadsheet->getProperties()
    ->setCreator('Abhisek')
    ->setTitle('userinfo')
    ->setLastModifiedBy('Abhisek')
    ->setDescription('user data updation')
    ->setSubject('user information')
    ->setKeywords('phpexcel implementation')
    ->setCategory('importing');

$ews = $spreadsheet->getSheet(0);
$ews->setTitle('Userdata');

$ews->setCellValue('a1', 'name');
$ews->setCellValue('b1', 'email');
$ews->setCellValue('c1', 'phone');
$ews->setCellValue('d1', 'dob');
$ews->setCellValue('e1', 'address');
$ews->setCellValue('f1', 'country');
$ews->setCellValue('g1', 'state');
$ews->setCellValue('h1', 'username');
$ews->setCellValue('i1', 'gender');
$ews->setCellValue('j1', 'hobbies');

$header = 'a1:j1';
$ews->getStyle($header)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
$style = array(
    'font' => array('bold' => true, ),
    'alignment' => array('horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER, ),
);
$ews->getStyle($header)->applyFromArray($style);

// Set column widths
for ($col = ord('a'); $col <= ord('j'); $col++) {
    $ews->getColumnDimension(chr($col))->setAutoSize(true);
}

$ews2 = new PHPExcel_Worksheet($spreadsheet, 'Reference');
$spreadsheet->addSheet($ews2, 0);
$ews2->setTitle('Reference');

// Add headers for additional columns
$ews2->setCellValue('a1', 'Country ID');
$ews2->setCellValue('b1', 'Country Name');
$ews2->setCellValue('c1', 'State ID');
$ews2->setCellValue('d1', 'State Name');
$ews2->setCellValue('e1', 'c_id');
$ews2->setCellValue('f1', 'Hobbies');
$ews2->setCellValue('g1', 'Gender');

$headerReference = 'a1:g1';
$ews2->getStyle($headerReference)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ff0000');
$ews2->getStyle($headerReference)->applyFromArray($style);

// Set column widths for Reference sheet
for ($col = ord('a'); $col <= ord('g'); $col++) {
    $ews2->getColumnDimension(chr($col))->setAutoSize(true);
}

// Fetch hobbies and gender data
$hobbiesArray = array(
    1 => 'Cricket',
    2 => 'Football',
    3 => 'Dancing',
    4 => 'Travelling',
    5 => 'Indoor games'
);

$genderArray = array(
    1 => 'Male',
    2 => 'Female',
);

// Populate hobbies and gender data in the Reference sheet
$row = 2; // Start from row 2 (after headers)
foreach ($hobbiesArray as $id => $hobby) {
    $ews2->setCellValue('f' . $row, $hobby);
    $row++;
}

$row = 2; // Reset row for gender
foreach ($genderArray as $id => $gender) {
    $ews2->setCellValue('g' . $row, $gender);
    $row++;
}

// Assuming you have tables named 'countries' and 'states' with columns 'id' and 'name'
$countryQuery = "SELECT id, country_name FROM countries";
$stateQuery = "SELECT sid, state_name,country_id FROM states";

$countryResult = pg_query($conn, $countryQuery);
$stateResult = pg_query($conn, $stateQuery);

// Fetch country and state data
$row = 2; // Start from row 2 (after headers)
while ($countryRow = pg_fetch_assoc($countryResult)) {
    $ews2->setCellValue('a' . $row, $countryRow['id']);
    $ews2->setCellValue('b' . $row, $countryRow['country_name']);
    $row++;
}

$row = 2; // Reset row for states
while ($stateRow = pg_fetch_assoc($stateResult)) {
    $ews2->setCellValue('c' . $row, $stateRow['sid']);
    $ews2->setCellValue('d' . $row, $stateRow['state_name']);
    $ews2->setCellValue('e' . $row, $stateRow['country_id']);
    $row++;
}

$userData = []; // Replace this with your actual data retrieval logic

$row = 2; // Start from row 2 (after headers)
foreach ($userData as $user) {
    $ews->setCellValue('a' . $row, $user['name']);
    $ews->setCellValue('b' . $row, $user['email']);
    $ews->setCellValue('c' . $row, $user['phone']);
    $ews->setCellValue('d' . $row, $user['dob']);
    $ews->setCellValue('e' . $row, $user['address']);
    $ews->setCellValue('f' . $row, $user['country']);
    $ews->setCellValue('g' . $row, $user['state']);
    $ews->setCellValue('h' . $row, $user['username']);
    $ews->setCellValue('i' . $row, $genderArray[$user['gender']]);
    
    // Parse hobbies IDs and display them as strings
    $hobbies = explode(',', $user['hobbies']);
    $hobbiesString = '';
    foreach ($hobbies as $hobbyId) {
        if (isset($hobbiesArray[$hobbyId])) {
            $hobbiesString .= $hobbiesArray[$hobbyId] . ', ';
        }
    }
    $hobbiesString = rtrim($hobbiesString, ', '); // Remove trailing comma and space
    $ews->setCellValue('j' . $row, $hobbiesString);

    $row++;
}

// Close database connection
pg_close($conn);

// Set headers to prompt file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template.xlsx"');
header('Cache-Control: max-age=0');

$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel2007');
ob_end_clean();
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
?>
