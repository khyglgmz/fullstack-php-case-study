class GeocodeMap {
    constructor() {
        this.map = null;
        this.markers = [];
        this.markerGroup = null;
        this.locations = [];
        this.customIcon = null;

        this.init();
    }

    init() {
        this.initMap();
        this.createCustomIcon();
        this.bindEvents();
        this.loadLocations();
    }

    createCustomIcon() {
        this.customIcon = L.divIcon({
            className: 'custom-marker',
            html: `<div class="marker-pin"></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42],
            popupAnchor: [0, -36]
        });
    }

    initMap() {
        this.map = L.map('map').setView([39.0, 35.0], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        this.markerGroup = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            disableClusteringAtZoom: 15,
            iconCreateFunction: (cluster) => {
                const count = cluster.getChildCount();
                let size = 'small';
                if (count > 10) size = 'medium';
                if (count > 20) size = 'large';

                return L.divIcon({
                    html: `<div class="cluster-marker ${size}"><span>${count}</span></div>`,
                    className: 'custom-cluster',
                    iconSize: [40, 40]
                });
            }
        }).addTo(this.map);
    }

    bindEvents() {
        document.getElementById('geocodeBtn').addEventListener('click', () => this.geocodeAll());

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.switchTab(e.target.dataset.tab));
        });
    }

    switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });

        document.getElementById('allList').classList.toggle('hidden', tab !== 'all');
        document.getElementById('failedList').classList.toggle('hidden', tab !== 'failed');
    }

    showLoading(show) {
        const loading = document.getElementById('loading');
        const geocodeBtn = document.getElementById('geocodeBtn');

        if (show) {
            loading.classList.add('active');
            geocodeBtn.disabled = true;
        } else {
            loading.classList.remove('active');
            geocodeBtn.disabled = false;
        }
    }

    async loadLocations() {
        this.showLoading(true);

        try {
            const response = await fetch('/api/locations');
            const data = await response.json();

            if (data.success) {
                this.locations = data.data.locations;
                this.updateSummary(data.data.summary);
                this.updateMarkers();
                this.updateAllList();
                this.updateFailedList();
            } else {
                this.showError(data.error.message);
            }
        } catch (error) {
            this.showError('Lokasyonlar yüklenirken hata oluştu: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async geocodeAll() {
        this.showLoading(true);

        try {
            const response = await fetch('/api/geocode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ forceRetry: false })
            });

            const data = await response.json();

            if (data.success) {
                this.locations = data.data.locations;
                this.updateSummary(data.data.summary);
                this.updateMarkers();
                this.updateAllList();
                this.updateFailedList();

                const summary = data.data.summary;
                if (summary.success > 0) {
                    this.showSuccess(`${summary.success} adres başarıyla geocode edildi`);
                }
                if (summary.failed > 0) {
                    this.showToast(`${summary.failed} adres geocode edilemedi`, 'warning');
                }
            } else {
                this.showError(data.error.message);
            }
        } catch (error) {
            this.showError('Geocode işlemi sırasında hata oluştu: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async retryAddress(addressId) {
        this.showLoading(true);

        try {
            const response = await fetch(`/api/geocode/${addressId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const index = this.locations.findIndex(loc => loc.addressId === addressId);
                if (index !== -1) {
                    this.locations[index] = data.data.location;
                }
                this.updateSummary(data.data.summary);
                this.updateMarkers();
                this.updateAllList();
                this.updateFailedList();

                if (data.data.location.status === 'success') {
                    this.showSuccess('Adres başarıyla geocode edildi');
                } else {
                    this.showToast('Adres yine geocode edilemedi', 'warning');
                }
            } else {
                this.showError(data.error.message);
            }
        } catch (error) {
            this.showError('Yeniden deneme sırasında hata oluştu: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    updateSummary(summary) {
        document.getElementById('totalCount').textContent = summary.total;
        document.getElementById('successCount').textContent = summary.success;
        document.getElementById('failedCount').textContent = summary.failed;
    }

    updateMarkers() {
        this.markerGroup.clearLayers();
        this.markers = [];

        const successLocations = this.locations.filter(loc => loc.status === 'success');

        // Hiç lokasyon yoksa veya başarılı lokasyon yoksa empty state göster
        if (this.locations.length === 0) {
            this.showEmptyMapState('Henüz lokasyon yok', 'Adresleri geocode etmek için "Koordinat Bul" butonuna tıklayın');
            return;
        }

        if (successLocations.length === 0) {
            this.showEmptyMapState('Başarılı lokasyon yok', 'Tüm adresler geocode edilemedi. Sidebar\'dan tekrar deneyin.');
            return;
        }

        this.hideEmptyMapState();

        successLocations.forEach(location => {
            if (location.latitude && location.longitude) {
                const marker = L.marker([location.latitude, location.longitude], {
                    icon: this.customIcon
                }).bindPopup(this.createPopupContent(location));

                this.markerGroup.addLayer(marker);
                this.markers.push(marker);
            }
        });

        if (this.markers.length > 0) {
            this.map.fitBounds(this.markerGroup.getBounds(), { padding: [50, 50] });
        }
    }

    showEmptyMapState(title = 'Henüz lokasyon yok', text = 'Adresleri geocode etmek için "Koordinat Bul" butonuna tıklayın') {
        let emptyState = document.getElementById('empty-map-state');
        if (!emptyState) {
            emptyState = document.createElement('div');
            emptyState.id = 'empty-map-state';
            emptyState.className = 'empty-state';
            emptyState.style.cssText = 'position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1000;background:rgba(255,255,255,0.95);border-radius:12px;padding:30px;';
            document.querySelector('.map-container').appendChild(emptyState);
        }
        emptyState.innerHTML = `
            <div class="empty-state-icon">📍</div>
            <div class="empty-state-title">${this.escapeHtml(title)}</div>
            <div class="empty-state-text">${this.escapeHtml(text)}</div>
        `;
        emptyState.style.display = 'block';
    }

    hideEmptyMapState() {
        const emptyState = document.getElementById('empty-map-state');
        if (emptyState) {
            emptyState.style.display = 'none';
        }
    }

    createPopupContent(location) {
        return `
            <div class="popup-content">
                <div class="popup-title">${this.escapeHtml(location.title)}</div>
                <div class="popup-address">${this.escapeHtml(location.address)}</div>
                <div class="popup-coords">
                    Koordinatlar: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}
                </div>
            </div>
        `;
    }

    updateAllList() {
        const container = document.getElementById('allAddresses');

        if (this.locations.length === 0) {
            container.innerHTML = '<li class="no-items">Henüz adres yok</li>';
            return;
        }

        container.innerHTML = this.locations.map(location => {
            const statusLabel = {
                success: 'Başarılı',
                failed: 'Başarısız'
            };

            let retryBtn = '';
            if (location.status === 'failed') {
                retryBtn = `<button class="btn btn-retry" onclick="geocodeMap.retryAddress(${location.addressId})">Tekrar Dene</button>`;
            }

            return `
                <li class="${location.status}">
                    <div class="title">${this.escapeHtml(location.title)}</div>
                    <div class="address">${this.escapeHtml(location.address)}</div>
                    ${location.status === 'failed' ? `<div class="error">${this.escapeHtml(location.errorMessage || 'Bilinmeyen hata')}</div>` : ''}
                    <span class="status-badge ${location.status}">${statusLabel[location.status]}</span>
                    ${retryBtn}
                </li>
            `;
        }).join('');
    }

    updateFailedList() {
        const container = document.getElementById('failedAddresses');
        const failedLocations = this.locations.filter(loc => loc.status === 'failed');

        if (failedLocations.length === 0) {
            container.innerHTML = '<li class="no-items">Başarısız adres yok</li>';
            return;
        }

        container.innerHTML = failedLocations.map(location => `
            <li class="failed">
                <div class="title">${this.escapeHtml(location.title)}</div>
                <div class="address">${this.escapeHtml(location.address)}</div>
                <div class="error">${this.escapeHtml(location.errorMessage || 'Bilinmeyen hata')}</div>
                <button class="btn btn-retry" onclick="geocodeMap.retryAddress(${location.addressId})">
                    Tekrar Dene
                </button>
            </li>
        `).join('');
    }

    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <span class="toast-message">${this.escapeHtml(message)}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

const geocodeMap = new GeocodeMap();
