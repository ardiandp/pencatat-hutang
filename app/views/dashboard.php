<?php
$title = "Dashboard";
ob_start();
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Statistik Hutang</h2>
        <canvas id="debtChart" width="400" height="400"></canvas>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ringkasan</h2>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400">Total Belum Bayar:</p>
            <p class="text-2xl font-bold text-red-500">Rp <?php echo number_format($total_unpaid, 2, ',', '.'); ?></p>
        </div>
        <div class="mb-4">
            <p class="text-gray-600 dark:text-gray-400">Total Sudah Bayar:</p>
            <p class="text-2xl font-bold text-green-500">Rp <?php echo number_format($total_paid, 2, ',', '.'); ?></p>
        </div>
        <div class="mt-6">
            <a href="<?php echo BASE_URL; ?>/debts" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded block text-center">
                Kelola Hutang
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('debtChart').getContext('2d');
    const debtChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($chart_data['labels']); ?>,
            datasets: [{
                label: 'Hutang',
                data: <?php echo json_encode($chart_data['data']); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? 'white' : 'black'
                    }
                }
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . "/layouts/app.php";
?>
