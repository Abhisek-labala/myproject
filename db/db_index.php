<?php
require "./db_connection.php";


if (isset($_GET['action'])) {

    $action = $_GET['action'];

    // FETCHING
    if ($action == "fetch") {
        include 'db_connection.php';

        $sql = "SELECT r.id, r.name, r.username, r.password, r.email, r.phone, r.address, r.gender, r.dob, 
                       co.country_name AS country, s.state_name AS state, r.hobbies, r.image_url 
                FROM regforms r
                JOIN countries co ON r.country = co.id
                JOIN states s ON r.state = s.sid";

        $result = pg_query($conn, $sql);

        $data = array();

        if (!$result) {
            echo "An error occurred.\n";
            exit;
        }
        if (pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $row['image_url'] = '<img src="/myproject/uploads/' . $row['image_url'] . '" width="50" height="50">';
                $data[] = $row;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($data);
    }


    // INSERTION
    elseif ($action == "insert") {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            $name = $_POST['name'];
            $username = $_POST['username'];
            // $password = $_POST['password'];
            $dob = $_POST['dob'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $address = $_POST['address'];
            $gender = $_POST['gender'];
            $country = $_POST['country'];
            $state = $_POST['state'];
            $hobbies = $_POST['hobbies'];
            $query_username = "SELECT username FROM regforms WHERE username='$username'";
            $result_username = pg_query($conn, $query_username);


            if ($_FILES['fileToUpload']['name'] != '') {
                $filename = $_FILES['fileToUpload']['name'];
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $valid_extension = array("jpg", "jpeg", "png", "gif");
                $max_file_size = 5242880; // 5MB (in bytes)

                // Check if file extension is valid
                if (in_array($extension, $valid_extension)) {
                    // Check if file size is within the limit
                    if ($_FILES['fileToUpload']['size'] <= $max_file_size) {
                        $new_name = rand() . "." . $extension;
                        $path = "/Xampp/htdocs/myProject/uploads/" . $new_name;

                        // Move the uploaded file to the destination directory
                        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $path)) {
                            $demopassword = "password";
                            $password = password_hash($demopassword, PASSWORD_DEFAULT);
                            $query = "INSERT INTO regforms (name,username,password, dob, phone, email, address, gender, country,state, hobbies, image_url) 
                                      VALUES ('$name','$username','$password', '$dob', '$phone', '$email', '$address', '$gender', '$country','$state', '$hobbies', '$new_name')";
                            // Execute the query to insert data into the database
                        } else {
                            // Handle file upload failure
                            echo "File upload failed.";
                        }
                    } else {
                        // Handle file size exceeding the limit
                        echo "File size exceeds the limit.";
                    }
                } else {
                    // Handle invalid file extension
                    echo "Invalid file extension.";
                }
            }


            $result = pg_query($conn, $query);

            if ($result) {
                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => "data insertion successful."
                    )
                );
            } else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "Error inserting data!"
                    )
                );
            }

        } else {
            echo json_encode(
                array(
                    "message" => "Invalid request."
                )
            );
            exit;
        }
    }

    //UPDATION
    elseif ($action == "update") {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Check if id is set in $_POST
            if (isset($_POST['hidden'])) {
                $id = $_POST['hidden'];

                // Initialize array to store key-value pairs for the SET clause
                $setValues = array();

                // Loop through $_POST to construct setValues array
                foreach ($_POST as $key => $value) {
                    // Skip id field, fileToUpload field, and hidden field
                    if ($key !== 'id' && $key !== 'fileToUpload' && $key !== 'hidden') {
                        // Escape values to prevent SQL injection
                        $value = pg_escape_string($conn, $value);
                        // Add key-value pair to setValues array
                        $setValues[] = "$key = '$value'";
                    }
                }
                $id = $_POST['hidden'];

                // Fetch the old image filename from the database
                $oldImageQuery = "SELECT image_url FROM regforms WHERE id = $id";
                $oldImageResult = pg_query($conn, $oldImageQuery);
                $oldImageRow = pg_fetch_assoc($oldImageResult);
                $oldImageFilename = $oldImageRow['image_url'];


                // Check if a new file is uploaded and old image exists
                // if ($_FILES['fileToUpload']['name'] != '' && !empty($oldImageFilename)) {
                if ($_FILES['fileToUpload']['name'] != '') {
                    $filename = $_FILES['fileToUpload']['name'];
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $valid_extension = array("jpg", "jpeg", "png", "gif");
                    $max_file_size = 5242880;
                    if (in_array($extension, $valid_extension)) {
                        if ($_FILES['fileToUpload']['size'] <= $max_file_size) {
                            $newFileName = rand() . "." . $extension;
                            $path = "/Xampp/htdocs/myProject/uploads/" . $newFileName;
                            if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $path)) {
                                // Add the image_url to setValues array
                                $setValues[] = "image_url = '$newFileName'";
                            }
                        }
                    }

                    //delinking the files from the folder
                    if (!empty($oldImageFilename)) {

                        $oldImagePath = "/Xampp/htdocs/myProject/uploads/" . $oldImageFilename;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }

                // Construct the SET clause by joining setValues array elements
                $setClause = implode(', ', $setValues);

                // Check if $setClause is empty
                if (empty($setClause)) {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "No fields provided for updating!"
                        )
                    );
                    exit;
                }

                // Construct the SQL query with the SET clause
                $query = "UPDATE regforms SET $setClause WHERE id = $id";

                // Execute the query
                $result = pg_query($conn, $query);

                if ($result !== false) {
                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => "Data updated successfully."
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "status" => "error",
                            "message" => "Error updating data!"
                        )
                    );
                }
            } else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "ID is not provided!"
                    )
                );
            }
        } else {
            echo json_encode(
                array(
                    "status" => "error",
                    "message" => "Invalid request."
                )
            );

            exit;
        }
    }
    // DELETION
    elseif ($action == "delete") {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if 'id' parameter is present in the request
            if (isset($_GET['id'])) {
                $id = $_GET['id'];
            } else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "no user id given."
                    )
                );
                exit;
            }
            //retriving the img file    
            $query = "SELECT image_url FROM regforms WHERE id = $id";
            $image_result = pg_query($conn, $query);
            $image_row = pg_fetch_assoc($image_result);
            $image_filename = $image_row['image_url'];

            // Delete the image file
            if (!empty($image_filename)) {
                $image_path = "/xampp/htdocs/myproject/uploads/$image_filename";
                if (file_exists($image_path)) {
                    unlink($image_path); // Delete the file
                }
            }

            $query = "DELETE FROM regforms WHERE id = $id";

            // executing query
            $result = pg_query($conn, $query);

            if ($result !== false) {
                echo json_encode(
                    array(
                        "status" => "success",
                        "message" => "Data deleted successfully."
                    )
                );
            } else {
                echo json_encode(
                    array(
                        "status" => "error",
                        "message" => "Error deleting data!"
                    )
                );
            }

        } else {
            echo "invalid request.";
        }
    } elseif ($action == "getcountry") {
        if (isset($_GET['country_name'])) {
            $countryName = $_GET['country_name'];


            // Query to select states corresponding to the selected country name using JOIN
            $query = "SELECT * FROM countries WHERE country_name= '$countryName'";
            $result = pg_query($conn, $query);

            // Check if there are any states in the database
            if ($result && pg_num_rows($result) > 0) {
                $country = array();
                // Fetch states and store them in an array
                while ($row = pg_fetch_assoc($result)) {
                    $country[] = $row;
                }
                // Send the states data as JSON response
                echo json_encode($country);
            } else {
                echo json_encode(array()); // Send an empty array if no states found
            }
        } else {
            echo json_encode(array()); // Send an empty array if country_name is not set
        }
    } elseif ($action == "getcountries") {
        $query = "SELECT * FROM countries";
        $result = pg_query($conn, $query);

        // Check if there are any countries in the database
        if ($result && pg_num_rows($result) > 0) {
            $countries = array();
            // Output options for each country
            while ($row = pg_fetch_assoc($result)) {
                $countries[] = $row;
            }
            echo json_encode($countries);
        } else {
            // No countries found
            echo "[]";
        }
    } elseif ($action == "getstate") {
        if (isset($_POST['country_name'])) {
            $countryName = $_POST['country_name'];

            // Escape the country name to prevent SQL injection
            $escapedCountryName = pg_escape_string($countryName);

            // Query to select states corresponding to the selected country name using JOIN
            $query = "SELECT s.* FROM states s INNER JOIN countries c ON s.country_id = c.id WHERE c.country_name = '$escapedCountryName'";
            $result = pg_query($conn, $query);

            // Check if there are any states in the database
            if ($result && pg_num_rows($result) > 0) {
                $states = array();
                // Fetch states and store them in an array
                while ($row = pg_fetch_assoc($result)) {
                    $states[] = $row;
                }
                // Send the states data as JSON response
                echo json_encode($states);
            } else {
                echo json_encode(array()); // Send an empty array if no states found
            }
        } else {
            echo json_encode(array()); // Send an empty array if country_name is not set
        }
    } elseif ($action == "getstates") {
        if (isset($_POST['country_id'])) {
            $countryId = $_POST['country_id'];

            // Query to select states corresponding to the selected country
            $query = "SELECT * FROM states WHERE country_id = $countryId";
            $result = pg_query($conn, $query);

            // Check if there are any states in the database
            if ($result && pg_num_rows($result) > 0) {
                $states = array();
                // Fetch states and store them in an array
                while ($row = pg_fetch_assoc($result)) {
                    $states[] = $row;
                }
                // Send the states data as JSON response
                echo json_encode($states);
            } else {
                echo json_encode(array()); // Send an empty array if no states found
            }
        } else {
            echo json_encode(array()); // Send an empty array if country_id is not set
        }
    } elseif ($action == "validate") {

        $insertCount = 0;
        $updateCount = 0;

        $response = array();

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
                       
                        $email = $rowData[1]; // Assuming email is in the second column
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Error: Invalid email format in row " . ($key + 1) . ".";
                        }
                    }

                    if (!empty($errors)) {
                        $response['errors'] = $errors;
                        echo json_encode($response);
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
                        //eamil validation
                        $rows = array_slice($objPHPExcel->getActiveSheet()->toArray(), 1); // Exclude the header row

                        foreach ($rows as $key => $row) {
                            $email = $row[1]; 
                            $date = $row[3];
                        
                            if (empty($email)) {
                                $emptyCells[] = "Row: " . ($key + 2) . ", Column: 2"; // Adjust row number
                            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $errors[] = "Error: Invalid email format in row " . ($key + 2) . ".";
                            }
                            if (!empty($date)) {
                                $dateObj = DateTime::createFromFormat('Y-m-d', $date);
                                if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
                                    $errors[] = "Error: Invalid date format in row " . ($key + 2) . ". It should be in yyyy-mm-dd format.";
                                }
                            } else {
                                $emptyCells[] = "Row: " . ($key + 2) . ", Column: 3"; // Adjust row number
                            }
                        }
                        
                    if (!empty($errors)) {
                        $response['errors'] = $errors;
                        echo json_encode($response);
                        exit;
                    }
                }

                // header template comparing
                $templateHeaders = array('name', 'email', 'phone', 'dob', 'address', 'country', 'state', 'username', 'gender', 'hobbies');

                if ($uploadedHeaders !== $templateHeaders) {
                    $response['error'] = "Uploaded file doesn't match the template. Please upload the correct file.";
                    echo json_encode($response);
                    exit;
                }

                $response['message'] = "File matches the template.";
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

                $errors = array();

                foreach ($uploadedHeaders as $index => $header) {
                    if ($header === 'country') {
                        $columnIndex = $index;
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0 && !in_array($row[$columnIndex], $countries)) {
                                $errors[] = "Error: Country '{$row[$columnIndex]}' in row " . ($key + 1) . " is not present in the reference data.";
                            }
                        }
                    } elseif ($header === 'state') {
                        $columnIndex = $index;
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0 && !in_array($row[$columnIndex], $states)) {
                                $errors[] = "Error: State '{$row[$columnIndex]}' in row " . ($key + 1) . " is not present in the reference data.";
                            }
                        }
                    } elseif ($header === 'gender') {
                        $columnIndex = $index;
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0 && !in_array($row[$columnIndex], $genders)) {
                                $errors[] = "Error: Gender '{$row[$columnIndex]}' in row " . ($key + 1) . " is not present in the reference data.";
                            }
                        }
                    } elseif ($header === 'hobbies') {
                        $columnIndex = $index;
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0) {
                                $hobbiesList = explode(',', $row[$columnIndex]);
                                foreach ($hobbiesList as $hobby) {
                                    // Trim each hobby to remove any leading or trailing whitespace
                                    $hobby = trim($hobby);
                                    if (!in_array($hobby, $hobbies)) {
                                        $errors[] = "Error: Hobby '{$hobby}' in row " . ($key + 1) . " is not present in the reference data.";
                                    }
                                }
                            }
                        }
                    } elseif ($header === 'username') {
                        $columnIndex = $index;
                        $usernames = array();
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0) {
                                $username = $row[$columnIndex];
                                if (in_array($username, $usernames)) {
                                    $errors[] = "Error: Duplicate entry found for username '{$username}' in row " . ($key + 1) . ".";
                                } else {
                                    $usernames[] = $username;
                                }
                            }
                        }
                    } elseif ($header === 'phone') {
                        $columnIndex = $index;
                        foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                            if ($key !== 0) {
                                $phone = $row[$columnIndex];
                                if (!is_numeric($phone)) {
                                    $errors[] = "Error: Phone number '{$phone}' in row " . ($key + 1) . " is not numeric.";
                                }
                            }
                        }
                    }
                }

                if (!empty($errors)) {
                    $response['errors'] = $errors;
                    echo json_encode($response);
                    exit;
                }

                // Display data from the Excel file
                $tableHTML = '<table>';
                $tableHTML .= '<thead><tr>';
                foreach ($uploadedHeaders as $header) {
                    $tableHTML .= '<th>' . $header . '</th>';
                }
                $tableHTML .= '</tr></thead><tbody>';
                foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                    if ($key !== 0) { // Skip the header row
                        $tableHTML .= '<tr>';
                        foreach ($row as $value) {
                            $tableHTML .= '<td>' . $value . '</td>';
                        }
                        $tableHTML .= '</tr>';
                    }
                }
                $tableHTML .= '</tbody></table><br>';
                $tableHTML .= '<button type="button" class="btn btn-success" id="uploadButton">Upload</button>';

                $response['tableHTML'] = $tableHTML;

                // Display empty cell indices
                if (!empty($emptyCells)) {
                    $response['emptyCells'] = $emptyCells;
                }
            } else {
                $response['error'] = "Please upload a valid file (CSV, XLS, or XLSX).";
            }
        } else {
            $response['error'] = "Error uploading file.";
        }

        echo json_encode($response);
    } elseif ($action == "excelupload") {
        require '../PHPExcel-v7.4/PHPExcel/IOFactory.php'; // Include PHPExcel library for handling Excel files
        require '../PHPExcel-v7.4/PHPExcel.php';

        $response = array(); // Initialize response array

        // Check if the file was uploaded successfully
        if ($_FILES['spreedsheetfile']['error'] === UPLOAD_ERR_OK) {
            // Access uploaded file details
            $uploadedFileName = $_FILES['spreedsheetfile']['name'];
            $uploadedFileTmp = $_FILES['spreedsheetfile']['tmp_name'];

            $insertCount = 0;
            $objPHPExcel = PHPExcel_IOFactory::load($uploadedFileTmp); // Load Excel file

            // Example code to insert data into the database
            foreach ($objPHPExcel->getActiveSheet()->toArray() as $key => $row) {
                if ($key !== 0) { // Skip header row
                    // Check if the index exists before accessing it
                    if (isset($row[9])) {
                        $username = $row[7];
                        $name = $row[0];
                        $email = $row[1];
                        $phone = $row[2];
                        $dob = $row[3];
                        $address = $row[4];
                        $country = $row[5];
                        $state = $row[6];
                        $gender = $row[8];
                        $hobbies = $row[9];
                        $pass = "password";
                        $phash = password_hash($pass, PASSWORD_DEFAULT);

                        // Check if hobbies are set and convert to string

                        // Check if user already exists
                        $sql = "SELECT * FROM regforms WHERE username = '$username'";
                        $result = pg_query($conn, $sql);
                        $count = pg_fetch_assoc($result);
                        if ($count == 0) {
                            // Insert data into the database
                            $sql2 = "INSERT INTO regforms (name, email, phone, dob, password, address, country, state, username, gender, hobbies)
                                    VALUES ('$name', '$email', '$phone', '$dob', '$phash', '$address', '$country', '$state', '$username', '$gender', '$hobbies')";
                            $result2 = pg_query($conn, $sql2);
                            if (!$result2) {
                                $response['error'] = "Data is not inserted into database";
                            } else {
                                $response['insertCount'] = $insertCount++;
                            }
                        } else {
                            $response['error'] = "Data Already present in the database";
                        }
                    } else {
                        // Handle missing data or move to the next row
                        continue;
                    }
                }
            }

            // Prepare the response

            // $response['updateCount'] = $updateCount;
            // $response['message'] = "Data insertion completed.";

            echo json_encode($response); // Return response as JSON
        } else {
            // Handle file upload error
            $response['error'] = "File upload error: " . $_FILES['spreedsheetfile']['error'];
            echo json_encode($response); // Return error response as JSON
        }
    } else {
        echo json_encode(
            array(
                "message" => "Invalid operation requested."
            )
        );
        exit;
    }

} else {
    echo json_encode(
        array(
            "message" => "Invalid request to server."
        )
    );
    exit;
}

pg_close($conn);

?>