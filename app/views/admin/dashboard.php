<?php
$title = "Admin Dashboard";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-bold text-blue-800 dark:text-blue-200">Total User</h3>
        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400"><?php echo $total_users; ?></p>
    </div>
    <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-bold text-green-800 dark:text-green-200">Total Catatan</h3>
        <p class="text-3xl font-bold text-green-600 dark:text-green-400"><?php echo $total_debts; ?></p>
    </div>
    <div class="bg-red-100 dark:bg-red-900 p-6 rounded-lg shadow-md">
        <h3 class="text-lg font-bold text-red-800 dark:text-red-200">Total Belum Bayar</h3>
        <p class="text-3xl font-bold text-red-600 dark:text-red-400">Rp <?php echo number_format($total_unpaid, 2, ',', '.'); ?></p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Daftar User</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Username
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                        Tanggal Daftar
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <p class="text-gray-900 dark:text-white whitespace-no-wrap"><?php echo htmlspecialchars($user['username']); ?></p>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <span class="px-2 py-1 rounded text-xs font-bold <?php echo $user['role'] == 'admin' ? 'bg-purple-200 text-purple-800' : 'bg-gray-200 text-gray-800'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                            <p class="text-gray-900 dark:text-white whitespace-no-wrap"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
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
