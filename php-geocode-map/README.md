# PHP Geocode - Adres Koordinat Servisi

Adresleri koordinata çeviren ve harita üzerinde gösteren PHP uygulaması.

## Özellikler

- **Geocoding:** Nominatim (OpenStreetMap) API ile adres → koordinat dönüşümü
- **Cache:** SQLite veritabanı ile tekrar eden istekleri önleme
- **Rate Limiting:** Nominatim politikasına uygun 1 saniye bekleme
- **Harita:** Leaflet.js + OpenStreetMap ile interaktif harita
- **Hata Yönetimi:** Başarısız adresler için retry mekanizması

## Gereksinimler

- PHP >= 8.1
- Composer
- SQLite3 PHP extension
- mod_rewrite (Apache) veya nginx url rewriting

## Bağımlılıklar

| Paket | Açıklama |
|-------|----------|
| guzzlehttp/guzzle | HTTP client (Nominatim API istekleri) |
| catfan/medoo | Hafif veritabanı framework'ü (SQLite) |
| vlucas/phpdotenv | Ortam değişkenleri yönetimi |

## Kurulum

```bash
# 1) Bağımlılıkları yükle
composer install

# 2) Ortam değişkenlerini ayarla
cp .env.example .env

# 3) Dizin izinlerini ayarla
chmod -R 775 storage/

# 4) Web sunucusunu başlat
php -S localhost:8000 -t public

# 5) Tarayıcıda aç
open http://localhost:8000/map.php
```

## Ortam Değişkenleri

`.env` dosyasını düzenleyerek yapılandırabilirsiniz:

```env
# Uygulama
APP_ENV=development
APP_DEBUG=true

# Veritabanı
DB_PATH=storage/database/geocode.sqlite

# Nominatim API
NOMINATIM_USER_AGENT=php-geocode-app/1.0

# Loglama
LOG_PATH=storage/logs/error.log
```

| Değişken | Açıklama | Varsayılan |
|----------|----------|------------|
| APP_ENV | Uygulama ortamı | development |
| APP_DEBUG | Debug modu | true |
| DB_PATH | SQLite veritabanı yolu | storage/database/geocode.sqlite |
| NOMINATIM_USER_AGENT | API User-Agent header'ı | php-geocode-app/1.0 |
| LOG_PATH | Hata log dosyası yolu | storage/logs/error.log |

## API Endpointleri

### 1. Tüm Adresleri Geocode Et

```
POST /api/geocode
```

Tüm adresleri koordinata çevirir.

**İstek Body (JSON - opsiyonel):**

```json
{
  "forceRetry": false
}
```

| Parametre | Tip | Varsayılan | Açıklama |
|-----------|-----|------------|----------|
| forceRetry | boolean | false | true ise cache'i bypass eder |

**Örnek istek:**

```bash
curl -X POST http://localhost:8000/api/geocode \
  -H "Content-Type: application/json" \
  -d '{"forceRetry": false}'
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "locations": [
      {
        "id": 1,
        "addressId": 1,
        "title": "Merkez Ofis",
        "address": "Levent, Beşiktaş, İstanbul",
        "latitude": 41.0782,
        "longitude": 29.0109,
        "status": "success",
        "errorMessage": null,
        "geocodedAt": "2024-01-15T10:30:00Z"
      }
    ],
    "summary": {
      "total": 22,
      "success": 21,
      "failed": 1
    }
  }
}
```

---

### 2. Lokasyonları Listele

```
GET /api/locations
```

Tüm lokasyonları veya duruma göre filtrelenmiş lokasyonları listeler.

**Query Parametreleri:**

| Parametre | Tip | Açıklama |
|-----------|-----|----------|
| status | string | Filtre: success, failed |

**Örnek istekler:**

