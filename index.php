<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta Bebras Challenge 2025</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Daftar Peserta Bebras Challenge 2025</h1>

<div class="school-list">
    <?php
    // Baca file JSON
    $json_file = 'data_sekolah.json';
    $json_data = file_get_contents($json_file);
    if ($json_data === false) {
        die("Error: Tidak dapat membaca file $json_file.");
    }
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error: Format JSON tidak valid di $json_file. " . json_last_error_msg());
    }

    // Kelompokkan data berdasarkan sekolah (untuk menampilkan daftar pendamping)
    $grouped_data = [];
    foreach ($data as $item) {
        $school_name = $item['sekolah'];
        if (!isset($grouped_data[$school_name])) {
            $grouped_data[$school_name] = [
                'pdf_file' => $item['pdf_file'], // Ambil PDF file dari entri pertama
                'pendamping_list' => []
            ];
        }
        // Gabungkan semua pendamping dan kode verifikasi untuk sekolah ini
        foreach ($item['pendamping'] as $idx => $pendamping_nama) {
             $grouped_data[$school_name]['pendamping_list'][] = [
                 'name' => $pendamping_nama,
                 'code' => $item['verification_codes'][$idx] // Ambil kode verifikasi yang sesuai
             ];
        }
    }

    // Urutkan daftar sekolah
    ksort($grouped_data);

    foreach ($grouped_data as $school_name => $school_info) {
        $sekolah_nama = htmlspecialchars($school_name);
        $pdf_file = $school_info['pdf_file'];
        $pendamping_list = $school_info['pendamping_list'];

        echo "<div class='school-item'>";
        echo "<h3>$sekolah_nama</h3>";
        echo "<ul class='pendamping-list'>";

        foreach ($pendamping_list as $pendamping) {
            $pendamping_nama = htmlspecialchars($pendamping['name']);
            $verification_code = $pendamping['code']; // Kode untuk verifikasi

            // Gunakan kode verifikasi sebagai identifier untuk tombol
            echo "<li>";
            echo "<strong>Pendamping:</strong> $pendamping_nama ";
            echo "<button class='download-btn' onclick='openModal(\"$pdf_file\", \"$verification_code\", \"$pendamping_nama\")'>Download PDF</button>";
            echo "</li>";
        }

        echo "</ul>";
        echo "</div>";
    }
    ?>
</div>

<!-- The Modal -->
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Verifikasi Unduhan</h3>
        <p id="modal-pendamping-name"></p>
        <p>Masukkan 4 digit terakhir nomor telepon pendamping untuk mengunduh PDF.</p>
        <form id="verificationForm">
            <input type="hidden" id="pdfFileInput" name="pdf_file">
            <input type="hidden" id="expectedCodeInput" name="expected_code"> <!-- Simpan kode yang benar di sini -->
            <label for="verificationCode">Kode Verifikasi (4 Digit):</label>
            <input type="text" id="verificationCode" name="code_input" placeholder="XXXX" maxlength="4" required>
            <div id="errorMessage" class="error-message"></div>
            <button type="submit">Verifikasi dan Unduh</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById("myModal");

    function openModal(pdfFile, expectedCode, pendampingName) {
        document.getElementById("pdfFileInput").value = pdfFile;
        document.getElementById("expectedCodeInput").value = expectedCode;
        document.getElementById("modal-pendamping-name").textContent = "Pendamping: " + pendampingName;
        modal.style.display = "block";
    }

    function closeModal() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }

    // Kirim ke verify.php (server-side)
    document.getElementById("verificationForm").addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append("pdf_file", document.getElementById("pdfFileInput").value);
        formData.append("input_code", document.getElementById("verificationCode").value.trim());
        formData.append("expected_code", document.getElementById("expectedCodeInput").value);

        fetch("verify.php", {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === "error") {
                document.getElementById("errorMessage").textContent = data.message;
            } else {
                window.location.href = data.download_url;
            }
        })
        .catch(err => {
            document.getElementById("errorMessage").textContent = "Terjadi kesalahan server.";
        });
    });
</script>


</body>
</html>
