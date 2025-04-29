<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/lapangan';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle field creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga_per_jam = $_POST['harga_per_jam'];
        $status = $_POST['status'];
        
        // Handle file upload
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto']['tmp_name'];
            $file_name = $_FILES['foto']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Check file extension
            $allowed = array('jpg', 'jpeg', 'png');
            if (in_array($file_ext, $allowed)) {
                $new_name = uniqid('lapangan_') . '.' . $file_ext;
                $upload_path = $upload_dir . '/' . $new_name;
                
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $foto = 'uploads/lapangan/' . $new_name;
                }
            }
        }
        
        if ($_POST['action'] === 'create') {
            try {
                $query = "INSERT INTO lapangan (nama, deskripsi, harga_per_jam, foto, status) 
                         VALUES (:nama, :deskripsi, :harga_per_jam, :foto, :status)";
                $stmt = $functions->db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':harga_per_jam', $harga_per_jam);
                $stmt->bindParam(':foto', $foto);
                $stmt->bindParam(':status', $status);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Lapangan berhasil ditambahkan.";
                } else {
                    $_SESSION['error'] = "Gagal menambahkan lapangan.";
                }
            } catch(PDOException $e) {
                $_SESSION['error'] = "Terjadi kesalahan sistem.";
            }
        } 
        elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            try {
                // Get existing photo
                $query = "SELECT foto FROM lapangan WHERE id = :id";
                $stmt = $functions->db->prepare($query);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // If no new photo uploaded, keep existing
                if (!$foto && $existing) {
                    $foto = $existing['foto'];
                }
                
                $query = "UPDATE lapangan 
                         SET nama = :nama, 
                             deskripsi = :deskripsi, 
                             harga_per_jam = :harga_per_jam,
                             foto = :foto,
                             status = :status 
                         WHERE id = :id";
                $stmt = $functions->db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':harga_per_jam', $harga_per_jam);
                $stmt->bindParam(':foto', $foto);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    // Delete old photo if new one uploaded
                    if ($foto !== $existing['foto'] && $existing['foto']) {
                        unlink('../' . $existing['foto']);
                    }
                    $_SESSION['success'] = "Lapangan berhasil diperbarui.";
                } else {
                    $_SESSION['error'] = "Gagal memperbarui lapangan.";
                }
            } catch(PDOException $e) {
                $_SESSION['error'] = "Terjadi kesalahan sistem.";
            }
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get all fields
$query = "SELECT * FROM lapangan ORDER BY id ASC";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Add New Field Button -->
    <div class="mb-6">
        <button onclick="showAddModal()"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i> Tambah Lapangan
        </button>
    </div>

    <!-- Fields Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($fields as $field): ?>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <?php if ($field['foto']): ?>
                <img src="/<?php echo $field['foto']; ?>" alt="<?php echo $field['nama']; ?>" 
                     class="w-full h-48 object-cover">
            <?php else: ?>
                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-gray-400"></i>
                </div>
            <?php endif; ?>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        <?php echo $field['nama']; ?>
                    </h3>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        <?php echo $field['status'] === 'tersedia' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($field['status']); ?>
                    </span>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    <?php echo $field['deskripsi']; ?>
                </p>
                <div class="mt-4">
                    <p class="text-lg font-semibold text-blue-600">
                        Rp <?php echo number_format($field['harga_per_jam'], 0, ',', '.'); ?> / jam
                    </p>
                </div>
                <div class="mt-6 flex space-x-3">
                    <button onclick="showEditModal(<?php echo htmlspecialchars(json_encode($field)); ?>)"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Field Modal -->
<div id="addModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <form action="fields.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                
                <div class="px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Tambah Lapangan Baru</h3>
                    
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lapangan</label>
                            <input type="text" name="nama" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" rows="3" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto Lapangan</label>
                            <input type="file" name="foto" accept="image/*" required
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga per Jam</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="harga_per_jam" required
                                       class="block w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="hideAddModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div id="editModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
            <form action="fields.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Edit Lapangan</h3>
                    
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Lapangan</label>
                            <input type="text" name="nama" id="edit_nama" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" rows="3" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Foto Lapangan</label>
                            <div id="current_foto" class="mt-2"></div>
                            <input type="file" name="foto" accept="image/*"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Biarkan kosong jika tidak ingin mengubah foto</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga per Jam</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="harga_per_jam" id="edit_harga_per_jam" required
                                       class="block w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="edit_status" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                    <button type="button" onclick="hideEditModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function hideAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.querySelector('#addModal form').reset();
}

function showEditModal(field) {
    document.getElementById('edit_id').value = field.id;
    document.getElementById('edit_nama').value = field.nama;
    document.getElementById('edit_deskripsi').value = field.deskripsi;
    document.getElementById('edit_harga_per_jam').value = field.harga_per_jam;
    document.getElementById('edit_status').value = field.status;
    
    // Show current photo if exists
    const currentFotoDiv = document.getElementById('current_foto');
    if (field.foto) {
        currentFotoDiv.innerHTML = `
            <img src="/${field.foto}" alt="${field.nama}" class="h-32 w-auto object-cover rounded-md">
            <p class="mt-1 text-xs text-gray-500">Foto saat ini</p>
        `;
    } else {
        currentFotoDiv.innerHTML = `
            <p class="text-xs text-gray-500">Tidak ada foto</p>
        `;
    }
    
    document.getElementById('editModal').classList.remove('hidden');
}

function hideEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.querySelector('#editModal form').reset();
    document.getElementById('current_foto').innerHTML = '';
}

// Validate file upload
function validateFile(input) {
    const file = input.files[0];
    if (file) {
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 2MB');
            input.value = '';
            return false;
        }
        
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG atau PNG');
            input.value = '';
            return false;
        }
    }
    return true;
}

// Add file validation to upload inputs
document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        validateFile(this);
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
