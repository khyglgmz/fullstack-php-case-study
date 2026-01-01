# Case Study Projeleri

Bu repository, farklı teknolojilerle geliştirilmiş 4 adet case study projesini içermektedir. Her proje bağımsız çalışabilir ve kendi dokümantasyonuna sahiptir.

## Projeler

| Proje | Teknoloji | Açıklama |
|-------|-----------|----------|
| [laravel-export](./laravel-export) | Laravel 12, PHP 8.2, SQLite | Ürün verisi JSON export REST API |
| [php-file-transfer](./php-file-transfer) | Saf PHP 8.1, SQLite | Dosya yükleme/indirme servisi |
| [php-geocode-map](./php-geocode-map) | PHP 8.1, Leaflet.js | Adres geocoding ve harita görselleştirme |
| [react-listing](./react-listing) | React 18, TypeScript, Vite | Kullanıcı listeleme arayüzü |

## Proje Detayları

### 1. Laravel Export API

Ürün verisini JSON dosyasına export eden REST API servisi.

**Özellikler:**
- Senkron ve asenkron (queue) export
- Fiyat ve durum filtresi
- Web arayüzü (bonus)

```bash
cd laravel-export
composer install
php artisan serve
# http://localhost:8000
```

---

### 2. PHP File Transfer

PSR standartlarına uygun, saf PHP ile geliştirilmiş dosya transfer servisi.

**Özellikler:**
- Dosya yükleme (maks 10MB, pdf/jpg/png/txt)
- MIME tipi doğrulama
- Soft delete desteği
- Sayfalı listeleme

```bash
cd php-file-transfer
composer install
php init-db.php
php -S localhost:8000 -t public
# http://localhost:8000
```

---

### 3. PHP Geocode Map

Adresleri koordinata çeviren ve harita üzerinde gösteren uygulama.

**Özellikler:**
- Nominatim (OpenStreetMap) API entegrasyonu
- SQLite cache mekanizması
- Leaflet.js interaktif harita
- Başarısız adresler için retry

```bash
cd php-geocode-map
composer install
php -S localhost:8000 -t public
# http://localhost:8000/map.php
```

---

### 4. React User Listing

JSONPlaceholder API'sinden kullanıcıları listeleyen React uygulaması.

**Özellikler:**
- Debounce'lu arama
- Detay modalı
- Loading/error/empty state yönetimi
- Klavye erişilebilirliği

```bash
cd react-listing
npm install
npm run dev
# http://localhost:5173
```

## Gereksinimler

| Proje | Gereksinimler |
|-------|---------------|
| laravel-export | PHP 8.2+, Composer, SQLite |
| php-file-transfer | PHP 8.1+, Composer, SQLite3 ext |
| php-geocode-map | PHP 8.1+, Composer, SQLite3 ext |
| react-listing | Node.js 18+, npm |

## Ortak Özellikler

Tüm projelerde:
- Detaylı hata yönetimi ve standart JSON hata formatı
- Ortam değişkenleri ile yapılandırma (`.env`)
- Katmanlı mimari (Controller, Service, Repository)
- Dokümante edilmiş API endpoint'leri
