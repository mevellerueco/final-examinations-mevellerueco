<?php
include '../includes/db.php';

// ── Update: fetch student by ID ──────────────────────────────────────────────
$updateStudent = null;
$updateSearched = false;
if (
    isset($_GET['section'], $_GET['student_id']) &&
    $_GET['section'] === 'update' && $_GET['student_id'] !== ''
) {
    $updateSearched = true;
    $uid = intval($_GET['student_id']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $updateStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── Delete: fetch student by ID ──────────────────────────────────────────────
$deleteStudent = null;
$deleteSearched = false;
if (
    isset($_GET['section'], $_GET['student_id']) &&
    $_GET['section'] === 'delete' && $_GET['student_id'] !== ''
) {
    $deleteSearched = true;
    $did = intval($_GET['student_id']);
    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $did);
    $stmt->execute();
    $deleteStudent = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ── Read: fetch students (with search and pagination) ────────────────────────
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1)
    $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM students";
$countSql = "SELECT COUNT(*) as total FROM students";
$params = [];
$types = '';
if ($searchQuery !== '') {
    $searchPattern = "%$searchQuery%";
    $sql .= " WHERE name LIKE ? OR surname LIKE ? OR id LIKE ?";
    $countSql .= " WHERE name LIKE ? OR surname LIKE ? OR id LIKE ?";
    $params = [$searchPattern, $searchPattern, $searchPattern];
    $types = 'sss';
}

$sql .= " ORDER BY id ASC LIMIT ? OFFSET ?";

// Get total count
$stmtCount = $conn->prepare($countSql);
if ($types !== '') {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$totalRecords = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);
if ($totalPages < 1)
    $totalPages = 1;
$stmtCount->close();

// Get students
$students = [];
$stmt = $conn->prepare($sql);
$types .= 'ii';
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
$stmt->close();

$activeSection = isset($_GET['section']) ? htmlspecialchars($_GET['section']) : 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Management System – CRUD operations for student records.">
    <title>Student Management System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav class="navbar">
        <a href="index.php" title="Go to Home" class="logo-link">
            <img src="images/mevelle_logo.png" id="logo" alt="Mevelle Logo">
        </a>
        <button class="navbarbuttons" id="btn-create" onclick="showSection('create')">Create</button>
        <button class="navbarbuttons" id="btn-read" onclick="showSection('read')">Read</button>
        <button class="navbarbuttons" id="btn-update" onclick="showSection('update')">Update</button>
        <button class="navbarbuttons" id="btn-delete" onclick="showSection('delete')">Delete</button>
    </nav>

    <!-- HOME ----------------------------------------------------------------->
    <section id="home" class="homecontent">
        <h1 class="splash">Welcome to Student Management System</h1>
        <h2 class="splash">A Project in Integrative Programming Technologies</h2>
    </section>

    <!-- CREATE --------------------------------------------------------------->
    <section id="create" class="content">
        <h1 class="contenttitle">Insert New Student</h1>
        <form action="../includes/insert.php" method="POST">
            <label for="surname" class="label">Surname</label>
            <input type="text" name="surname" id="surname" class="field" required><br />

            <label for="name" class="label">Name</label>
            <input type="text" name="name" id="name" class="field" required><br />

            <label for="middlename" class="label">Middle Name</label>
            <input type="text" name="middlename" id="middlename" class="field"><br />

            <label for="address" class="label">Address</label>
            <input type="text" name="address" id="address" class="field"><br />

            <label for="contact" class="label">Mobile Number</label>
            <input type="tel" name="contact" id="contact" class="field" pattern="[0-9]*" minlength="11" maxlength="11"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"><br />

            <div id="btncontainer">
                <button type="button" class="btns cancel-btn" onclick="goHome()">Cancel</button>
                <button type="button" id="clrbtn" class="btns" onclick="clearFields('create')">Clear Fields</button>
                <button type="submit" id="savebtn" class="btns save-btn">Save</button>
            </div>
        </form>
        <div id="success-toast" class="toast toast-hidden">Registration Successful!</div>
    </section>

    <!-- READ ----------------------------------------------------------------->
    <section id="read" class="content">
        <h1 class="contenttitle">View All Students</h1>

        <!-- Search Bar -->
        <div class="read-actions-bar">
            <form method="GET" action="index.php" class="inline-search">
                <input type="hidden" name="section" value="read">
                <input type="text" name="q" class="field search-input" placeholder="Search by name or ID..."
                    value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btns search-btn">Search</button>
                <?php if ($searchQuery !== ''): ?>
                    <a href="index.php?section=read" class="btns reset-btn">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($students)): ?>
            <p class="no-records">No student records found for your search.</p>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Surname</th>
                            <th>Name</th>
                            <th>Middle Name</th>
                            <th>Address</th>
                            <th>Contact Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($s['id']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($s['surname']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($s['name']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($s['middlename'] ?? '—') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($s['address'] ?? '—') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($s['contact_number'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="index.php?section=read&q=<?= urlencode($searchQuery) ?>&page=<?= $page - 1 ?>"
                            class="page-btn">Previous</a>
                    <?php endif; ?>
                    <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?section=read&q=<?= urlencode($searchQuery) ?>&page=<?= $page + 1 ?>"
                            class="page-btn">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </section>

    <!-- UPDATE --------------------------------------------------------------->
    <section id="update" class="content">
        <h1 class="contenttitle">Update Student Record</h1>

        <!-- Step 1: Search -->
        <form method="GET" action="index.php" class="search-form">
            <input type="hidden" name="section" value="update">
            <label for="update-search-id" class="label">Student ID</label>
            <input type="number" name="student_id" id="update-search-id" class="field" placeholder="Enter student ID"
                value="<?= isset($_GET['student_id']) && $activeSection === 'update' ? htmlspecialchars($_GET['student_id']) : '' ?>"
                min="1" required>
            <button type="submit" class="btns search-btn">Find Student</button>
        </form>

        <?php if ($updateSearched): ?>
            <?php if ($updateStudent): ?>
                <!-- Step 2: Edit form -->
                <div class="found-student">
                    <p class="found-label">Editing record for Student ID: <strong><?= $updateStudent['id'] ?></strong></p>
                    <form action="../includes/update.php" method="POST">
                        <input type="hidden" name="id" value="<?= $updateStudent['id'] ?>">

                        <label for="u-surname" class="label">Surname</label>
                        <input type="text" name="surname" id="u-surname" class="field"
                            value="<?= htmlspecialchars($updateStudent['surname']) ?>" required><br />

                        <label for="u-name" class="label">Name</label>
                        <input type="text" name="name" id="u-name" class="field"
                            value="<?= htmlspecialchars($updateStudent['name']) ?>" required><br />

                        <label for="u-middlename" class="label">Middle Name</label>
                        <input type="text" name="middlename" id="u-middlename" class="field"
                            value="<?= htmlspecialchars($updateStudent['middlename'] ?? '') ?>"><br />

                        <label for="u-address" class="label">Address</label>
                        <input type="text" name="address" id="u-address" class="field"
                            value="<?= htmlspecialchars($updateStudent['address'] ?? '') ?>"><br />

                        <label for="u-contact" class="label">Contact Number</label>
                        <input type="tel" name="contact" id="u-contact" class="field" pattern="[0-9]*" minlength="11"
                            maxlength="11" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            value="<?= htmlspecialchars($updateStudent['contact_number'] ?? '') ?>"><br />

                        <div class="btncontainer-inner">
                            <button type="button" class="btns cancel-btn" onclick="showSection('read')">Cancel</button>
                            <button type="button" class="btns" onclick="clearFields('update')">Clear Fields</button>
                            <button type="submit" class="btns save-btn">Update Record</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="not-found">
                    <p>No student found with ID: <strong><?= htmlspecialchars($_GET['student_id']) ?></strong></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="update-toast" class="toast toast-hidden">Record Updated Successfully!</div>
    </section>

    <!-- DELETE --------------------------------------------------------------->
    <section id="delete" class="content">
        <h1 class="contenttitle">Remove Student Record</h1>

        <!-- Search -->
        <form method="GET" action="index.php" class="search-form">
            <input type="hidden" name="section" value="delete">
            <label for="delete-search-id" class="label">Student ID</label>
            <input type="number" name="student_id" id="delete-search-id" class="field" placeholder="Enter student ID"
                value="<?= isset($_GET['student_id']) && $activeSection === 'delete' ? htmlspecialchars($_GET['student_id']) : '' ?>"
                min="1" required>
            <button type="submit" class="btns search-btn">Find Student</button>
        </form>

        <?php if ($deleteSearched): ?>
            <?php if ($deleteStudent): ?>
                <div class="found-student delete-preview">
                    <p class="found-label">Student Found — confirm deletion:</p>
                    <table class="preview-table">
                        <tr>
                            <th>ID</th>
                            <td><?= htmlspecialchars($deleteStudent['id']) ?></td>
                        </tr>
                        <tr>
                            <th>Surname</th>
                            <td><?= htmlspecialchars($deleteStudent['surname']) ?></td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><?= htmlspecialchars($deleteStudent['name']) ?></td>
                        </tr>
                        <tr>
                            <th>Middle Name</th>
                            <td><?= htmlspecialchars($deleteStudent['middlename'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?= htmlspecialchars($deleteStudent['address'] ?? '—') ?></td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td><?= htmlspecialchars($deleteStudent['contact_number'] ?? '—') ?></td>
                        </tr>
                    </table>
                    <form action="../includes/delete.php" method="POST" id="deleteForm">
                        <input type="hidden" name="id" value="<?= $deleteStudent['id'] ?>">
                        <button type="button" class="btns delete-btn" onclick="openDeleteModal(<?= $deleteStudent['id'] ?>)">
                            Delete Student
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="not-found">
                    <p>No student found with ID: <strong><?= htmlspecialchars($_GET['student_id']) ?></strong></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div id="delete-toast" class="toast toast-hidden">Student Record Deleted Successfully!</div>
    </section>

    <!-- Error Toast -->
    <div id="error-toast" class="toast toast-hidden" style="background-color: #d94a4a;">Error!</div>

    <!-- CUSTOM CONFIRM MODAL -->
    <div id="custom-modal" class="modal-overlay">
        <div class="modal-box">
            <h3 class="modal-title">Confirm Deletion</h3>
            <p class="modal-text">Are you sure you want to delete this student?</p>
            <form action="../includes/delete.php" method="POST" id="globalDeleteForm">
                <input type="hidden" name="id" id="modalDeleteId" value="">
                <div class="modal-buttons">
                    <button type="button" class="btns" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btns delete-btn">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>

</html>
