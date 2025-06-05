// Anda bisa menambahkan validasi form atau interaksi JS lainnya di sini
// Untuk aplikasi CRUD sederhana ini, JavaScript tidak memiliki fungsi inti yang kompleks.
// Tetapi file ini tetap disertakan untuk struktur proyek yang lengkap.

document.addEventListener('DOMContentLoaded', function() {
    console.log('Script.js loaded successfully for simplified CRUD application!');

    // Contoh sederhana: Menampilkan alert saat form disubmit (opsional, bisa dihapus)
    const userForm = document.querySelector('form');
    if (userForm) {
        userForm.addEventListener('submit', function() {
            // alert('Form submitted!'); // Hapus atau jadikan komentar setelah pengujian
        });
    }
});