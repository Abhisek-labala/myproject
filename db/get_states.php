<?php
include 'db_connection.php';

// Check if country_id is set in the POST data
if(isset($_POST['country_id'])) {
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
?>
