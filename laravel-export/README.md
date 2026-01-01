# Laravel Product Export API

Bu servis, ürün verisini JSON dosyasına export eden basit bir REST API sunar.
Case study için hazırlanmıştır ve okunabilir, küçük bir mimariyle kuruludur.

## Gereksinimler

- PHP 8.2+
- Composer
- SQLite

## Kurulum

```bash
# 1) Bağımlılıkları yükle
composer install

# 2) .env dosyasını oluştur
cp .env.example .env

# 3) Uygulama anahtarı oluştur
php artisan key:generate

# 4) Veritabanı dosyası oluştur (SQLite için)
touch database/database.sqlite

# 5) Migration'ları çalıştır
php artisan migrate

# 6) Örnek verileri yükle
php artisan db:seed --class=ProductSeeder

# 7) Sunucuyu başlat
php artisan serve
```

## .env Örneği

```env
APP_NAME=ProductExport
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/full/path/to/database/database.sqlite

QUEUE_CONNECTION=database
```

## API

### Senkron Export

```
GET /api/export/products
```

Opsiyonel query parametreleri:

| Parametre | Tip | Açıklama |
|-----------|-----|----------|
| is_active | boolean | Aktif/pasif filtre |
| min_price | numeric | Minimum fiyat |
| max_price | numeric | Maksimum fiyat |

Örnek istekler:

```bash
# Tüm ürünleri export et
curl http://localhost:8000/api/export/products

# Sadece aktif ürünler
curl "http://localhost:8000/api/export/products?is_active=true"

# Fiyat aralığına göre
curl "http://localhost:8000/api/export/products?min_price=50&max_price=500"

# Birden fazla filtre
curl "http://localhost:8000/api/export/products?is_active=true&min_price=100"
```

Başarılı response (200):

```json
{
    "success": true,
    "data": {
        "file_path": "exports/products_20251229_1430.json",
        "record_count": 50,
        "created_at": "2025-12-29T14:30:00+00:00",
        "file_size": "12.5 KB"
    }
}
```

### Asenkron Export (Queue)

```
POST /api/export/products/async
```

Body (JSON - opsiyonel):

```json
{
    "is_active": true,
    "min_price": 50,
    "max_price": 500
}
```

Örnek istek:

```bash
curl -X POST http://localhost:8000/api/export/products/async \
  -H "Content-Type: application/json" \
  -d '{"is_active": true}'
```

Response (202):

```json
{
    "success": true,
    "message": "Export işlemi kuyruğa eklendi.",
    "data": {
        "status": "queued",
        "filters": {
            "is_active": true
        }
    }
}
```

Queue worker başlatma:

```bash
php artisan queue:work
```

## Hata Örnekleri

Validasyon hatası (422):

```json
{
    "success": false,
    "error": {
        "message": "Validasyon hatası",
        "details": {
            "max_price": ["max_price parametresi min_price değerinden büyük veya eşit olmalıdır!"]
        }
    }
}
```

Veri bulunamadı (404):

```json
{
    "success": false,
    "error": {
            "message": "Export edilecek ürün bulunamadı",
        "code": 404
    }
}
```

## Proje Yapısı

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   └── ExportController.php
│   └── Requests/
│       └── ExportProductsRequest.php
├── Jobs/
│   └── ExportProductsJob.php
├── Models/
│   └── Product.php
├── Services/
│   └── ProductExportService.php
└── Exceptions/
    └── ExportException.php

resources/views/
├── welcome.blade.php          # Ana sayfa
└── export/
    └── products.blade.php     # Export form sayfası

routes/
├── api.php                    # API routes
└── web.php                    # Web routes

storage/app/private/exports/   # Export dosyaları
```
Export dosyaları storage/app/private/exports altında tutulur.
Varsayılan olarak public erişime açık değildir.
## Teknik Notlar

- Laravel 12 kullanıldı (10/11 ile aynı yapı çalışır)
- Service class + form request validasyonu
- Custom exception ile standart JSON hata yapısı
- Queue job ile asenkron export
- SQLite varsayılan, MySQL ile de çalışır

## Web Arayüzü (Bonus)

Bu arayüz, API’nin kullanımını görsel olarak göstermek amacıyla
bilinçli olarak minimal tutulmuştur.

```
GET /export/products
```

Özellikler:
- Filtre seçenekleri (is_active, min_price, max_price)
- Export butonu
- Sonuç gösterimi (dosya yolu, kayıt sayısı, boyut)
- JSON dosya indirme linki

```
http://localhost:8000/export/products
```

## Test

Sunucuyu başlattıktan sonra bu komutlarla test edebilirsiniz:

### Başarılı Senaryolar

```bash
# Tüm ürünleri export et
curl http://localhost:8000/api/export/products | jq

# Sadece aktif ürünler
curl "http://localhost:8000/api/export/products?is_active=true" | jq

# Sadece pasif ürünler
curl "http://localhost:8000/api/export/products?is_active=false" | jq

# Fiyat aralığına göre
curl "http://localhost:8000/api/export/products?min_price=50&max_price=500" | jq

# Birden fazla filtre
curl "http://localhost:8000/api/export/products?is_active=true&min_price=100" | jq

# Asenkron export (queue)
curl -X POST http://localhost:8000/api/export/products/async \
  -H "Content-Type: application/json" | jq

# Asenkron export filtre ile
curl -X POST http://localhost:8000/api/export/products/async \
  -H "Content-Type: application/json" \
  -d '{"is_active": true, "min_price": 50}' | jq

# Queue worker (ayrı terminalde)
php artisan queue:work

# Web arayüzü
open http://localhost:8000/export/products
```

### Hata Senaryoları

```bash
# max_price < min_price (422 - Validasyon hatası)
curl "http://localhost:8000/api/export/products?min_price=500&max_price=100" | jq

# Negatif fiyat değeri (422 - Validasyon hatası)
curl "http://localhost:8000/api/export/products?min_price=-50" | jq

# Geçersiz is_active değeri (422 - Validasyon hatası)
curl "http://localhost:8000/api/export/products?is_active=invalid" | jq

# Sayısal olmayan fiyat (422 - Validasyon hatası)
curl "http://localhost:8000/api/export/products?min_price=abc" | jq

# Sonuç bulunamadı - çok yüksek min_price (404 - Veri bulunamadı)
curl "http://localhost:8000/api/export/products?min_price=999999" | jq

# Asenkron export geçersiz JSON body (422 - Validasyon hatası)
curl -X POST http://localhost:8000/api/export/products/async \
  -H "Content-Type: application/json" \
  -d '{"min_price": -100}' | jq

# Olmayan endpoint (404 - Not Found)
curl http://localhost:8000/api/export/invalid | jq

# Yanlış HTTP method (405 - Method Not Allowed)
curl -X DELETE http://localhost:8000/api/export/products | jq
```

## Export Dosya Formatı

```json
{
    "exported_at": "2025-12-29T14:30:00+00:00",
    "total_count": 50,
    "products": [
        {
            "id": 1,
            "name": "Elektronik Ürün 1",
            "description": "Bu ürün #1 için açıklama metni.",
            "price": "149.99",
            "stock": 100,
            "sku": "SKU-00001",
            "is_active": true,
            "created_at": "2025-12-29T14:00:00.000000Z",
            "updated_at": "2025-12-29T14:00:00.000000Z"
        }
    ]
}
```
