<?php
header('Content-Type: text/plain');

$host = 'localhost';
$user = 'rwaf';
$pass = 'Py*uhb$L$##';
$db = 'rwaf';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

echo "=== INVESTIGATION REPORT ===\n\n";

echo "1. ORDERS TABLE STRUCTURE:\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("DESCRIBE Orders");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-25s %-20s %-10s %s\n", $row['Field'], $row['Type'], $row['Key'], $row['Extra']);
}

echo "\n2. ORDERITEMS TABLE STRUCTURE:\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("DESCRIBE OrderItems");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-25s %-20s %-10s %s\n", $row['Field'], $row['Type'], $row['Key'], $row['Extra']);
}

echo "\n3. RECENT ORDERS (Last 5):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, OrderID, UserID, OrderDate, order_total, Status FROM Orders ORDER BY OrderDate DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo sprintf("DB_ID: %d | OrderID: %s | UserID: %d | Date: %s | Total: $%.2f | Status: %s\n", 
        $row['ID'], $row['OrderID'], $row['UserID'], $row['OrderDate'], $row['order_total'], $row['Status']);
}

echo "\n4. ORDER ITEMS FOR MOST RECENT ORDER:\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, OrderID FROM Orders ORDER BY OrderDate DESC LIMIT 1");
$latestOrder = $result->fetch_assoc();
if ($latestOrder) {
    echo "Latest Order: ID=" . $latestOrder['ID'] . ", OrderID=" . $latestOrder['OrderID'] . "\n\n";
    
    $result = $mysqli->query("SELECT OrderRecordID, FormID, ItemID, FormDescription, Quantity, Price, size_item, color_item, artwork_logo 
                             FROM OrderItems WHERE OrderRecordID = " . $latestOrder['ID']);
    while ($row = $result->fetch_assoc()) {
        echo sprintf("OrderRecordID: %d | FormID: %s | ItemID: %d | Desc: %s | Qty: %d | Price: $%.2f\n", 
            $row['OrderRecordID'], $row['FormID'], $row['ItemID'], substr($row['FormDescription'], 0, 40), 
            $row['Quantity'], $row['Price']);
        echo sprintf("  Size: %s | Color: %s | Logo: %s\n", $row['size_item'], $row['color_item'], $row['artwork_logo']);
    }
}

echo "\n5. CHECK USER ID 20296 (Jamie Krugger):\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, Name, Email, BudgetBalance FROM Users WHERE ID = 20296");
if ($user = $result->fetch_assoc()) {
    echo sprintf("User: %s (%s) | Budget: $%.2f\n", $user['Name'], $user['Email'], $user['BudgetBalance']);
}

echo "\n6. ORDERS FOR USER 20296:\n";
echo str_repeat("-", 80) . "\n";
$result = $mysqli->query("SELECT ID, OrderID, OrderDate, order_total, Status FROM Orders WHERE UserID = 20296 ORDER BY OrderDate DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    echo sprintf("DB_ID: %d | OrderID: %s | Date: %s | Total: $%.2f | Status: %s\n", 
        $row['ID'], $row['OrderID'], $row['OrderDate'], $row['order_total'], $row['Status']);
}

echo "\n7. SAMPLE ORDER ID ANALYSIS:\n";
echo str_repeat("-", 80) . "\n";
echo "Example OrderID from user: 0930-095443-17668\n";
echo "Format breakdown:\n";
echo "  0930 = September 30 (month-day)\n";
echo "  095443 = 09:54:43 (time)\n";
echo "  17668 = ??? (UserID or DB auto-increment ID?)\n\n";

// Check if 17668 is a DB ID or User ID
$result = $mysqli->query("SELECT ID, OrderID, UserID FROM Orders WHERE ID = 17668");
if ($row = $result->fetch_assoc()) {
    echo "If 17668 is DB ID: Found order with ID=17668, OrderID={$row['OrderID']}, UserID={$row['UserID']}\n";
} else {
    echo "No order found with DB ID = 17668\n";
}

$result = $mysqli->query("SELECT ID, OrderID, UserID FROM Orders WHERE UserID = 17668");
if ($row = $result->fetch_assoc()) {
    echo "If 17668 is UserID: Found order with ID={$row['ID']}, OrderID={$row['OrderID']}, UserID=17668\n";
} else {
    echo "No order found with UserID = 17668\n";
}

$mysqli->close();
?>
