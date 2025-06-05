<?php
// Konfigurasi koneksi database
$host = 'localhost'; // Umumnya 'localhost' untuk server lokal
$username = 'root';  // Umumnya 'root' jika tidak diubah di XAMPP
$password = '';      // Umumnya kosong jika tidak disetel password di XAMPP
$database = 'db_users'; // Nama database yang telah Anda buat di phpMyAdmin

// Membuat koneksi ke database
$conn = new mysqli($host, $username, $password, $database);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Variabel untuk pesan notifikasi (misal: "Pengguna berhasil ditambahkan!")
$message = '';

// --- Logika CRUD (Create, Update, Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Memeriksa aksi yang diminta (add atau update)
    if (isset($_POST['action'])) {
        // Logika CREATE (Tambah Pengguna)
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']); // Mencegah SQL Injection
            $email = $conn->real_escape_string($_POST['email']); // Mencegah SQL Injection

            $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
            if ($conn->query($sql) === TRUE) {
                $message = "Pengguna berhasil ditambahkan!";
            } else {
                // Menampilkan error jika ada, berguna untuk debugging
                $message = "Error menambah pengguna: " . $conn->error;
            }
        }
        // Logika UPDATE (Edit Pengguna)
        elseif ($_POST['action'] == 'update') {
            $id = $conn->real_escape_string($_POST['id']);
            $name = $conn->real_escape_string($_POST['name']);
            $email = $conn->real_escape_string($_POST['email']);

            $sql = "UPDATE users SET name='$name', email='$email' WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                $message = "Pengguna berhasil diperbarui!";
            } else {
                $message = "Error memperbarui pengguna: " . $conn->error;
            }
        }
    }
}

// Logika DELETE (Hapus Pengguna)
// Aksi delete dihandle melalui GET request (link)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);
    $sql = "DELETE FROM users WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $message = "Pengguna berhasil dihapus!";
    } else {
        $message = "Error menghapus pengguna: " . $conn->error;
    }
    // Redirect untuk menghilangkan parameter GET dari URL setelah delete
    header("Location: index.php");
    exit();
}

// Logika untuk menampilkan data yang akan di-edit di formulir
// Ini terjadi ketika pengguna mengklik tombol "Edit"
$edit_id = null;
$edit_name = '';
$edit_email = '';
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = $conn->real_escape_string($_GET['id']);
    $sql = "SELECT * FROM users WHERE id=$edit_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $edit_name = $row['name'];
        $edit_email = $row['email'];
    } else {
        $message = "Pengguna tidak ditemukan untuk diedit.";
        $edit_id = null; // Reset edit mode if user not found
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Data Pengguna Sederhana</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Manajemen Data Pengguna</h1>

        <?php if ($message): // Tampilkan pesan notifikasi jika ada ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2><?php echo ($edit_id) ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h2>
        <form action="index.php" method="POST">
            <?php if ($edit_id): // Jika dalam mode edit, sertakan ID tersembunyi dan aksi update ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_id); ?>">
                <input type="hidden" name="action" value="update">
            <?php else: // Jika dalam mode tambah, sertakan aksi add ?>
                <input type="hidden" name="action" value="add">
            <?php endif; ?>

            <label for="name">Nama:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_name); ?>" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_email); ?>" required><br><br>

            <input type="submit" value="<?php echo ($edit_id) ? 'Update Pengguna' : 'Tambah Pengguna'; ?>">
            <?php if ($edit_id): // Tombol batal jika dalam mode edit ?>
                <a href="index.php" class="button-cancel">Batal Edit</a>
            <?php endif; ?>
        </form>

        <hr> <h2>Daftar Pengguna</h2>
        <?php
        // Logika READ (Menampilkan Daftar Pengguna)
        $sql = "SELECT id, name, email FROM users ORDER BY id DESC"; // Ambil semua data pengguna
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Jika ada data, tampilkan dalam tabel
            echo "<table>";
            echo "<thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Aksi</th></tr></thead>";
            echo "<tbody>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                echo "<td>";
                // Link Edit
                echo "<a href='index.php?action=edit&id=" . htmlspecialchars($row["id"]) . "' class='button-edit'>Edit</a> ";
                // Link Delete dengan konfirmasi JavaScript
                echo "<a href='index.php?action=delete&id=" . htmlspecialchars($row["id"]) . "' class='button-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus pengguna ini?\")'>Hapus</a>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            // Jika tidak ada data
            echo "<p>Tidak ada pengguna dalam daftar.</p>";
        }

        // Menutup koneksi database
        $conn->close();
        ?>
    </div>
    <script src="js/script.js"></script>
</body>
</html>