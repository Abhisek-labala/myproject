<?php
include 'db_connection.php';

// Check if country_name is set in the POST data
if(isset($_GET['country_name'])) {
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
?>
