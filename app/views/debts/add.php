<?php
$title = "Tambah Hutang";
ob_start();
?>

<div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md max-w-lg mx-auto">
    <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-6">Tambah Hutang</h2>
    <form action="<?php echo BASE_URL; ?>/debts/add" method="POST">
        <div class="mb-4">
            <label for="description" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Deskripsi:</label>
            <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600" required></textarea>
        </div>
        <div class="mb-4">
            <label for="amount" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Jumlah:</label>
            <input type="number" step="0.01" name="amount" id="amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600" required>
        </div>
        <div class="mb-4">
            <label for="type" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Tipe:</label>
            <select name="type" id="type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600">
                <option value="hutang">Hutang (Saya berhutang)</option>
                <option value="pinjam">Pinjam (Orang lain meminjam)</option>
            </select>
        </div>
        <div class="mb-6">
            <label for="due_date" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Jatuh Tempo:</label>
            <input type="date" name="due_date" id="due_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600">
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Simpan
            </button>
            <a href="<?php echo BASE_URL; ?>/debts" class="inline-block align-baseline font-bold text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                Batal
            </a>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . "/../layouts/app.php";
?>
