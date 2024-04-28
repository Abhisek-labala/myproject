
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
            $state=$_POST['state'];
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
                            $demopassword ="password";
                            $password =password_hash($demopassword,PASSWORD_DEFAULT);
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
                    }}

                    //delinking the files from the folder
                $oldImagePath = "/Xampp/htdocs/myProject/uploads/" . $oldImageFilename;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
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