<?php
include 'db.php';

$id         = intval($_POST['id']);
$name       = trim($_POST['name']);
$surname    = trim($_POST['surname']);
$middlename = trim($_POST['middlename']);
$address    = trim($_POST['address']);
$contact    = trim($_POST['contact']);

$stmt = $conn->prepare("UPDATE students SET name=?, surname=?, middlename=?, address=?, contact_number=? WHERE id=?");
$stmt->bind_param("sssssi", $name, $surname, $middlename, $address, $contact, $id);

if ($stmt->execute()) {
    header("Location: ../MyFinal_Exam/index.php?status=updated&section=update");
} else {
    header("Location: ../MyFinal_Exam/index.php?status=error&section=update");
}
$stmt->close();
$conn->close();
?>
