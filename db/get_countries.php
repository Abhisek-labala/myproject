<?php
require_once 'db_connection.php';
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
pg_close($conn);            
?>