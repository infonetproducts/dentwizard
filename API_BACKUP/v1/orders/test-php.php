<?php
echo "PHP is working\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Testing database connection...\n";

$conn = @mysqli_connect("localhost", "rwaf", "Py*uhb$L$##", "rwaf");
if ($conn) {
    echo "Database connected OK\n";
    mysqli_close($conn);
} else {
    echo "Database connection failed\n";
}
?>