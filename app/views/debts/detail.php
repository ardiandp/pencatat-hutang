<?php
$title = "Detail Hutang";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Debt Info -->
    <div class="md:col-span-1 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Informasi Hutang</h2>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Deskripsi:</p>
            <p class="font-semibold"><?php echo htmlspecialchars($debt['description']); ?></p>
        </div>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Jumlah:</p>
            <p class="font-semibold text-lg">Rp <?php echo number_format($debt['amount'], 2, ',', '.'); ?></p>
        </div>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Tipe:</p>
            <p class="font-semibold"><?php echo ucfirst($debt['type']); ?></p>
        </div>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Status:</p>
            <span class="px-2 py-1 rounded text-sm font-bold <?php echo $debt['status'] == 'paid' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800'; ?>">
                <?php echo ucfirst($debt['status']); ?>
            </span>
        </div>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400 text-sm">Jatuh Tempo:</p>
            <p class="font-semibold"><?php echo $debt['due_date'] ? date('d M Y', strtotime($debt['due_date'])) : '-'; ?></p>
        </div>
        <?php if (isAdmin()): ?>
            <div class="mb-4">
                <p class="text-gray-600 dark:text-gray-400 text-sm">Pemilik:</p>
                <p class="font-semibold"><?php echo htmlspecialchars($debt['username']); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Payment History & Form -->
    <div class="md:col-span-2 space-y-6">
        <!-- Add Payment Form -->
        <?php if ($debt['status'] != 'paid'): ?>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Tambah Pembayaran</h2>
                <form action="<?php echo BASE_URL; ?>/payments/add" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="debt_id" value="<?php echo $debt['id']; ?>">
                    <div class="mb-4">
                        <label for="payment_amount" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Jumlah Bayar:</label>
                        <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600" required>
                    </div>
                    <div class="mb-4">
                        <label for="proof_image" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Bukti Gambar (Base64):</label>
                        <input type="file" name="proof_image" id="proof_image" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Simpan Pembayaran
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Payment History -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Histori Pembayaran</h2>
            <?php if (empty($payments)): ?>
                <p class="text-gray-500 italic">Belum ada histori pembayaran.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($payments as $payment): ?>
                        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-green-600">Rp <?php echo number_format($payment['payment_amount'], 2, ',', '.'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></p>
                                </div>
                                <?php if ($payment['proof_image']): ?>
                                    <div class="w-24 h-24">
                                        <img src="<?php echo $payment['proof_image']; ?>" alt="Bukti Bayar" class="w-full h-full object-cover rounded cursor-pointer" onclick="window.open(this.src)">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . "/../layouts/app.php";
?>
