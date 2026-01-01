# Kullanıcı Dizini - React Case Study

Basit bir "kullanıcı listeleme" ekranı için hazırlanmış küçük bir React uygulaması. Kullanıcıları bir API'den çekiyor, isimle filtreleme yapıyor ve tıklanınca detayları modalda gösteriyor. Amacı; okunabilir, sade ve kullanım senaryosu net bir çözüm sunmaktı.

## Özellikler

- JSONPlaceholder üzerinden kullanıcıları listeleme
- İsme göre debounce'lu arama
- Kullanıcıya tıklayınca detay modalı
- Loading / error / empty state ekranları
- Klavye ile erişilebilir kullanım
- Input validasyonu ve basit XSS önlemleri

## Gereksinimler

- Node.js 18+
- npm veya yarn

## Teknoloji Yığını

- **React 18** - UI kütüphanesi
- **Vite** - Build tool ve geliştirme sunucusu
- **TypeScript** - Tip güvenliği
- **Tailwind CSS** - Stil

## Kurulum

```bash
# 1) Bağımlılıkları yükle
npm install

# 2) Ortam dosyasını kopyala
cp .env.example .env
```

## Geliştirme

```bash
npm run dev
```

Uygulama `http://localhost:5173` adresinde çalışır.

## Derleme

```bash
# Üretim derlemesi
npm run build

# Derlemeyi önizle
npm run preview
```

## Ortam Değişkenleri

`.env` dosyasını düzenleyerek yapılandırabilirsiniz:

```env
VITE_API_BASE_URL=https://jsonplaceholder.typicode.com
VITE_API_TIMEOUT=10000
```

| Değişken | Açıklama | Varsayılan |
|----------|----------|------------|
| VITE_API_BASE_URL | API temel URL'i | https://jsonplaceholder.typicode.com |
| VITE_API_TIMEOUT | İstek zaman aşımı (ms) | 10000 |

## API Referansı

### Kullanıcıları Getir

```
GET /users
```

API'den tüm kullanıcıları getirir.

**Örnek istek:**

```bash
curl -X GET https://jsonplaceholder.typicode.com/users
```

**Başarılı yanıt (200):**

```json
[
  {
    "id": 1,
    "name": "Leanne Graham",
    "username": "Bret",
    "email": "Sincere@april.biz",
    "address": {
      "street": "Kulas Light",
      "suite": "Apt. 556",
      "city": "Gwenborough",
      "zipcode": "92998-3874",
      "geo": {
        "lat": "-37.3159",
        "lng": "81.1496"
      }
    },
    "phone": "1-770-736-8031 x56442",
    "website": "hildegard.org",
    "company": {
      "name": "Romaguera-Crona",
      "catchPhrase": "Multi-layered client-server neural-net",
      "bs": "harness real-time e-markets"
    }
  }
]
```

## Hata Yönetimi

Uygulama aşağıdaki durumları yönetir:

| Durum | Açıklama | Kullanıcı Deneyimi |
|-------|----------|-------------------|
| Yükleniyor | API isteği devam ediyor | Spinner gösterimi |
| Hata | API isteği başarısız | Hata mesajı + yeniden dene butonu |
| Boş | Sonuç bulunamadı | "Sonuç bulunamadı" mesajı |
| Başarılı | Veriler geldi | Kullanıcı kartları listesi |

**Hata durumu örneği:**

- Timeout: 10 saniye içinde yanıt alınamazsa
- Network hatası: İnternet bağlantısı yoksa
- API hatası: Sunucu 4xx/5xx döndürürse

## Proje Yapısı

```
src/
├── api/
│   └── userApi.ts           # Hata yönetimli API servisi
├── components/
│   ├── common/
│   │   ├── Modal.tsx        # Yeniden kullanılabilir modal bileşeni
│   │   ├── Spinner.tsx      # Yükleniyor göstergesi
│   │   └── ErrorMessage.tsx # Yeniden deneme butonlu hata gösterimi
│   └── users/
│       ├── UserCard.tsx     # Tekil kullanıcı kartı
│       ├── UserList.tsx     # Kullanıcı listesi kapsayıcısı
│       ├── UserSearch.tsx   # Doğrulamalı arama girişi
│       ├── UserModal.tsx    # Kullanıcı detay modalı
│       └── EmptyState.tsx   # Sonuç bulunamadı mesajı
├── hooks/
│   ├── useUsers.ts          # Veri çekme hook'u
│   ├── useDebounce.ts       # Debounce yardımcısı
│   └── useModal.ts          # Modal durum yönetimi
├── types/
│   └── user.ts              # TypeScript arayüzleri
├── utils/
│   ├── constants.ts         # Yapılandırma sabitleri
│   └── validators.ts        # Girdi temizleme
├── App.tsx                  # Ana uygulama bileşeni
├── main.tsx                 # Giriş noktası
└── index.css                # Tailwind içe aktarımları
```

## Uygulama Notları

- UI state küçük hook'lara ayrıldı (`useUsers`, `useDebounce`, `useModal`) böylece ana ekran daha temiz kaldı
- API katmanı timeout ve hata mesajlarını kullanıcı dostu hale getiriyor
- Arama input'u sanitize ediliyor ve uzunluğu kısıtlanıyor
- Varsayılan veri kaynağı JSONPlaceholder, ama `.env` ile değiştirilebilir

## SOLID Prensipleri Uygulamada

- **Single Responsibility:** UI, hook ve API katmanları ayrıldı
- **Open/Closed:** Liste, arama ve modal bileşenleri prop'larla yönetiliyor
- **Interface Segregation:** Küçük, amaca özel prop tipleri
- **Dependency Inversion:** `useUsers` doğrudan `fetch` değil, API fonksiyonunu kullanıyor

## Test

```bash
# Geliştirme sunucusunu başlat
npm run dev

# Tarayıcıda aç
open http://localhost:5173

# Test senaryoları:
# 1. Sayfa yüklendiğinde kullanıcı listesi görünmeli
# 2. Arama kutusuna yazınca liste filtrelenmeli (300ms debounce)
# 3. Kullanıcı kartına tıklayınca modal açılmalı
# 4. ESC tuşu veya dışarı tıklama ile modal kapanmalı
```
