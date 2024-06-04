<?php
use Carbon\Carbon;
require 'WarehouseItem.php';
function getUsers() {
    $json = file_get_contents('users.json');
    return json_decode($json, true);
}
function authenticateUser($access_code): ?array
{
    $users = getUsers();
    foreach ($users as $user) {
        if ($user['access_code'] == $access_code) {
            return [
                  'id' => $user['id'],
                  'username' => $user['username']
            ];
        }
    }
    return null;
}
function printMenu()
{
    echo "Warehouse\n";
    echo "1. Add new item\n";
    echo "2. Update item quantity\n";
    echo "3. Display all items\n";
    echo "4. Display all logs\n";
    echo "5. Delete item\n";
    echo "6. exit\n";
}
function addItem($userID)
{
    $name = readline("Enter item name: ");
    $quantity = (int)readline("Enter item quantity: ");
    $item = new WarehouseItem($quantity, $name);
    $item->save();
    echo "Item added successfully!\n";
    $newData = [
          'user_id' => $userID,
          'warehouse_item_id' => $item->getId(),
          'time' => Carbon::now('UTC')->toDateTimeString(),
          'description' => "added warehouse item $name"
    ];
    $jsonData = file_get_contents('logs.json');
    $logData = json_decode($jsonData, true);
    $logData[] = $newData;
    $newJsonData = json_encode($logData, JSON_PRETTY_PRINT);
    file_put_contents('logs.json', $newJsonData);
}
function updateItemQuantity($userID)
{
    $id = (int)readline("Enter item ID to update: ");
    $items = WarehouseItem::loadItems();
    $item = getItemById($items, $id);
    if ($item) {
        $newQuantity = (int)readline("Enter new quantity: ");
        $item->setQuantity($newQuantity);
        $item->save();
        echo "Item quantity updated successfully!\n";
        $newData = [
              'user_id' => $userID,
              'warehouse_item_id' => $id,
              'time' => Carbon::now('UTC')->toDateTimeString(),
              'description' => "changed the items amount to $newQuantity"
        ];
        $jsonData = file_get_contents('logs.json');
        $logData = json_decode($jsonData, true);
        $logData[] = $newData;
        $newJsonData = json_encode($logData, JSON_PRETTY_PRINT);
        file_put_contents('logs.json', $newJsonData);
    } else {
        echo "Item not found!\n";
    }
}
function displayAllLogs()
{

    $jsonData = file_get_contents('logs.json');
    $logData = json_decode($jsonData, true);

    if (empty($logData)) {
        echo "No logs found!\n";
    } else {
        foreach ($logData as $log) {
            echo "User ID: {$log['user_id']}, 
            Warehouse Item ID: {$log['warehouse_item_id']}, 
            Time: {$log['time']}, 
            Description: {$log['description']}\n";
        }
    }
}
function displayAllItems()
{
    $items = WarehouseItem::loadItems();
    if (empty($items)) {
        echo "No items found!\n";
    } else {
        foreach ($items as $itemArray) {
            $item = WarehouseItem::fromArray($itemArray);
            echo "ID: " . $item->getId() .
                  ", Name: " . $item->getName() .
                  ", Quantity: " . $item->getQuantity() .
                  ", Date Created: " . $item->getDateCreated()->toDateTimeString() .
                  ", Last Modified: " . $item->getLastModified()->toDateTimeString() . "\n";

        }
    }
}
function deleteItemById(int $id, int $userID): bool
{
    $items = WarehouseItem::loadItems();
    $itemFound = false;
    $deletedItemName = null;
    foreach ($items as $key => $item) {
        if ($item['id'] === $id) {
            $deletedItemName = $item['name'];
            unset($items[$key]);
            $itemFound = true;
            break;
        }
    }
    if ($itemFound) {
        $updatedJsonData = json_encode(array_values($items), JSON_PRETTY_PRINT);
        file_put_contents('warehouse_items.json', $updatedJsonData);

        // Logging
        $newData = [
              'user_id' => $userID,
              'warehouse_item_id' => $id,
              'time' => Carbon::now('UTC')->toDateTimeString(),
              'description' => "deleted warehouse item $deletedItemName"
        ];
        $jsonData = file_get_contents('logs.json');
        $logData = json_decode($jsonData, true);
        $logData[] = $newData;
        $newJsonData = json_encode($logData, JSON_PRETTY_PRINT);
        file_put_contents('logs.json', $newJsonData);

        return true;
    } else {
        return false;
    }
}
function getItemById(array $items, int $id): ?WarehouseItem
{
    foreach ($items as $itemData) {
        if ($itemData['id'] == $id) {
            return WarehouseItem::fromArray($itemData);
        }
    }
    return null;
}

$access_code = readline("Enter your access code: ");
$userData = authenticateUser($access_code);
if ($userData !== null) {
    $currentUserID = $userData['id'];
    $currentUserName = $userData['username'];
    echo "Welcome, " . $currentUserName . "!\n";
    while (true) {
        printMenu();
        $choice = (int)readline("Enter your choice: ");
        switch ($choice) {
            case 1:
                addItem($currentUserID);
                break;
            case 2:
                updateItemQuantity($currentUserID);
                break;
            case 3:
                displayAllItems();
                break;
            case 4:
                displayAllLogs();
                break;
            case 6:
                echo "Goodbye!\n";
                exit;
            case 5:
                $id = (int)readline("Enter item ID to delete: ");
                deleteItemById($id, $currentUserID);
                break;
            default:
                echo "Invalid choice. Please try again.\n";
        }
    }
} else {
    echo "Invalid access code.\n";
    exit;
}




