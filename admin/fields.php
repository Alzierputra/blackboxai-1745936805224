<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Handle field creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $nama = $_POST['nama'];
        $deskripsi = $_POST['deskripsi'];
        $harga_per_jam = $_POST['harga_per_jam'];
        $status = $_POST['status'];
        
        if ($_POST['action'] === 'create') {
            try {
                $query = "INSERT INTO lapangan (nama, deskripsi, harga_per_jam, status) 
                         VALUES (:nama, :deskripsi, :harga_per_jam, :status)";
                $stmt = $functions->db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':harga_per_jam', $harga_per_jam);
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
                $query = "UPDATE lapangan 
                         SET nama = :nama, 
                             deskripsi = :deskripsi, 
                             harga_per_jam = :harga_per_jam, 
                             status = :status 
                         WHERE id = :id";
                $stmt = $functions->db->prepare($query);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':harga_per_jam', $harga_per_jam);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
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
<div id="addModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="fields.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Tambah Lapangan Baru
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lapangan</label>
                            <input type="text" name="nama" id="nama" required
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3" required
                                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        <div>
                            <label for="harga_per_jam" class="block text-sm font-medium text-gray-700">Harga per Jam</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="harga_per_jam" id="harga_per_jam" required
                                       class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button type="button" onclick="hideAddModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Field Modal -->
<div id="editModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="fields.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Edit Lapangan
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label for="edit_nama" class="block text-sm font-medium text-gray-700">Nama Lapangan</label>
                            <input type="text" name="nama" id="edit_nama" required
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="edit_deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" rows="3" required
                                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        <div>
                            <label for="edit_harga_per_jam" class="block text-sm font-medium text-gray-700">Harga per Jam</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="harga_per_jam" id="edit_harga_per_jam" required
                                       class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="edit_status" required
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="tersedia">Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" onclick="hideEditModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
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
    document.getElementById('editModal').classList.remove('hidden');
}

function hideEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.querySelector('#editModal form').reset();
}
</script>

<?php require_once '../includes/footer.php'; ?>
