<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Debt App'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }

        function toggleTheme() {
            if (localStorage.theme === 'dark') {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white dark:bg-gray-800 shadow-md p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="text-xl font-bold text-gray-800 dark:text-white">Debt App</a>
            <div class="flex items-center space-x-4">
                <?php if (isAuthenticated()): ?>
                    <a href="<?php echo BASE_URL; ?>/dashboard" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/debts" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">My Debts</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo BASE_URL; ?>/admin" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">Admin</a>
                    <?php endif; ?>
                    <button onclick="toggleTheme()" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:outline-none">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    </button>
                    <a href="<?php echo BASE_URL; ?>/logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">Login</a>
                    <a href="<?php echo BASE_URL; ?>/register" class="text-gray-700 dark:text-gray-300 hover:text-blue-500 dark:hover:text-blue-400">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container mx-auto mt-8 p-4 flex-grow">
        <?php echo $content ?? ''; // This is where the main content will be injected ?>
    </main>

    <footer class="bg-white dark:bg-gray-800 shadow-md p-4 mt-8">
        <div class="container mx-auto text-center text-gray-600 dark:text-gray-400">
            &copy; <?php echo date("Y"); ?> Debt App. All rights reserved.
        </div>
    </footer>
</body>
</html>
