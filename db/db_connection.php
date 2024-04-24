<?php
$conn_string = "host=localhost port=5432 dbname=myproject user=postgres password=abhisek";
$conn = pg_connect($conn_string);

if(!$conn)
{
    die;
}
?>