```bash
# Tüm lokasyonlar
curl http://localhost:8000/api/locations

# Sadece başarılı
curl "http://localhost:8000/api/locations?status=success"

# Sadece başarısız
curl "http://localhost:8000/api/locations?status=failed"
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "locations": [
      {
        "id": 1,
        "addressId": 1,
        "title": "Merkez Ofis",
        "address": "Levent, Beşiktaş, İstanbul",
        "latitude": 41.0782,
        "longitude": 29.0109,
        "status": "success",
        "errorMessage": null,
        "geocodedAt": "2024-01-15T10:30:00Z"
      }
    ],
    "count": 18
  }
}
```

---

### 3. Tek Adresi Yeniden Dene

```
POST /api/geocode/{addressId}
```

Belirli bir adresi yeniden geocode etmeye çalışır.

**Örnek istek:**

```bash
curl -X POST http://localhost:8000/api/geocode/5
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "id": 5,
    "addressId": 5,
    "title": "Şube Ofis",
    "address": "Kadıköy, İstanbul",
    "latitude": 40.9923,
    "longitude": 29.0253,
    "status": "success",
    "errorMessage": null,
    "geocodedAt": "2024-01-15T11:00:00Z"
  }
}
```

---

### 4. Adresleri Dosyadan Başlat

```
POST /api/initialize
```

`data/addresses.json` dosyasından adresleri veritabanına yükler.

**Örnek istek:**

```bash
curl -X POST http://localhost:8000/api/initialize
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "message": "Adresler başarıyla yüklendi",
    "count": 22
  }
}
```

---

## Hata Yanıtları

Tüm hatalar şu formatta döner:

```json
{
  "success": false,
  "error": {
    "code": "HATA_KODU",
    "message": "Okunabilir hata mesajı"
  }
}
```

### HTTP Durum Kodları

| Durum | Açıklama |
|-------|----------|
| 200 | Başarılı |
| 400 | Hatalı İstek (validasyon hatası) |
| 404 | Bulunamadı (adres mevcut değil) |
| 429 | Çok Fazla İstek (rate limit aşıldı) |
| 500 | Sunucu Hatası |
| 503 | Servis Kullanılamıyor (Nominatim API hatası) |

### Hata Kodları

| Kod | Açıklama |
|-----|----------|
| `VALIDATION_ERROR` | Girdi validasyonu başarısız |
| `ADDRESS_NOT_FOUND` | İstenen adres bulunamadı |
| `GEOCODE_FAILED` | Geocoding işlemi başarısız |
| `RATE_LIMIT_EXCEEDED` | API rate limit aşıldı |
| `API_ERROR` | Nominatim API hatası |
| `DATABASE_ERROR` | Veritabanı işlem hatası |

### Hata Örnekleri

**Olmayan adres ID (404):**

```bash
curl -X POST http://localhost:8000/api/geocode/999
```

```json
{
  "success": false,
  "error": {
    "code": "ADDRESS_NOT_FOUND",
    "message": "Adres bulunamadı"
  }
}
```

**Geçersiz adres ID formatı (400):**

