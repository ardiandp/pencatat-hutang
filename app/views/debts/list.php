<?php
$title = "Daftar Hutang";
ob_start();
?>

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Daftar Hutang</h2>
        <a href="<?php echo BASE_URL; ?>/debts/add" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Tambah Hutang
        </a>
    </div>

    <?php if (getSession("success")): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo getSession("success"); unset($_SESSION["success"]); ?></span>
        </div>
    <?php endif; ?>
    <?php if (getSession("error")): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo getSession("error"); unset($_SESSION["error"]); ?></span>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Deskripsi
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Jumlah
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Tipe
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($debts as $debt): ?>
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <p class="text-gray-900 dark:text-white whitespace-no-wrap"><?php echo htmlspecialchars($debt['description']); ?></p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <p class="text-gray-900 dark:text-white whitespace-no-wrap">Rp <?php echo number_format($debt['amount'], 2, ',', '.'); ?></p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-<?php echo $debt['type'] == 'hutang' ? 'red' : 'blue'; ?>-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-<?php echo $debt['type'] == 'hutang' ? 'red' : 'blue'; ?>-200 opacity-50 rounded-full"></span>
                                <span class="relative"><?php echo ucfirst($debt['type']); ?></span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-<?php echo $debt['status'] == 'paid' ? 'green' : 'yellow'; ?>-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-<?php echo $debt['status'] == 'paid' ? 'green' : 'yellow'; ?>-200 opacity-50 rounded-full"></span>
                                <span class="relative"><?php echo ucfirst($debt['status']); ?></span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <a href="<?php echo BASE_URL; ?>/debts/detail?id=<?php echo $debt['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Detail</a>
                            <a href="<?php echo BASE_URL; ?>/debts/edit?id=<?php echo $debt['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <a href="<?php echo BASE_URL; ?>/debts/delete?id=<?php echo $debt['id']; ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . "/../layouts/app.php";
?>
