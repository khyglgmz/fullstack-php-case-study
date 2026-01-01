<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adres Haritası - PHP Geocode</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="toast-container" class="toast-container"></div>

    <div class="container">
        <aside class="sidebar">
            <h1>Adres Haritası</h1>

            <div class="controls">
                <button id="geocodeBtn" class="btn btn-primary">
                    Geocode et
                </button>
            </div>

            <div class="summary-cards">
                <div class="summary-card total">
                    <div class="summary-card-icon">📍</div>
                    <div class="summary-card-content">
                        <span class="summary-card-value" id="totalCount">-</span>
                        <span class="summary-card-label">Toplam</span>
                    </div>
                </div>
                <div class="summary-card success">
                    <div class="summary-card-icon">✓</div>
                    <div class="summary-card-content">
                        <span class="summary-card-value" id="successCount">-</span>
                        <span class="summary-card-label">Başarılı</span>
                    </div>
                </div>
                <div class="summary-card failed">
                    <div class="summary-card-icon">✕</div>
                    <div class="summary-card-content">
                        <span class="summary-card-value" id="failedCount">-</span>
                        <span class="summary-card-label">Başarısız</span>
                    </div>
                </div>
            </div>

            <div class="tabs">
                <button class="tab-btn active" data-tab="all">Tümü</button>
                <button class="tab-btn" data-tab="failed">Başarısızlar</button>
            </div>

            <div class="tab-content" id="allList">
                <ul id="allAddresses"></ul>
            </div>

            <div class="tab-content hidden" id="failedList">
                <ul id="failedAddresses"></ul>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span>Yükleniyor...</span>
            </div>
        </aside>

        <main class="map-container">
            <div id="map"></div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="assets/js/map.js?v=<?= time() ?>"></script>
</body>
</html>
