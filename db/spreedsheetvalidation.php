<?php
require_once 'db_connection.php';
if ($_FILES['spreedsheetfile']['error'] === UPLOAD_ERR_OK) {
    // Access uploaded file details
    $uploadedFileName = $_FILES['spreedsheetfile']['name'];
    $uploadedFileTmp = $_FILES['spreedsheetfile']['tmp_name'];
    $uploadedFileType = $_FILES['spreedsheetfile']['type'];

    // Check file type (mime type or file extension)
    if (in_array($uploadedFileType, array('application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'))) {
        // Load PHPExcel library for XLS and XLSX files
        require '../PHPExcel-v7.4/PHPExcel/IOFactory.php';

        // Load the uploaded file
        if ($uploadedFileType === 'text/csv') {
            // For CSV files, read using PHP built-in functions or libraries like fgetcsv()
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

        // Compare headers with your template
        $templateHeaders = array('ID', 'name', 'email', 'phone', 'dob', 'address', 'country', 'state', 'username', 'gender', 'hobbies');

        if ($uploadedHeaders !== $templateHeaders) {
            echo "Error: Uploaded file doesn't match the template. Please upload the correct file.";
            exit;
        }

        echo "File uploaded successfully and matches the template.";

        // Display empty cell indices
        if (!empty($emptyCells)) {
            echo "<br>Empty Cells:<br>";
            foreach ($emptyCells as $emptyCell) {
                echo $emptyCell . "<br>";
            }
        }

        // If all validations are successful, insert data into the database

        // Insert data into the database
        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
            if ($key !== 0) { // Skip the header row
                $id = pg_escape_string($row[0]);
                $name = pg_escape_string($row[1]);
                $email = pg_escape_string($row[2]);
                $phone = pg_escape_string($row[3]);
                $dob = pg_escape_string($row[4]);
                $address = pg_escape_string($row[5]);
                $country = pg_escape_string($row[6]);
                $state = pg_escape_string($row[7]);
                $username = pg_escape_string($row[8]);
                $gender = pg_escape_string($row[9]);
                $hobbies = pg_escape_string($row[10]);

                $sql = "INSERT INTO regforms (ID, name, email, phone, dob, address, country, state, username, gender, hobbies)
                        VALUES ('$id', '$name', '$email', '$phone', '$dob', '$address', '$country', '$state', '$username', '$gender', '$hobbies')";

                $result = pg_query($conn, $sql);
                if (!$result) {
                    echo "Error inserting data: " . pg_last_error($conn);
                    exit;
                }
            }
        }

        echo "Data inserted successfully.";

        pg_close($conn);
    } else {
        echo "Error: Please upload a valid file (CSV, XLS, or XLSX).";
    }
} else {
    echo "Error uploading file.";
}
?>
