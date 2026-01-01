# Dosya Transfer Servisi API

SQLite ile metadata depolama kullanan, PSR standartlarına uygun saf PHP ile geliştirilmiş REST API dosya transfer servisi.

## Özellikler

- Validasyonlu dosya yükleme (maks 10MB, pdf/jpg/png/txt)
- Dosya indirme
- Dosya metadata sorgulama
- Sayfalı dosya listeleme
- Soft delete desteği
- Güvenli dosya depolama (rastgele dosya adları)
- MIME tipi doğrulama
- Hata loglama

## Gereksinimler

- PHP 8.1+
- Composer
- SQLite3 extension
- fileinfo extension

## Bağımlılıklar

| Paket | Açıklama |
|-------|----------|
| catfan/medoo | Hafif veritabanı framework'ü (SQLite) |
| vlucas/phpdotenv | Ortam değişkenleri yönetimi |

## Kurulum

```bash
# 1) Bağımlılıkları yükle
composer install

# 2) Ortam dosyasını kopyala
cp .env.example .env

# 3) Veritabanını başlat
php init-db.php

# 4) Geliştirme sunucusunu başlat
php -S localhost:8000 -t public
```

## Yapılandırma

`.env` dosyasını düzenleyerek yapılandırabilirsiniz:

```env
APP_ENV=development
APP_DEBUG=true

UPLOAD_MAX_SIZE=10485760        # 10MB (byte)
UPLOAD_PATH=storage/uploads
ALLOWED_EXTENSIONS=pdf,jpg,jpeg,png,txt

DB_PATH=storage/database/file_transfer.sqlite

LOG_PATH=storage/logs/error.log
```

## API Endpointleri

### 1. Dosya Yükle

```
POST /api/files
```

Sisteme yeni dosya yükler.

**İstek:**
- Content-Type: `multipart/form-data`
- Body:
  - `file` (zorunlu): Yüklenecek dosya
  - `description` (opsiyonel): Dosya açıklaması

**Örnek istek:**

```bash
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/sample.pdf" \
  -F "description=Test PDF dosyası"
```

**Başarılı yanıt (201):**

```json
{
  "success": true,
  "data": {
    "fileId": "550e8400-e29b-41d4-a716-446655440000",
    "originalName": "sample.pdf",
    "description": "Test PDF dosyası",
    "size": 589,
    "mimeType": "application/pdf",
    "checksum": "a591a6d40bf420404a011733cfb7b190d62c65bf0bcda32b57b277d9ad9f146e",
    "createdAt": "2025-12-29T10:30:00Z"
  }
}
```

---

### 2. Dosya Metadata'sını Getir

```
GET /api/files/{fileId}
```

Belirli bir dosyanın metadata bilgilerini getirir.

**Örnek istek:**

```bash
curl http://localhost:8000/api/files/550e8400-e29b-41d4-a716-446655440000
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "fileId": "550e8400-e29b-41d4-a716-446655440000",
    "originalName": "document.pdf",
    "description": "Belgem",
    "size": 102400,
    "mimeType": "application/pdf",
    "checksum": "a591a6d40bf420404a011733cfb7b190d62c65bf0bcda32b57b277d9ad9f146e",
    "createdAt": "2025-12-29T10:30:00Z"
  }
}
```

---

### 3. Dosya İndir

```
GET /api/files/{fileId}/download
```

Dosya içeriğini indirir.

**Yanıt:**
- Content-Type: Dosyanın orijinal MIME tipi
- Content-Disposition: `attachment; filename="orijinal_dosyaadi.ext"`

**Örnek istek:**

```bash
curl -O -J http://localhost:8000/api/files/550e8400-e29b-41d4-a716-446655440000/download
```

---

### 4. Dosyaları Listele

```
GET /api/files
```

Tüm dosyaların sayfalı listesini getirir.

**Query Parametreleri:**

| Parametre | Tip | Varsayılan | Açıklama |
|-----------|-----|------------|----------|
| page | integer | 1 | Sayfa numarası |
| pageSize | integer | 10 | Sayfa başına öğe (maks: 100) |

**Örnek istek:**

