<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Product Export API</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">
                Laravel Product Export API
            </h1>
            <p class="text-gray-600 dark:text-gray-400 text-lg">
                Ürün verilerini JSON formatında export edin
            </p>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto">
            <!-- Feature Cards -->
            <div class="grid md:grid-cols-2 gap-6 mb-12">
                <!-- Web UI Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white ml-4">Web Arayüzü</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Kullanıcı dostu form ile ürünleri filtreleyin ve JSON olarak export edin.
                    </p>
                    <a href="/export/products" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        Export Sayfasına Git
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <!-- API Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white ml-4">REST API</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        Programatik erişim için RESTful API endpointleri kullanın.
                    </p>
                    <a href="#api-docs" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        API Dokümantasyonu
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- API Documentation -->
            <div id="api-docs" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">API Endpointleri</h2>

                <!-- Sync Export -->
                <div class="mb-8">
                    <div class="flex items-center mb-3">
                        <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded mr-3">GET</span>
                        <code class="text-gray-800 dark:text-gray-200 font-mono">/api/export/products</code>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-3">Senkron export - Hemen JSON dosyası oluşturur</p>
                    <div class="bg-gray-100 dark:bg-gray-900 rounded-md p-4 overflow-x-auto">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Örnek İstek:</p>
                        <code class="text-sm text-gray-800 dark:text-gray-200 font-mono">
                            curl "http://localhost:8000/api/export/products?is_active=true&min_price=50&max_price=500"
                        </code>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-900 rounded-md p-4 mt-3 overflow-x-auto">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Örnek Cevap:</p>
                        <pre class="text-sm text-gray-800 dark:text-gray-200 font-mono">{
    "success": true,
    "data": {
        "file_path": "exports/products_20260101_1830.json",
        "record_count": 50,
        "created_at": "2026-01-01T18:30:00+00:00",
        "file_size": "12.5 KB"
    }
}</pre>
                    </div>
                </div>

                <!-- Async Export -->
                <div class="mb-8">
                    <div class="flex items-center mb-3">
                        <span class="bg-blue-500 text-white text-xs font-bold px-2 py-1 rounded mr-3">POST</span>
                        <code class="text-gray-800 dark:text-gray-200 font-mono">/api/export/products/async</code>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mb-3">Asenkron export - Kuyruğa ekler (Queue Job)</p>
                    <div class="bg-gray-100 dark:bg-gray-900 rounded-md p-4 overflow-x-auto">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Örnek İstek:</p>
                        <code class="text-sm text-gray-800 dark:text-gray-200 font-mono">
                            curl -X POST http://localhost:8000/api/export/products/async -H "Content-Type: application/json" -d '{"is_active": true}'
                        </code>
                    </div>
                </div>

                <!-- Query Parameters -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Filtre Parametreleri</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="text-left py-2 text-gray-800 dark:text-white">Parametre</th>
                                    <th class="text-left py-2 text-gray-800 dark:text-white">Tip</th>
                                    <th class="text-left py-2 text-gray-800 dark:text-white">Açıklama</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 dark:text-gray-400">
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2"><code>is_active</code></td>
                                    <td class="py-2">boolean</td>
                                    <td class="py-2">Sadece aktif ürünleri filtrele</td>
                                </tr>
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2"><code>min_price</code></td>
                                    <td class="py-2">number</td>
                                    <td class="py-2">Minimum fiyat filtresi</td>
                                </tr>
                                <tr>
                                    <td class="py-2"><code>max_price</code></td>
                                    <td class="py-2">number</td>
                                    <td class="py-2">Maximum fiyat filtresi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-12 text-gray-500 dark:text-gray-400 text-sm">
                <p>Laravel Product Export API - Case Study</p>
            </div>
        </div>
    </div>
</body>
</html>