```bash
curl -X POST http://localhost:8000/api/geocode/invalid
```

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Geçersiz adres ID formatı"
  }
}
```

**Geçersiz status parametresi (400):**

```bash
curl "http://localhost:8000/api/locations?status=invalid"
```

```json
{
  "success": false,
  "error": {
    "code": "INVALID_STATUS",
    "message": "Geçersiz durum. Kabul edilen değerler: success, failed"
  }
}
```

**Geocode başarısız (503):**

```bash
curl -X POST http://localhost:8000/api/geocode/5
```

```json
{
  "success": false,
  "error": {
    "code": "GEOCODE_FAILED",
    "message": "Adres koordinata çevrilemedi"
  }
}
```

---

## Proje Yapısı

```
php-geocode/
├── public/
│   ├── index.php              # API giriş noktası
│   ├── map.php                # Harita sayfası
│   ├── .htaccess              # URL yönlendirme
│   └── assets/
│       ├── css/style.css
│       └── js/map.js
├── src/
│   ├── Config/
│   │   ├── Database.php       # SQLite bağlantısı
│   │   └── Logger.php         # Hata loglama
│   ├── Controllers/
│   │   └── GeocodeController.php
│   ├── Services/
│   │   └── GeocodeService.php # İş mantığı
│   ├── Repositories/
│   │   └── LocationRepository.php
│   ├── Models/
│   │   └── Location.php
│   ├── Validators/
│   │   └── AddressValidator.php
│   ├── Http/
│   │   ├── Router.php
│   │   ├── Request.php
│   │   └── Response.php
│   ├── Exceptions/
│   │   ├── GeocodeException.php
│   │   └── ValidationException.php
│   └── Helpers/
│       └── PathHelper.php
├── data/
│   └── addresses.json         # Adres verileri
├── storage/
│   ├── database/              # SQLite DB
│   └── logs/                  # Hata logları
├── .env.example
├── .env
├── composer.json
└── README.md
```

## Harita Sayfası Özellikleri

1. **Marker'lar:** Başarılı geocode edilen her adres için marker
2. **Popup:** Marker'a tıklanınca title + address + koordinatlar
3. **Geocode et butonu:** Tüm adresleri geocode eder
4. **Tab Filtresi:** "Tümü" ve "Başarısızlar" tabları ile adres listesini filtreleme
5. **Tekrar Dene:** Her başarısız adres için retry butonu
6. **Otomatik Zoom:** Tüm marker'lar görünecek şekilde zoom

## Teknik Detaylar

### Cache Mekanizması

- Her adres `address_id` ile SQLite'da saklanır
- Daha önce başarıyla geocode edilmiş adresler tekrar API'ye sorulmaz
- `forceRetry: true` ile cache bypass edilebilir

### Rate Limiting

- Nominatim API'si 1 istek/saniye sınırı koyar
- Her geocode isteği arasında 1 saniye beklenir
- User-Agent header zorunludur

### Hata Yönetimi

- API hataları `status: failed` olarak kaydedilir
- Hata mesajı `error_message` alanında saklanır
- Başarısız adresler sidebar'da listelenir
- Her başarısız adres için retry mekanizması

## Test

Sunucuyu başlattıktan sonra bu komutlarla test edebilirsiniz:

### Başarılı Senaryolar

```bash
# Adresleri başlat
curl -X POST http://localhost:8000/api/initialize

# Tüm adresleri geocode et
curl -X POST http://localhost:8000/api/geocode \
  -H "Content-Type: application/json" \
  -d '{"forceRetry": false}'

# Cache bypass ile geocode et
curl -X POST http://localhost:8000/api/geocode \
  -H "Content-Type: application/json" \
  -d '{"forceRetry": true}'

# Tüm lokasyonları listele
curl http://localhost:8000/api/locations

# Başarılı olanları listele
curl "http://localhost:8000/api/locations?status=success"

# Başarısız olanları listele
curl "http://localhost:8000/api/locations?status=failed"

# Tek adresi yeniden dene
curl -X POST http://localhost:8000/api/geocode/1

# Harita sayfası
open http://localhost:8000/map.php
```

### Hata Senaryoları

```bash
# Olmayan adres ID (404 - ADDRESS_NOT_FOUND)
curl -X POST http://localhost:8000/api/geocode/999

# Geçersiz adres ID formatı (400 - VALIDATION_ERROR)
curl -X POST http://localhost:8000/api/geocode/invalid

# Negatif adres ID (400 - INVALID_ADDRESS_ID)
curl -X POST http://localhost:8000/api/geocode/-1

# Sıfır adres ID (400 - INVALID_ADDRESS_ID)
curl -X POST http://localhost:8000/api/geocode/0

# Geçersiz status parametresi (400 - INVALID_STATUS)
curl "http://localhost:8000/api/locations?status=invalid"

# Boş status parametresi (tüm lokasyonları döner - hata değil)
curl "http://localhost:8000/api/locations?status="
```