```bash
curl "http://localhost:8000/api/files?page=1&pageSize=10"
```

**Başarılı yanıt (200):**

```json
{
  "success": true,
  "data": {
    "files": [
      {
        "fileId": "550e8400-e29b-41d4-a716-446655440000",
        "originalName": "document.pdf",
        "description": "Belgem",
        "size": 102400,
        "mimeType": "application/pdf",
        "checksum": "a591a6d40bf420...",
        "createdAt": "2025-12-29T10:30:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "pageSize": 10,
      "totalItems": 45,
      "totalPages": 5
    }
  }
}
```

---

### 5. Dosya Sil

```
DELETE /api/files/{fileId}
```

Dosyayı soft delete yapar (silinmiş olarak işaretler ama veriyi korur).

**Örnek istek:**

```bash
curl -X DELETE http://localhost:8000/api/files/550e8400-e29b-41d4-a716-446655440000
```

**Başarılı yanıt:** `204 No Content`

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
| 201 | Oluşturuldu (dosya yüklendi) |
| 204 | İçerik Yok (dosya silindi) |
| 400 | Hatalı İstek (validasyon hatası) |
| 404 | Bulunamadı (dosya mevcut değil) |
| 413 | Yük Çok Büyük (dosya 10MB'ı aşıyor) |
| 415 | Desteklenmeyen Medya Tipi (geçersiz dosya tipi) |
| 500 | Sunucu Hatası |

### Hata Kodları

| Kod | Açıklama |
|-----|----------|
| `VALIDATION_ERROR` | Girdi validasyonu başarısız |
| `FILE_NOT_FOUND` | İstenen dosya bulunamadı |
| `FILE_TOO_LARGE` | Dosya maksimum boyutu aşıyor |
| `INVALID_FILE_TYPE` | Dosya uzantısına izin verilmiyor |
| `INVALID_CONTENT_TYPE` | Dosya MIME tipi geçersiz |
| `NO_FILE` | Dosya yüklenmedi |
| `EMPTY_FILE` | Dosya boş |
| `INVALID_FILE_ID` | Dosya ID formatı geçersiz |
| `INVALID_PAGE` | Sayfa numarası geçersiz |
| `INVALID_PAGE_SIZE` | Sayfa boyutu geçersiz |

### Hata Örnekleri

**Boş dosya yükleme (400):**

```bash
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/empty.txt"
```

```json
{
  "success": false,
  "error": {
    "code": "EMPTY_FILE",
    "message": "Dosya boş"
  }
}
```

**Geçersiz uzantı (415):**

```bash
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/invalid.exe"
```

```json
{
  "success": false,
  "error": {
    "code": "INVALID_FILE_TYPE",
    "message": "Dosya uzantısına izin verilmiyor"
  }
}
```

**Yanlış MIME tipi (415):**

```bash
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/fake-image.png"
```

```json
{
  "success": false,
  "error": {
    "code": "INVALID_CONTENT_TYPE",
    "message": "Dosya MIME tipi geçersiz"
  }
}
```

**Dosya bulunamadı (404):**

```bash
curl http://localhost:8000/api/files/00000000-0000-0000-0000-000000000000
```

```json
{
  "success": false,
  "error": {
    "code": "FILE_NOT_FOUND",
    "message": "İstenen dosya bulunamadı"
  }
}
```

**Geçersiz dosya ID (400):**

```bash
curl http://localhost:8000/api/files/invalid-id
```

```json
{
  "success": false,
  "error": {
    "code": "INVALID_FILE_ID",
    "message": "Dosya ID formatı geçersiz"
  }
}
```

## Proje Yapısı

```
file-transfer-service/
├── public/
│   ├── index.php              # Giriş noktası
│   └── .htaccess              # URL yönlendirme
├── src/
│   ├── Controllers/
│   │   └── FileController.php # HTTP istek yönetimi
│   ├── Services/
│   │   └── FileService.php    # İş mantığı
│   ├── Repositories/
│   │   └── FileRepository.php # Veritabanı işlemleri
│   ├── Models/
│   │   └── File.php           # Dosya entity'si
│   ├── Validators/
│   │   └── FileValidator.php  # Girdi validasyonu
│   ├── Exceptions/
│   │   ├── ValidationException.php
│   │   ├── NotFoundException.php
│   │   └── FileUploadException.php
│   ├── Http/
│   │   ├── Request.php        # İstek wrapper'ı
│   │   ├── Response.php       # JSON yanıt yardımcısı
│   │   └── Router.php         # Basit routing
│   └── Config/
│       ├── Database.php       # SQLite bağlantısı
│       └── Logger.php         # Hata loglama
├── storage/
│   ├── uploads/               # Yüklenen dosyalar
│   ├── database/              # SQLite veritabanı
│   └── logs/                  # Hata logları
├── .env.example
├── composer.json
├── init-db.php
└── README.md
```

## Test Dosyaları

Projeye hızlı test yapabilmeniz için örnek dosyalar eklenmiştir:

```
tests/fixtures/
├── sample.pdf                      # Test PDF dosyası (589 byte)
├── sample.png                      # Test PNG resmi (69 byte)
├── sample.txt                      # Test metin dosyası (227 byte)
└── edge-cases/
    ├── empty.txt                   # Boş dosya (0 byte) - EMPTY_FILE hatası
    ├── special-chars-öçşğü.txt     # Türkçe karakterli dosya adı
    ├── spaces in name.txt          # Boşluklu dosya adı
    ├── invalid.exe                 # Geçersiz uzantı - INVALID_FILE_TYPE hatası
    └── fake-image.png              # Yanlış MIME tipi - INVALID_CONTENT_TYPE hatası
```

## Test

Sunucuyu başlattıktan sonra bu komutlarla test edebilirsiniz:

### Başarılı Senaryolar

```bash
# PDF yükle
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/sample.pdf" \
  -F "description=Test PDF dosyası"

# PNG yükle
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/sample.png"

# TXT yükle
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/sample.txt"

# Özel karakterli dosya adı
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/special-chars-öçşğü.txt"

# Boşluklu dosya adı
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/spaces in name.txt"

# Tüm dosyaları listele
curl http://localhost:8000/api/files

# Sayfalama ile listele
curl "http://localhost:8000/api/files?page=1&pageSize=5"

# Belirli dosya detayı
curl http://localhost:8000/api/files/{fileId}

# Dosya indir
curl -O -J http://localhost:8000/api/files/{fileId}/download

# Dosya sil
curl -X DELETE http://localhost:8000/api/files/{fileId}
```

### Hata Senaryoları (Edge Cases)

```bash
# Boş dosya yükleme (400 - EMPTY_FILE)
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/empty.txt"

# Geçersiz uzantı (415 - INVALID_FILE_TYPE)
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/invalid.exe"

# Yanlış MIME tipi (415 - INVALID_CONTENT_TYPE)
curl -X POST http://localhost:8000/api/files \
  -F "file=@tests/fixtures/edge-cases/fake-image.png"

# Olmayan dosya ID (404 - FILE_NOT_FOUND)
curl http://localhost:8000/api/files/00000000-0000-0000-0000-000000000000

# Geçersiz dosya ID formatı (400 - INVALID_FILE_ID)
curl http://localhost:8000/api/files/invalid-id

# Geçersiz sayfa numarası (400 - INVALID_PAGE)
curl "http://localhost:8000/api/files?page=-1"

# Geçersiz sayfa boyutu (400 - INVALID_PAGE_SIZE)
curl "http://localhost:8000/api/files?pageSize=999"

# Dosya olmadan istek (400 - NO_FILE)
curl -X POST http://localhost:8000/api/files
```

## Güvenlik Özellikleri

1. **Rastgele Dosya Adları:** Dosyalar UUID tabanlı isimlerle saklanır, path traversal saldırılarını önler
2. **MIME Doğrulama:** Dosya içeriği sadece uzantıya değil, `finfo` ile doğrulanır
3. **Girdi Validasyonu:** Tüm girdiler işlenmeden önce doğrulanır
4. **Soft Delete:** Dosyalar silinmiş olarak işaretlenir ama hemen kaldırılmaz
5. **Hata Loglama:** Tüm hatalar zaman damgası ve stack trace ile loglanır
