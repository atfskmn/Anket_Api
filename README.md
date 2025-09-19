
# 📋 Anket Programı

## Proje Tanımı
Online anket oluşturma, paylaşma ve sonuçlarını analiz etme sistemi. Kullanıcılar çoktan seçmeli ve açık uçlu sorularla anket oluşturabilir, paylaşabilir ve sonuçlarını analiz edebilir.

## Proje Hedefleri
- Anket oluşturma ve düzenleme sistemi
- Çoktan seçmeli ve açık uçlu soru tipleri
- Anket paylaşımı ve cevaplama sistemi
- Sonuç analizi ve grafik raporlama
- Admin paneli ile anket yönetimi

## 🗺Veritabanı Yapısı

### 1. surveys (Anketler)
- id (Primary Key)
- title (varchar 200) - Anket başlığı
- description (text) - Anket açıklaması
- start_date (timestamp) - Başlangıç tarihi
- end_date (timestamp) - Bitiş tarihi
- is_active (boolean) - Aktif/pasif durumu
- is_public (boolean) - Herkese açık mı
- allow_anonymous (boolean) - Anonim cevaba izin ver
- max_responses (integer) - Maksimum cevap sayısı
- response_count (integer) - Mevcut cevap sayısı
- created_by (integer) - Oluşturan kullanıcı ID
- created_at (timestamp)
- updated_at (timestamp)

### 2. questions (Sorular)
- id (Primary Key)
- survey_id (Foreign Key) - surveys.id
- question_text (text) - Soru metni
- question_type (enum) - Soru tipi (multiple_choice, single_choice, text, textarea, rating, yes_no)
- is_required (boolean) - Zorunlu mu
- sort_order (integer) - Sıralama
- help_text (text) - Yardım metni
- created_at (timestamp)
- updated_at (timestamp)

### 3. question_options (Soru Seçenekleri)
- id (Primary Key)
- question_id (Foreign Key) - questions.id
- option_text (varchar 255) - Seçenek metni
- sort_order (integer) - Sıralama
- is_other (boolean) - "Diğer" seçeneği mi
- created_at (timestamp)
- updated_at (timestamp)

### 4. responses (Cevaplar)
- id (Primary Key)
- survey_id (Foreign Key) - surveys.id
- respondent_name (varchar 100) - Cevaplayıcı adı
- respondent_email (varchar 255) - Cevaplayıcı e-posta
- submitted_at (timestamp) - Gönderim tarihi
- ip_address (varchar 45) - IP adresi
- user_agent (text) - Tarayıcı bilgisi
- is_complete (boolean) - Tamamlandı mı
- created_at (timestamp)
- updated_at (timestamp)

### 5. answers (Yanıtlar)
- id (Primary Key)
- response_id (Foreign Key) - responses.id
- question_id (Foreign Key) - questions.id
- option_id (Foreign Key) - question_options.id (nullable)
- answer_text (text) - Metin yanıtı
- rating_value (integer) - Puanlama değeri
- created_at (timestamp)
- updated_at (timestamp)

## 🔌 API Endpoint'leri

### Public Endpoints
- `GET /api/surveys/{id}` - Anket detayı ve soruları
- `POST /api/surveys/{id}/responses` - Anket cevapla
- `GET /api/surveys/{id}/results` - Anket sonuçları (public ise)
- `GET /api/surveys/public` - Herkese açık anketler

### User Endpoints (JWT korumalı)
- `POST /api/surveys` - Anket oluştur
- `PUT /api/surveys/{id}` - Anket güncelle
- `DELETE /api/surveys/{id}` - Anket sil
- `GET /api/user/surveys` - Anketlerim
- `POST /api/surveys/{id}/questions` - Soru ekle
- `PUT /api/questions/{id}` - Soru güncelle
- `DELETE /api/questions/{id}` - Soru sil
- `GET /api/surveys/{id}/analytics` - Anket analizi

### Admin Endpoints (JWT korumalı)
- `GET /api/admin/surveys` - Tüm anketler
- `GET /api/admin/responses` - Tüm cevaplar
- `GET /api/admin/analytics` - Genel istatistikler
- `PUT /api/admin/surveys/{id}/status` - Anket durumu güncelle
- `DELETE /api/admin/surveys/{id}` - Anket sil (admin)

### Auth Endpoints
- `POST /api/auth/login` - Giriş yap
- `POST /api/auth/register` - Kayıt ol
- `POST /api/auth/logout` - Çıkış yap
- `GET /api/auth/me` - Kullanıcı bilgileri

##  Menü Yapısı

### Ana Menü
- Ana Sayfa
-  Anketler
-  Sonuçlar
-  Anket Oluştur
-  Giriş/Kayıt

### Kullanıcı Menü (Giriş sonrası)
-  Ana Sayfa
- Anketlerim
-  Sonuçlarım
- Yeni Anket
- Profil

### Admin Menü
-  Kontrol Paneli
-  Anket Yönetimi
-  Kullanıcı Yönetimi
-  Raporlar
-  Profil

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
