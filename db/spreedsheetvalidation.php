<?php
require_once 'db_connection.php';
$insertCount = 0;
$updateCount = 0;

if ($_FILES['spreedsheetfile']['error'] === UPLOAD_ERR_OK) {
    // Access uploaded file details
    $uploadedFileName = $_FILES['spreedsheetfile']['name'];
    $uploadedFileTmp = $_FILES['spreedsheetfile']['tmp_name'];
    $uploadedFileType = $_FILES['spreedsheetfile']['type'];

    // Check file type
    if (in_array($uploadedFileType, array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'))) {
        // Load PHPExcel library for XLS and XLSX files
        require '../PHPExcel-v7.4/PHPExcel/IOFactory.php';

        // Load the uploaded file
        if ($uploadedFileType === 'text/csv') {
            // For CSV files, read using PHP built-in functions
            $fileData = file_get_contents($uploadedFileTmp);
            $csvData = str_getcsv($fileData, "\n"); // Assuming each row is separated by newline
            $uploadedHeaders = str_getcsv($csvData[0]); // Assuming headers are in the first row

            // Perform data validation for CSV files
            $errors = array();
            $emptyCells = array(); // Collect empty cell indices
            foreach ($csvData as $key => $row) {
                $rowData = str_getcsv($row);
                foreach ($rowData as $index => $value) {
                    if (empty($value)) {
                        $errors[] = "Error: Field '{$uploadedHeaders[$index]}' in row " . ($key + 1) . " is empty.";
                        $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1); // Collect empty cell indices
                    }
                }
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo $error . "<br>";
                }
                exit;
            }
        } else {
            // For XLS and XLSX files, use PHPExcel library
            $objPHPExcel = PHPExcel_IOFactory::load($uploadedFileTmp);
            $uploadedHeaders = $objPHPExcel->getActiveSheet()->toArray()[0];

            // Perform data validation for XLS and XLSX files
            $errors = array();
            $emptyCells = array(); // Collect empty cell indices
            foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                foreach ($row as $index => $value) {
                    if ($key === 0 && empty($value)) {
                        $errors[] = "Error: Field '{$uploadedHeaders[$index]}' in row " . ($key + 1) . " is empty.";
                    }
                    if (empty($value)) {
                        $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1); // Collect empty cell indices
                    }
                }
                // Check if any cell in the row is empty
                if (count(array_filter($row)) !== count($row)) {
                    $emptyCells[] = "Row: " . ($key + 1) . ", Column: " . ($index + 1);
                }
            }

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo $error . "<br>";
                }
                exit;
            }
        }

        // header template comparing
        $templateHeaders = array('name', 'email', 'phone', 'dob', 'address', 'country', 'state', 'username', 'gender', 'hobbies');

        if ($uploadedHeaders !== $templateHeaders) {
            echo "Error: Uploaded file doesn't match the template. Please upload the correct file.";
            exit;
        }

        echo "File matches the template.";
        $referenceSheet = $objPHPExcel->getSheetByName('Reference');

        // Get reference data for countries
        $highestRow = $referenceSheet->getHighestRow(); // Get the highest row number
        $countries = array();
        for ($row = 2; $row <= $highestRow; $row++) {
            $country = $referenceSheet->getCell('A' . $row)->getValue();
            $countries[] = $country;
        }

        // Get reference data for states
        $states = array();
        for ($row = 2; $row <= $highestRow; $row++) {
            $state = $referenceSheet->getCell('C' . $row)->getValue();
            $states[] = $state;
        }

        // Get reference data for hobbies
        $hobbies = array();
        for ($row = 2; $row <= $highestRow; $row++) {
            $hobby = $referenceSheet->getCell('F' . $row)->getValue();
            $hobbies[] = $hobby;
        }

        // Get reference data for genders
        $genders = array();
        for ($row = 2; $row <= $highestRow; $row++) {
            $gender = $referenceSheet->getCell('G' . $row)->getValue();
            $genders[] = $gender;
        }

        // Compare reference data with uploaded file data
        $validInsertions = true;
        foreach ($uploadedHeaders as $header) {
            if ($header === 'country') {
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    if ($key !== 0 && !in_array($row[5], $countries)) {
                        echo "Error: Country '{$row[5]}' in row " . ($key + 1) . " is not present in the reference data.<br>";
                        $validInsertions = false;
                    }
                }
            } elseif ($header === 'state') {
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    if ($key !== 0 && !in_array($row[6], $states)) {
                        echo "Error: State '{$row[6]}' in row " . ($key + 1) . " is not present in the reference data.<br>";
                        $validInsertions = false;
                    }
                }
            } elseif ($header === 'gender') {
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    if ($key !== 0 && !in_array($row[8], $genders)) {
                        echo "Error: Gender '{$row[8]}' in row " . ($key + 1) . " is not present in the reference data.<br>";
                        $validInsertions = false;
                    }
                }
            } elseif ($header === 'hobbies') {
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    $hobbiesList = explode(',', $row[9]);
                    if ($key !== 0) {
                        $hobbiesList = explode(',', $row[9]);
                        foreach ($hobbiesList as $hobby) {
                            // Trim each hobby to remove any leading or trailing whitespace
                            $hobby = trim($hobby);
                            if (!in_array($hobby, $hobbies)) {
                                echo "Error: Hobby '{$hobby}' in row " . ($key + 1) . " is not present in the reference data.<br>";
                                $validInsertions = false;
                            }
                        }
                    }
                }
            }
        }

        if (!$validInsertions) {
            echo "Some records contain errors. Aborting insertion.";
            exit;
        }

        // Display empty cell indices
        if (!empty($emptyCells)) {
            echo "<br>Empty Cells:<br>";
            foreach ($emptyCells as $emptyCell) {
                echo $emptyCell . "<br>";
            }
        }

        $existingusernames = array();
        $query = "SELECT username FROM regforms";
        $result = pg_query($conn, $query);
        if ($result) {
            while ($row = pg_fetch_assoc($result)) {
                $existingusernames[] = $row['username'];
            }
        } else {
            echo "Error retrieving existing usernames: " . pg_last_error($conn);
            exit;
        }

        // Create an array to store encountered usernames
        $excelUsernames = array();

        // insertion and updating into database
        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
            if ($key !== 0) { // Skip the header row
                // Check if any value in the row is empty
                if (in_array('', $row)) {
                    echo "Error: Row " . ($key + 1) . " contains empty values. Skipping insertion for this record.<br>";
                    continue;
                }
                
                // Extracting data from the row
                $name = pg_escape_string($row[0]);
                $email = pg_escape_string($row[1]);
                $phone = pg_escape_string($row[2]);
                $dob = pg_escape_string($row[3]);
                $address = pg_escape_string($row[4]);
                $country = pg_escape_string($row[5]);
                $state = pg_escape_string($row[6]);
                $username = pg_escape_string($row[7]);
                $gender = pg_escape_string($row[8]);
                $hobbies = pg_escape_string($row[9]);
                $pass = "password";
                $phash = password_hash($pass, PASSWORD_DEFAULT);

                // Check if the username exists in the Excel file
                if (in_array($username, $excelUsernames)) {
                    echo "Error: Username '$username' already exists in the Excel file. Skipping insertion for this record.<br>";
                    continue;
                }

                // Check if the username exists in the database
                if (in_array($username, $existingusernames)) {
                    echo "Error: Username '$username' already exists in the database. Skipping insertion for this record.<br>";
                    continue;
                }

                // If username is unique, proceed with insertion
                $sql = "INSERT INTO regforms (name, email, phone, dob, password, address, country, state, username, gender, hobbies)
                        VALUES ('$name', '$email', '$phone', '$dob', '$phash', '$address', '$country', '$state', '$username', '$gender', '$hobbies')";
                $insertCount++;

                $result = pg_query($conn, $sql);
                if (!$result) {
                    echo "Error inserting data: " . pg_last_error($conn);
                    exit;
                }

                // Add the username to the excelUsernames array
                $excelUsernames[] = $username;
            }
        }

        echo "Data inserted: $insertCount";

        pg_close($conn);
    } else {
        echo "Error: Please upload a valid file (CSV, XLS, or XLSX).";
    }
} else {
    echo "Error uploading file.";
}
?>
