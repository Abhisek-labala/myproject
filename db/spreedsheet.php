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

$ews->setCellValue('a1', 'ID'); // Sets cell 'a1' to value 'ID 
$ews->setCellValue('b1', 'name');
$ews->setCellValue('c1', 'email');
$ews->setCellValue('d1', 'phone');
$ews->setCellValue('e1', 'dob');
$ews->setCellValue('f1', 'address');
$ews->setCellValue('g1', 'country');
$ews->setCellValue('h1', 'state');
$ews->setCellValue('i1', 'username');
$ews->setCellValue('j1', 'gender');
$ews->setCellValue('k1', 'hobbies');

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

$ews2 = new \PHPExcel_Worksheet($spreadsheet, 'Reference');
$spreadsheet->addSheet($ews2, 0);
$ews2->setTitle('Reference');

// Add headers for additional columns
$ews2->setCellValue('a1', 'Country ID');
$ews2->setCellValue('b1', 'Country Name');
$ews2->setCellValue('c1', 'State ID');
$ews2->setCellValue('d1', 'State Name');
$ews2->setCellValue('e1', 'Hobbies');
$ews2->setCellValue('f1', 'Gender');

$headerReference = 'a1:f1';
$ews2->getStyle($headerReference)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('00ffff00');
$ews2->getStyle($headerReference)->applyFromArray($style);

// Set column widths for Reference sheet
for ($col = ord('a'); $col <= ord('f'); $col++) {
    $ews2->getColumnDimension(chr($col))->setAutoSize(true);
}



// Assuming you have tables named 'countries' and 'states' with columns 'id' and 'name'
$countryQuery = "SELECT id, country_name FROM countries";
$stateQuery = "SELECT sid, state_name FROM states";

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
    $row++;
}
$hobbiesArray = array(
    1 => 'Reading',
    2 => 'Cooking',
    3 => 'Sports',
    4 => 'Gardening',
    // Add more hobbies as needed
);

$genderArray = array(
    1 => 'Male',
    2 => 'Female',
    3 => 'Other',
    // Add more genders as needed
);


// Close database connection
pg_close($conn);

// Set headers to prompt file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="template.xlsx"');
header('Cache-Control: max-age=0');

$writer = \PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel2007');
ob_end_clean();
$writer->setIncludeCharts(true);
$writer->save('php://output');
exit;
?>