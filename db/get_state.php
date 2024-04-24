<?php
include 'db_connection.php';

// Check if country_name is set in the POST data
if(isset($_POST['country_name'])) {
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
?>
