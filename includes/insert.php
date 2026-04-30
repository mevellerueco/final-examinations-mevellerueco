<?php
include 'db.php';

$name      = trim($_POST['name']);
$surname   = trim($_POST['surname']);
$middlename = trim($_POST['middlename']);
$address   = trim($_POST['address']);
$contact   = trim($_POST['contact']);

$stmt = $conn->prepare("INSERT INTO students (name, surname, middlename, address, contact_number) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $surname, $middlename, $address, $contact);

if ($stmt->execute()) {
    header("Location: ../MyFinal_Exam/index.php?status=success&section=create");
} else {
    header("Location: ../MyFinal_Exam/index.php?status=error&section=create");
}
$stmt->close();
$conn->close();
?>
