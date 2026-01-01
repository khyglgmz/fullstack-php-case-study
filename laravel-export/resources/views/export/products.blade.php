<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ürün Export</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-md">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 text-center">
            Ürün Export
        </h1>

        <form id="exportForm" class="space-y-4">
            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="is_active"
                    name="is_active"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600"
                >
                <label for="is_active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Sadece aktif ürünler
                </label>
            </div>

            <div>
                <label for="min_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Min Fiyat
                </label>
                <input
                    type="number"
                    id="min_price"
                    name="min_price"
                    min="0"
                    step="0.01"
                    placeholder="0.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <div>
                <label for="max_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Max Fiyat
                </label>
                <input
                    type="number"
                    id="max_price"
                    name="max_price"
                    min="0"
                    step="0.01"
                    placeholder="1000.00"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <button
                type="submit"
                id="exportBtn"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Export JSON
            </button>
        </form>

        <!-- Error Message -->
        <div id="error" class="hidden mt-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 rounded-md">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 dark:text-red-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200" id="errorTitle">Hata</h3>
                    <p class="text-sm text-red-700 dark:text-red-300 mt-1" id="errorMessage"></p>
                    <div id="errorDetails" class="hidden mt-2 text-xs text-red-600 dark:text-red-400">
                        <ul id="errorDetailsList" class="list-disc list-inside space-y-1"></ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Result -->
        <div id="result" class="hidden mt-6 p-4 bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md">
            <div class="flex items-center mb-3">
                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">Export Başarılı!</h2>
            </div>
            <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                <li>
                    <span class="font-medium">Dosya:</span>
                    <span id="filePath" class="text-gray-600 dark:text-gray-400"></span>
                </li>
                <li>
                    <span class="font-medium">Kayıt Sayısı:</span>
                    <span id="recordCount" class="text-gray-600 dark:text-gray-400"></span>
                </li>
                <li>
                    <span class="font-medium">Oluşturma Tarihi:</span>
                    <span id="createdAt" class="text-gray-600 dark:text-gray-400"></span>
                </li>
                <li>
                    <span class="font-medium">Dosya Boyutu:</span>
                    <span id="fileSize" class="text-gray-600 dark:text-gray-400"></span>
                </li>
            </ul>
            <a
                id="downloadLink"
                href="#"
                target="_blank"
                class="inline-flex items-center mt-4 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                JSON İndir
            </a>
        </div>

        <!-- Back Link -->
        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                ← Ana Sayfaya Dön
            </a>
        </div>
    </div>

    <script>
        document.getElementById('exportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('exportBtn');
            const errorDiv = document.getElementById('error');
            const resultDiv = document.getElementById('result');
            const errorDetails = document.getElementById('errorDetails');
            const errorDetailsList = document.getElementById('errorDetailsList');

            // Reset UI
            errorDiv.classList.add('hidden');
            resultDiv.classList.add('hidden');
            errorDetails.classList.add('hidden');
            errorDetailsList.innerHTML = '';
            btn.disabled = true;
            btn.textContent = 'Export ediliyor...';

            // Build query params
            const params = new URLSearchParams();

            const isActive = document.getElementById('is_active').checked;
            if (isActive) {
                params.append('is_active', 'true');
            }

            const minPrice = document.getElementById('min_price').value;
            if (minPrice) {
                params.append('min_price', minPrice);
            }

            const maxPrice = document.getElementById('max_price').value;
            if (maxPrice) {
                params.append('max_price', maxPrice);
            }

            try {
                const url = '/api/export/products' + (params.toString() ? '?' + params.toString() : '');
                const response = await fetch(url);
                const data = await response.json();

                if (!response.ok || !data.success) {
                    // Handle error with details
                    const error = data.error || {};
                    const errorObj = {
                        message: error.message || 'Export işlemi başarısız oldu',
                        code: error.code || response.status,
                        details: error.details || null
                    };
                    throw errorObj;
                }

                // Show result
                document.getElementById('filePath').textContent = data.data.file_path;
                document.getElementById('recordCount').textContent = data.data.record_count;
                document.getElementById('createdAt').textContent = data.data.created_at;
                document.getElementById('fileSize').textContent = data.data.file_size;

                // Extract filename from path
                const filename = data.data.file_path.split('/').pop();
                document.getElementById('downloadLink').href = '/export/download/' + filename;

                resultDiv.classList.remove('hidden');

            } catch (error) {
                // Display error
                if (error.message) {
                    document.getElementById('errorTitle').textContent = `Hata (${error.code || 'Bilinmiyor'})`;
                    document.getElementById('errorMessage').textContent = error.message;

                    // Show validation details if available
                    if (error.details && typeof error.details === 'object') {
                        errorDetails.classList.remove('hidden');
                        for (const [field, messages] of Object.entries(error.details)) {
                            const msgArray = Array.isArray(messages) ? messages : [messages];
                            msgArray.forEach(msg => {
                                const li = document.createElement('li');
                                li.textContent = `${field}: ${msg}`;
                                errorDetailsList.appendChild(li);
                            });
                        }
                    }
                } else {
                    document.getElementById('errorTitle').textContent = 'Hata';
                    document.getElementById('errorMessage').textContent = error.toString();
                }
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.textContent = 'Export JSON';
            }
        });
    </script>
</body>
</html>
