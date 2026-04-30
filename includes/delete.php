<?php
include 'db.php';

$id = intval($_POST['id']);

$stmt = $conn->prepare("DELETE FROM students WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../MyFinal_Exam/index.php?status=deleted&section=delete");
} else {
    header("Location: ../MyFinal_Exam/index.php?status=error&section=delete");
}
$stmt->close();
$conn->close();
?>
