INSERT INTO places (name, category, description, lat, lng, ai_context, popular_score) VALUES 
('Haburman Köprüsü', 'Historical', 'Selçuklu döneminden kalma tarihi köprü.', 38.123456, 39.123456, 'Sen bir tarihçisin, köprünün taş yapısından bahset.', 100),
('Şeyhandede Şelalesi', 'Nature', 'Doğal güzelliği ile ünlü bir şelale.', 38.124567, 39.125678, 'Sen bir doğa rehberisin, şelalenin serinliğini anlat.', 80),
('Ulu Cami', 'Historical', 'İlçenin en eski ve görkemli camisi.', 38.125678, 39.126789, 'Caminin mimari detaylarını ve huzurunu vurgula.', 90),
('Çeteci Abdullah Paşa Medresesi', 'Historical', '18. yüzyıldan kalma tarihi medrese.', 38.127000, 39.127000, 'Medresenin eğitim tarihindeki yerini ve taş işçiliğini anlat.', 85),
('Beyler Sarayı', 'Historical', 'Osmanlı döneminde idari merkez olarak kullanılan saray kalıntıları.', 38.128000, 39.128000, 'Sarayın eski görkemini ve yerel bey yönetimini tasvir et.', 80),
('Saray Hamamı', 'Historical', 'Beyler Sarayı yerleşkesine ait tarihi hamam yapısı.', 38.129000, 39.129000, 'Osmanlı hamam kültürünü ve kubbe yapısını vurgula.', 75),
('Karakaya Hanı', 'Historical', 'İpek yolu güzergahında bulunan konaklama mekanı.', 38.130000, 39.130000, 'Kervansaray hayatını ve ticaret yollarını anlat.', 70),
('Sinagog ve Kilise', 'Historical', 'İlçedeki farklı inanışların bir arada yaşadığı kültürel miras.', 38.131000, 39.131000, 'Çermik\'teki inanç hoşgörüsünü ve mimari çeşitliliği anlat.', 65),
('Çermik Kaplıcaları', 'HotSpring', 'Birçok hastalığa iyi gelen şifalı sular.', 38.126789, 39.127890, 'Kaplıcanın mineral özelliklerinden ve sağlığa yararlarından bahset.', 110),
('Gelincik Dağı', 'Nature', 'Eşsiz manzarasıyla doğa yürüyüşü rotası.', 38.127890, 39.128901, 'Dağın zirvesindeki manzarayı ve temiz havayı anlat.', 70),
('Sinek Çayı Şelalesi', 'Nature', 'Sinek Çayı üzerinde bulunan büyüleyici şelale.', 38.130000, 39.140000, 'Şelalenin sesini ve etraftaki bitki örtüsünü tasvir et.', 85),
('Gaban Kral Yolu', 'Nature', 'Antik dönemden kalma tarihi ve doğal yürüyüş yolu.', 38.135000, 39.145000, 'Yolun antik taş döşemelerini ve çevredeki vadiyi anlat.', 65),
('Sinek Çayı ve Kaynağı', 'Nature', 'Çayın doğduğu buz gibi su kaynağı.', 38.140000, 39.150000, 'Suyun tazeliğini ve kaynağın etrafındaki piknik alanlarını belirt.', 75);

-- İşletmeler (Restoran, Otel)
INSERT INTO businesses (username, password, business_name, contact_info, lat, lng, category) VALUES
('osmanli_sofrasi', 'pass123', 'Osmanlı Sofrası', '0412 123 45 67', 38.128000, 39.129000, 'Restaurant'),
('termal_otel', 'pass123', 'Çermik Termal Otel', '0412 765 43 21', 38.129000, 39.130000, 'Hotel');

-- İşletme Ürünleri
INSERT INTO products (business_id, name, price, description) VALUES
(1, 'Çermik Tavası', 150.00, 'Meşhur yerel tava yemeği.'),
(1, 'Kuyulu Kebap', 180.00, 'Geleneksel kuyu kebabı.'),
(2, 'Standart Oda', 1200.00, 'Kahvaltı dahil çift kişilik oda.'),
(2, 'King Suite', 2500.00, 'Lüks suit oda.');

-- Belediye Hizmetleri
INSERT INTO services (title, description, status) VALUES
('Yeni Meydan Projesi', 'Kent meydanının modern bir görünüme kavuşturulması projesi.', 'Ongoing'),
('Gençlik Merkezi', 'Gençler için modern eğitim ve spor alanlarının tamamlanması.', 'Completed'),
('Asfalt Çalışmaları', 'İlçemiz genelindeki yol düzenleme çalışmaları.', 'Ongoing');

-- Yol Üstü Durakları (Doğa Mekanları için)
-- ID Tahminleri: 3 (Şeyhandede), 6 (Gelincik), 7 (Sinek Çayı Şelalesi), 8 (Gaban Kral Yolu), 9 (Sinek Çayı Kaynağı)
INSERT INTO road_stops (parent_place_id, poi_name, trigger_radius, audio_script) VALUES
(6, 'Zirve Başlangıcı', 100, 'Gelincik Dağı zirve tırmanışına buradan başlıyorsunuz. Keyifli yürüyüşler!'),
(3, 'Şelale Sesi', 150, 'Şeyhandede şelalesinin sesini duymaya başladınız. Az sonra muazzam bir manzara sizi bekliyor.'),
(7, 'Eski Değirmen', 100, 'Sol tarafınızda Sinek Çayı kıyısında eski bir değirmen kalıntısı göreceksiniz.'),
(8, 'Antik Yazıtlar', 80, 'Kral yolu üzerindeki bu kayalıklarda antik dönemden kalma silik yazıtlar bulunmaktadır.'),
(9, 'Buz Gibi Su', 50, 'Çayın kaynağına ulaştınız! Suyun sıcaklığı yıl boyu 4 derece civarındadır.'),
(1, 'Köprü Taşları', 50, 'Haburman köprüsünün devasa taş bloklarına dikkatli bakın, her biri Selçuklu mühendisliğinin harikasıdır.'),
(2, 'Cami Avlusu', 30, 'Ulu Cami avlusunun serinliği ve manevi atmosferi sizi karşılamak üzere.');
