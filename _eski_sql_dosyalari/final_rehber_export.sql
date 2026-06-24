-- MySQL dump 10.13  Distrib 8.0.17, for Win64 (x86_64)
--
-- Host: localhost    Database: rehber
-- ------------------------------------------------------
-- Server version	8.0.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `rehber`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `rehber` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `rehber`;

--
-- Table structure for table `ai_chat_logs`
--

DROP TABLE IF EXISTS `ai_chat_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_chat_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `question` text,
  `answer` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_chat_logs`
--

LOCK TABLES `ai_chat_logs` WRITE;
/*!40000 ALTER TABLE `ai_chat_logs` DISABLE KEYS */;
INSERT INTO `ai_chat_logs` VALUES (1,2,'kaplıca hakkında bilgi ver','Çermik Kaplıcaları hakkında bilgi: İlçemizin Tarihi ve Turistik Yerleri\r\nÇermik Melike Belkıs Kaplıcaları:  İlçe merkezinin Hamambaşı mevkiinde bulunan Melike Belkıs Kaplıcaları, tedavi özellikleri ve niteliği bakımından dünyanın en iyi kaplıcalarından biri olup tarihin çok eski dönemlerinden beri insanlığa şifa dağıtmaya devam etmektedir. \r\nBüyük Paşa” ve “Küçük Paşa” denilen tarihi hamamların yanında iki adet localı ve bir adet “Özel Aile Kabinleri” olmak üzere beş ayrı bölümde hizmet verilmektedir. Çermik Melike Belkıs Kaplıcaları dört mevsim hizmete açıktır. Tedavi amaçlı ku... (Detaylı bilgi için sayfanın altındaki tarihçe kısmına bakabilirsiniz)','2026-02-24 08:24:55'),(2,2,'çermikin neresinde kalıyor','Şu an Ulu Cami çok yakınındasınız. Ulu Cami: İlçenin batısındaki Kale Mahallesi’nde bulunmaktadır. Yapı ayrı zamanlarda inşa edilmiş, bitişik iki camiden oluşmaktadır. Batı kesimi ilk yapıdan kalmış, doğudaki tek kubbeli kısım ise sonradan ilave edilmiştir. Doğudan batıya doğru uzanan üç tonoz ile üzeri kaplı olan Ulu Cami, üçer keme','2026-02-25 06:58:11'),(3,2,'bana ömer halis demir parkını anlat','Şu an Ulu Cami çok yakınındasınız. Ulu Cami: İlçenin batısındaki Kale Mahallesi’nde bulunmaktadır. Yapı ayrı zamanlarda inşa edilmiş, bitişik iki camiden oluşmaktadır. Batı kesimi ilk yapıdan kalmış, doğudaki tek kubbeli kısım ise sonradan ilave edilmiştir. Doğudan batıya doğru uzanan üç tonoz ile üzeri kaplı olan Ulu Cami, üçer keme','2026-02-25 06:58:53'),(4,2,'acıktım','Şu an Sinek Çayı Şelalesi sayfasındasınız. Bu mekan hakkında size her şeyi anlatabilirim. Yerel bir dost gibi konuş. Piknik alanları ve en iyi yüzme noktalarını öner.','2026-02-25 09:11:37'),(5,2,'sinek şelalesini anlat','Şu an Ulu Cami çok yakınındasınız. Ulu Cami: İlçenin batısındaki Kale Mahallesi’nde bulunmaktadır. Yapı ayrı zamanlarda inşa edilmiş, bitişik iki camiden oluşmaktadır. Batı kesimi ilk yapıdan kalmış, doğudaki tek kubbeli kısım ise sonradan ilave edilmiştir. Doğudan batıya doğru uzanan üç tonoz ile üzeri kaplı olan Ulu Cami, üçer keme','2026-02-25 09:11:53');
/*!40000 ALTER TABLE `ai_chat_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `businesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `business_name` varchar(255) NOT NULL,
  `contact_info` text,
  `lat` varchar(50) DEFAULT NULL,
  `lng` varchar(50) DEFAULT NULL,
  `category` enum('Restaurant','Hotel') NOT NULL,
  `hotel_info` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image_main` varchar(255) DEFAULT NULL,
  `panorama_360` varchar(255) DEFAULT NULL,
  `image_gallery` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
INSERT INTO `businesses` VALUES (1,'osmanli_sofrasi','pass123','Osmanlı Sofrası','0412 123 45 67','38.12800000','39.12900000','Restaurant',NULL,'2026-02-21 21:21:20',NULL,NULL,NULL),(2,'termal_otel','pass123','Çermik Termal Otel','0412 765 43 21','38.12900000','39.13000000','Hotel',NULL,'2026-02-21 21:21:20',NULL,NULL,NULL),(3,'lezzet_duragi','$2y$10$ZLyBw/PGWwB21eeN3oo7MuKFb5hSQPLLS.m3GYadaqcRlYzwbnup2','Lezzet Durağı Restoran','Çermik Çarşı Merkezi, No:12','38.1384','39.4475','Restaurant',NULL,'2026-02-23 08:19:49',NULL,NULL,NULL),(4,'giran','$2y$10$o9ZOW33ZswXP605r3f4SGefRZhOhu2ekQUiBaIWlSCo2jtLS1cdu2','Çermik Gıran Park Termal Otel','Kaplıcalar Caddesi, No:45','38.13899716490099','39.478498672926214','Hotel','{\"Wifi\": \"Var\", \"Havuz\": \"Var\", \"Klima\": \"Tüm Odalarda\", \"Otopark\": \"Ücretsiz\", \"Kahvaltı\": \"Dahil\", \"Oda Sayısı\": \"80\", \"Yıldız Sayısı\": \"4 Yıldız\"}','2026-02-23 08:19:49',NULL,'https://s.insta360.com/p/91fa58de925e25d0e96d5e25cff4818b?region=SG',NULL);
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `communication_logs`
--

DROP TABLE IF EXISTS `communication_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `communication_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('SMS','Email') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('Success','Failed') DEFAULT 'Success',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `communication_logs`
--

LOCK TABLES `communication_logs` WRITE;
/*!40000 ALTER TABLE `communication_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `communication_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `genel_bilgiler`
--

DROP TABLE IF EXISTS `genel_bilgiler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `genel_bilgiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `baslik` varchar(255) NOT NULL,
  `icerik` text NOT NULL,
  `tarih` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `genel_bilgiler`
--

LOCK TABLES `genel_bilgiler` WRITE;
/*!40000 ALTER TABLE `genel_bilgiler` DISABLE KEYS */;
INSERT INTO `genel_bilgiler` VALUES (1,'Hükümet Konağı ','Geliştirdiğin Çermik ilçe rehberi uygulaması için \"Resmi Kurumlar\" veya \"Önemli Yerler\" kategorisinde kullanabileceğin Çermik Hükümet Konağı hakkındaki detaylı konum ve kurum bilgilerini aşağıda derledim:\r\n\r\nKonum ve Adres Bilgisi\r\nÇermik Hükümet Konağı, vatandaşların kolayca ulaşabilmesi için ilçe merkezinde, oldukça işlek bir noktada yer almaktadır.\r\n\r\nAçık Adres: Tepe Mahallesi, Diyarbakır Caddesi, Hükümet Konağı Binası, 21600 Çermik / Diyarbakır\r\n(Not: Binanın konumu Tepe ve Çukur mahallelerinin kesişim noktasına çok yakındır, resmi kayıtlarda bazı birimler Çukur Mah. Kapalı Sokak olarak da geçebilmektedir ancak ana giriş ve bilinen adres Tepe Mah. Diyarbakır Caddesi üzerindedir.)\r\n\r\nHükümet Konağı İçerisinde Hizmet Veren Kurumlar\r\nİlçedeki resmi idari işlerin tek merkezden yürütülebilmesi amacıyla birçok müdürlük bu binanın içinde (veya ek binasında) toplanmıştır. Bina içerisinde yer alan başlıca kamu kurumları şunlardır:\r\n\r\n1. Çermik Kaymakamlığı ve Bağlı Birimler:\r\n\r\nKaymakamlık Makamı ve İlçe Yazı İşleri Müdürlüğü: İlçenin genel idaresi, resmi yazışmalar ve vatandaş dilekçelerinin kabul edildiği ana birim.\r\n\r\nİlçe Sosyal Yardımlaşma ve Dayanışma Vakfı (SYDV): İhtiyaç sahibi vatandaşlara yakacak, barınma, eğitim, sağlık ve nakdi yardımların koordine edildiği merkez.\r\n\r\nİlçe Malmüdürlüğü: Hazine ve maliye işlemleri, vezne ve tahsilat hizmetlerinin yürütüldüğü birim.\r\n\r\n2. Diğer Temel Kamu Kurumları:\r\n\r\nÇermik İlçe Nüfus Müdürlüğü: Kimlik, pasaport, sürücü belgesi, adres kayıt ve doğum/ölüm/evlilik tescil işlemlerinin yapıldığı en yoğun kurumlardan biridir.\r\n\r\nÇermik Adliyesi: Hükümet Konağı yapısı içerisinde (veya entegre bitişik nizamda) hizmet verir. Adli makamlar, savcılık ve mahkeme salonları burada bulunur.\r\n\r\nİlçe Seçim Kurulu Başkanlığı: Seçim dönemlerinde ve seçmen kütüğü işlemlerinde aktif olan birimdir (Adliye ile koordineli çalışır).\r\n\r\n(Not: İlçe Emniyet Müdürlüğü, İlçe Milli Eğitim Müdürlüğü veya İlçe Tarım Müdürlüğü gibi bazı kurumlar, kapasite veya görev alanları gereği ilçenin farklı noktalarındaki kendi müstakil binalarında hizmet verebilmektedir. Kaymakamlığa bağlı olsalar da fiziki olarak her zaman Hükümet Konağı\'nın içinde yer almazlar.)','2026-02-23 12:57:44');
/*!40000 ALTER TABLE `genel_bilgiler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hospitals`
--

DROP TABLE IF EXISTS `hospitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `panorama_360` varchar(255) DEFAULT NULL,
  `image_main` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hospitals`
--

LOCK TABLES `hospitals` WRITE;
/*!40000 ALTER TABLE `hospitals` DISABLE KEYS */;
INSERT INTO `hospitals` VALUES (1,'Çermik Devlet Hastanesi','Genel Kapasite ve Yeni Hizmet Binası\r\nYeni Hastane Binası: Hastane, Ağustos 2024\'te 18.810 metrekarelik kapalı alana sahip yeni ve modern binasına taşınarak 50 yatak kapasitesiyle hizmet vermeye başlamıştır. Eski binasından taşındığı için güncel adresi değişmiştir.\r\n\r\nKapasite Detayları: 25 poliklinik odası, 3 ameliyathane, 20\'si tek kişilik ve 15\'i çift kişilik olmak üzere toplam 35 hasta odası ile hasta kabul etmektedir.\r\n\r\nYoğun Bakım ve Üniteler: 4 yataklı yenidoğan yoğun bakım, 6 yataklı genel yoğun bakım, 7 cihazlı hemodiyaliz ünitesi ve 10 hasta kapasiteli fizik tedavi ünitesi mevcuttur.\r\n\r\n2026 Yılındaki Önemli Gelişmeler\r\nAnne Dostu Hastane Unvanı (Ocak 2026): Hastane, Sağlık Bakanlığı tarafından yapılan değerlendirmeler sonucunda anne ve bebek sağlığını önceleyen standartları başarıyla karşılayarak \"Anne Dostu Hastane\" unvanını aldı. Çermik Devlet Hastanesi, 2026 yılında Türkiye genelinde bu unvanı almaya hak kazanan ilk sağlık tesisi oldu.\r\n\r\nBaşhekim Değişikliği (Şubat 2026): Şubat 2026 başlarında hastane başhekimliğine Umut Karagöz\'ün yerine Fırat Türken atanmıştır.\r\n\r\nÖne Çıkan Hizmetler ve Donanımlar\r\nHastane, yenidoğan sarılığı tedavisini hızlandıran tünel fototerapi cihazları ve mide/bağırsak hastalıklarının teşhisi için kurulan endoskopi/kolonoskopi üniteleriyle bölge halkına gelişmiş tıbbi imkanlar sunmaktadır.\r\n\r\n5 yatak kapasiteli evde sağlık hizmeti birimi de aktif olarak bölge halkına destek vermektedir.\r\n\r\nGüncel İletişim ve Adres Bilgileri\r\n(Önemli Not: Eski \"Siverek Yolu\" adresi yeni binayla birlikte değişmiştir.)\r\n\r\nAdres: Tepe Mahallesi, Diyarbakır Caddesi No:128 Çermik / Diyarbakır\r\n\r\nTelefon: 0 (412) 606 05 74\r\n\r\nE-posta: dbkrcermikdh.bsh@saglik.gov.tr',38.14381625,39.47553181,'','','2026-02-23 08:43:00'),(2,'Çermik 1 Nolu Sağlık Ocağı','',38.13589309,39.45398365,'','','2026-02-23 08:51:44'),(3,'Çermik 2 Nolu Sağlık Ocağı','',38.13757291,39.46782466,'','','2026-02-23 08:51:55'),(4,'Çermik 3 Nolu Sağlık Ocağı','',38.13485516,39.44729905,'','','2026-02-24 09:24:23');
/*!40000 ALTER TABLE `hospitals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `municipal_guide`
--

DROP TABLE IF EXISTS `municipal_guide`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `municipal_guide` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `municipal_guide_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `municipal_guide` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `municipal_guide`
--

LOCK TABLES `municipal_guide` WRITE;
/*!40000 ALTER TABLE `municipal_guide` DISABLE KEYS */;
INSERT INTO `municipal_guide` VALUES (1,NULL,'Evlendirme İşleri','Evlilik başvurusunda gerekli belgeler;\r\n\r\n1. Resimli Nüfus cüzdanları aslı ve fotokopileri.  10 yılı geçen nüfus cüzdanları geçersizdir.Kabul edilmeyecektir.\r\n\r\n2. Çiftlerden birinin  Belediye sınırları içerisinde ikamet etmesi zorunludur.\r\n\r\n3. Son 6 ayda çekilmiş 4er adet vesikalık fotoğraf. FOTOKOPİ RESİMLER GEÇERSİZDİR.\r\n\r\n4. 16 yaşını doldurup , 17 sinden gün alan adaylar Aile Mahkemesinden evlenme izin belgesi 17 yaşını bitirip 18 yaşından gün alan adaylar noterden alınmış anne-baba muvafakatnamesi ile evlenebilirler.(Anne veya babadan biri ölmüş ise ölümü nüfus kaydı ile belgelenecektir).beraber gelmeleri zorunludur.\r\n\r\n5. Kayıt ve müracaatlar sabah 08.30-16.00 saatleri arasında yapılır.Müracaat sonrasında,hazırlanacak evraklar bir sonraki gün başvurunun yapıldığı ilgilidentemin edilir.Temin edilen evraklarda istenilen hususlar yerine getirilerek Nikah saati için gerekli başvuru Yazı İşleri Müdürlüğü\'ne yapılır.\r\n\r\n6.Belediye Meclisinin ...... Tarih ve ....... Sayılı kararı gereği:\r\n\r\nNikah akdi ücreti:\r\nBelediye de yapılan nikah akdi ücreti:\r\nCüzdan Bedeli    ............TL\r\n \r\n\r\nMal ayrılığı talebi olanların, noterden yaptırmış oldukları MAL AYRILIĞI SÖZLEŞMESİ müracaat dosyasına koyulur.\r\n \r\n\r\nKızlık soyadını kullanmak isteyenler, müracaat anında ilgili memura talebini bildireceklerdir.\r\n\r\nMÜRACAAT EVRAKLARI, ALINDIĞI TARİHTEN İTİBAREN 6 AY SÜRE İLE GEÇERLİDİR.\r\n\r\nYabancı Uyruklu Vatandaşlar için alınacak belgeler hakkında Yazı İşleri Müdürlüğü 543 23 20 (216)  no\'lu telefonlardan bilgi alınabilir.\r\n\r\nİstenilen evraklar tamamlandıktan sonra gün için müracaat edilecektir.Eksik evrakla müracaat yasa gereği kabul edilmemektedir.\r\n\r\nEVLİLİK BAŞVURU EVRAKLARI DÜZENLENDİĞİ TARİHTEN İTİBAREN 6 (ALTI AY) SÜRE  İLE GEÇERLİ OLDUĞUNDAN  İSTENİLEN  NİKAH GÜNÜ BU TARİH ARALIĞINI  GEÇMEYECEKTİR.\r\n\r\nNikah şahitlerinin evlenecek kişileri tanıması ve T.C. Kimlik numaralarının bildirilmesi zorunludur.','',1,'2026-02-24 08:44:35'),(2,NULL,'İmar İşleri','MADDE 1 – (1) Bu Yönetmeliğin amacı; Ankara Büyükşehir Belediyesi sınırları içerisinde plan, fen, sağlık ve sürdürülebilir çevre şartlarına uygun yapı ve yapılaşma ile projelendirmeye ve denetime ilişkin usul ve esasları belirlemektir.\r\n\r\nKapsam\r\n\r\nMADDE 2 – (1) Bu Yönetmelik, uygulama imar planı bulunan alanları kapsar.\r\n\r\nDayanak\r\n\r\nMADDE 3 – (Değişik:RG-4/3/2022-31768)\r\n\r\n(1) Bu Yönetmelik, 3/5/1985 tarihli ve 3194 sayılı İmar Kanunu, 10/7/2018 tarihli ve 30474 sayılı Resmî Gazete’de yayımlanan 1 sayılı Cumhurbaşkanlığı Teşkilatı Hakkında Cumhurbaşkanlığı Kararnamesinin altıncı kısmının dördüncü bölüm hükümleri ile 3/7/2017 tarihli ve 30113 sayılı Resmî Gazete’de yayımlanan Planlı Alanlar İmar Yönetmeliğinin 69 uncu maddesi hükümlerine dayanılarak hazırlanmıştır.\r\n\r\nTanımlar\r\n\r\nMADDE 4 – (Değişik:RG-4/3/2022-31768)\r\n\r\n(1) Planlı Alanlar İmar Yönetmeliğinin 4 üncü maddesinin birinci fıkrasında yer alan tanımların yanı sıra bu Yönetmelikte geçen;\r\n\r\na) Belediye hizmet alanı: Belediyelerin görev ve sorumlulukları kapsamındaki hizmetlerinin götürülebilmesi için gerekli itfaiye, acil yardım ve kurtarma, ulaşıma yönelik transfer istasyonu, araç ve makine parkı, bakım ve ikmal istasyonu, garaj ve triyaj alanları, belediye depoları, asfalt tesisi, atık işleme tesisi, zabıta birimleri, mezbaha, ekmek üretim tesisi, pazar yeri, idari, sosyal ve kültürel merkez gibi mahallî müşterek nitelikteki ihtiyaçları karşılamak üzere kurulan tesisler ile sermayesinin yarıdan fazlası belediyeye ait olan şirketlerin sahip olduğu tesislerin yapılabileceği alanları,\r\n\r\nb) Bölge kat nizamı planlı alanlar/bölgeler: Herman Jansen ve Yücel Uybadin imar planları ile ıslah imar planlarında; yapılaşma ile ilgili koşulların genellikle kat sayısı ve cephe hattı ile belirlendiği, bunun dışında diğer yapılanma şartlarında bu Yönetmelik hükümlerinin geçerli olduğu (klasik parselli) alanları/bölgeleri,\r\n\r\nc) Depolama alanları: İmar planlarında her türlü açık ve kapalı depolama alanları, yükleme-boşaltma-nakliye tesisleri ile depolama tesislerine ayrılmış alanları,\r\n\r\nç) Islah imar planı alanları: 2805, 2981, 3290, 3366 sayılı Kanunlara göre ıslah imar planı yapılmış alanları,\r\n\r\nd) Kentsel servis alanları: 1/10/2017 tarihinden önceki mevcut İmar planlarında kentin tanımlanmış yön ve bölgelerinde, bölgesel ihtiyacı karşılamak üzere belirlenmiş, büro, iş hanı, gazino, lokanta, çarşı, çok katlı mağaza, banka, otel, sinema, tiyatro gibi sosyal kültürel tesisler, yönetimle ilgili tesisler, ilgili kurumlardan uygun görüş alınmak kaydıyla özel/resmi eğitim, sağlık ve benzeri tesislerin yapılabildiği alanları,\r\n\r\ne) Kentsel tasarım projesi: Doğal, tarihi, kültürel, sosyal ve ekonomik özellikler ile arazi yapısı dikkate alınarak, tasarım amacına göre kütle ve yapılanma düzeni veya açık alan düzenlemelerini içeren; taşıt ulaşımı, otopark ve servis ilişkileri ve yaya dolaşım ilişkilerini kuran; yapı, sokak, doku, açık ve yeşil alanların ilişkisini ve kentsel mobilya detaylarını gösteren; altyapı unsurlarını bütüncül bir yaklaşımla disiplinler arası olarak ele alan; imge, anlam ve kimlik özelliklerini ifade eden; tasarım ilke ve araçlarını içeren uygun ölçekteki projeyi,\r\n\r\nf) Küçük sanayi alanı: Şehirde yaşayanların günlük bakım, tamir, servis ve küçük ölçekli imalat ihtiyaçlarının karşılanabileceği, patlayıcı, parlayıcı ve yanıcı maddeler içermeyen ve çevre sağlığı yönünden tehlike oluşturmayan atölye, imalathane ile depoların yerleşmelere yakın veya kolay ulaşılabilir yerlerinde yapılabildiği alanları,\r\n\r\ng) Sosyal ve kültürel tesisler alanı: Toplumun kültürel faaliyetlerine yönelik hizmet vermek üzere kütüphane, halk eğitim merkezi, sergi salonu, sanat galerisi, müze, konser, konferans, kongre salonları, sinema, tiyatro ve opera gibi fonksiyonlar ile sosyal yaşamın niteliğini ve düzeyini artırmak amacı ile toplumun faydalanacağı kreş, kurs, yurt, çocuk yuvası, yetiştirme yurdu, yaşlı ve engelli bakımevi, rehabilitasyon merkezi, toplum merkezi, şefkat evleri gibi fonksiyonlarda hizmet vermek üzere ayrılan kamu veya özel mülkiyetteki alanları,\r\n\r\nğ) Teknik altyapı alanları: Kamu veya özel sektör tarafından yapılacak elektrik, petrol ve doğalgaz iletim hatları, reglaj istasyonları, su deposu, içme ve kullanma suyu ile yer altı ve yer üstü her türlü arıtma, kanalizasyon, atık işleme tesisleri, trafo, enerji dağıtım merkezi, her türlü enerji, ulaştırma, haberleşme gibi servislerin temini için yapılan tesisler ile açık veya kapalı otopark kullanışlarının yapılabileceği alanları,\r\n\r\nh) Toplu işyerleri: Büyük alan kullanımı gerektiren ticari işletmeler, inşaat malzemesi, oto galeri, tarımsal üretim pazarlama, nakliyat ambarı, toptancı hali, toptan ticaret, pazarlama ve depolama alanları, tır ve kamyon parkı ve benzeri tesisler ile çevre sağlığı yönünden gerekli tedbirler alınmak kaydıyla mermer, hurda, teneke, kağıt, plastik gibi maddelerin organize bir şekilde depolanması ve işlenmesine yönelik faaliyetler ile bunlara ilişkin sosyal ve teknik altyapı tesislerinin de yer aldığı alanları,\r\n\r\nifade eder.','',2,'2026-02-24 08:45:44'),(3,NULL,'Eğitim','Çermik Belediyesi Etüt Merkezi 7 Gün boyunca Saat 08:00 ile 22:00 saatleri arasında öğrencilerimize hizmet vermektedir.','uploads/guide/1772005739_etüt.jpeg',3,'2026-02-25 07:48:59'),(4,NULL,'Vergi İşlemleri','Emlak vergisi, Türkiye sınırları içinde bulunan bina, arsa ve araziler üzerinden alınan vergi türüdür.\r\n\r\nEmlak Vergisi Kanununda yer alan bina ve arazi vergisinin kapsamı, mükellefi, mükellefiyetin başlaması ve sona ermesi, emlak vergisi bildirimi, emlak vergisinin matrah ve oranı, verginin ödeme zamanı, indirimli bina vergisi oranı uygulaması gibi konular emlak vergisinin temel unsurlarıdır.\r\n\r\nEmlak vergisi gayrimenkulün kayıtlı olduğu ilgili belediyeye ödenecek olup birinci taksiti mart, nisan ve mayıs aylarında, ikinci taksiti kasım ayında olmak üzere iki eşit taksitte ödenebilmektedir.\r\n\r\nBina Vergisinin Kapsamı Nedir?\r\n\r\nTürkiye sınırları içinde bulunan binalar, Emlak Vergisi Kanunu hükümlerine göre bina vergisine tabidir.\r\n\r\nArazi Vergisinin Kapsamı Nedir?\r\n\r\nTürkiye sınırları içinde bulunan arazi ve arsalar, Emlak Vergisi Kanunu hükümlerine göre arazi vergisine tabidir.\r\n\r\nEmlak Vergisinin Kapsamına Giren Bina, Arsa ve Arazi Tabirlerinin Kapsamı Nedir?\r\n\r\nBina, yapıldığı madde ne olursa olsun, gerek karada gerek su üzerindeki sabit inşaatların hepsini kapsar. Yüzer havuzlar, sair yüzer yapılar, çadırlar ve nakil vasıtalarına takılıp çekilebilen seyyar evler ve benzerleri bina sayılmaz.\r\n\r\nBelediye sınırları içinde belediyece parsellenmiş arazi arsa sayılır.\r\n\r\nArazi, sınırları yeterli vasıtalarla belirlenmiş, yatay ve düşey sınırları bulunan yeryüzü parçasıdır. Aksine hüküm olmadıkça Emlak Vergisi Kanununda yer alan arazi tabiri arsaları da kapsar.\r\n\r\nBina ve Arazi Vergisinin Mükellefi Kimlerdir?\r\n\r\nBina veya arazinin maliki, varsa intifa hakkı sahibi, her ikisi de yoksa bina veya araziye malik gibi tasarruf edenlerdir. Bir bina veya araziye paylı mülkiyet halinde malik olanlar hisseleri oranında mükelleftirler. Müşterek mülkiyette ise malikler vergiden müteselsilen sorumludurlar.\r\n\r\nArazi Vergisinde İstisnalar Nelerdir?\r\n\r\nMükelleflerin bir belediye ve bu belediyenin mücavir alan sınırları içinde bulunan arazisinin (arsalar hariç) toplam vergi değerinin 10.000 Türk lirası arazi vergisinden istisnadır.\r\n\r\nBu hükmün uygulamasında mükellef ile eş ve velayet altındaki çocuklara ait arazi değerleri toplu olarak dikkate alınır.\r\n\r\nİstisna, hisseli arazide mükelleflerin hisse miktarları ayrı ayrı dikkate alınmak suretiyle uygulanır.\r\n\r\nBina ve Arazi Vergisinde Mükellefiyet Ne Zaman Başlar?\r\n\r\nEmlak Vergisi Kanununun 33 üncü maddenin (1) ilâ (7) numaralı fıkralarında yazılı vergi değerini tadil eden sebeplerin doğması halinde bu değişikliklerin meydana geldiği, aynı maddenin (8) numaralı fıkrasında yazılı halde ise bu duruma bağlı olarak takdir işleminin yapıldığı tarihi,\r\nDört yılda bir yapılan takdir işlemlerinde takdir işleminin yapıldığı tarihi,\r\nMuafiyetin sukut ettiği tarihi,\r\ntakip eden bütçe yılından itibaren başlar.\r\n\r\nÖrnek: Gayrimenkul 26/7/2025 tarihinde satın alınmış ise 31/12/2025 tarihine kadar, 15/11/2025 tarihinde satın alınmış ise satın alma tarihinden itibaren üç ay içinde satın alan tarafından ilgili belediyeye emlak vergisi bildirimi verilmesi, her iki durumda da emlak vergisi mükellefiyetinin 2026 yılından itibaren başlaması gerekmektedir.\r\n\r\nBina ve Arazi Vergisinde Mükellefiyet Ne Zaman Sona Erer?\r\n\r\nYanan, yıkılan, tamamen kullanılmaz hale gelen veya vergiye tabi iken muaflık şartlarını kazanan binalardan dolayı mükellefiyet, bu olayların meydana geldiği tarihi takip eden taksitten itibaren sona erer.\r\n\r\nOturulması ve kullanılması kanunların verdiği yetkiye dayanılarak yasak edilen binaların vergileri, mükelleflerce keyfiyetin vergi dairesine bildirilmesi veya vergi dairesince re’sen tespit edilmesi üzerine, bu olayların meydana geldiği tarihlerden sonra gelen taksitlerden itibaren, bu hallerin devam ettiği sürece alınmaz.\r\n\r\nArazi vergisinde mükellefiyet, vergiye tabi iken muaflık şartlarını kazanan araziden dolayı mükellefiyet, bu olayın meydana geldiği tarihi takip eden taksitten itibaren sona erer.\r\n\r\nKanunların verdiği yetkiye dayanılarak tasarrufu yasak edilen arazinin vergisi, mükelleflerce keyfiyetin vergi dairesine bildirilmesi veya vergi dairesince re’sen tespit edilmesi üzerine, yasaklama tarihini izleyen taksitlerden itibaren bu hallerin devam ettiği sürece alınmaz.\r\n\r\nDeprem, su basması, yangın gibi tabii afetler sebebiyle yanan, yıkılan binaların arsalarına ait vergiler, bu olayların vukua geldiği tarihleri takip eden bütçe yılından itibaren iki yıl süre ile alınmaz.\r\n\r\nÜzerine bina yapılan arsaya ilişkin arazi vergisi mükellefiyeti, inşaatın bittiği yılı takip eden bütçe yılından itibaren sona erer.\r\n\r\nBina ve Arazi Vergisinin Matrahı Nedir?\r\n\r\nBina ve arazi vergisinin matrahı, bina ve arazi için Emlak Vergisi Kanunu hükümlerine göre tespit olunan vergi değeridir. Binaya sabit bir şekilde yerleştirilmiş olan her türlü makine ve tesislere ait değerler vergi matrahına alınmaz.\r\n\r\nEmlak Vergisinde Vergi Değeri (Matrah) Nedir?\r\n\r\nVergi değeri;\r\n\r\na) Arsa ve araziler için, Vergi Usul Kanununun asgari ölçüde birim değer tespitine ilişkin hükümlerine göre takdir komisyonlarınca arsalar için her mahalle ve arsa sayılacak parsellenmemiş arazide her köy için cadde, sokak veya değer bakımından farklı bölgeler (turistik bölgelerdeki cadde, sokak veya değer bakımından farklı olanlar ilgili valilerce tespit edilecek pafta, ada veya parseller), arazide her il veya ilçe için arazinin cinsi (kıraç, taban, sulak) itibarıyla takdir olunan birim değerlere göre,\r\n\r\nb) Binalar için, Bakanlığımız ve Çevre Şehircilik ve İklim Değişikliği Bakanlığınca müştereken tespit ve ilân edilecek bina metrekare normal inşaat maliyetleri ile (a) bendinde belirtilen esaslara göre bulunacak arsa veya arsa payı değeri esas alınarak Emlak Vergisi Kanununa göre hazırlanmış bulunan Tüzük hükümlerinden yararlanılmak suretiyle,\r\n\r\nhesaplanan bedeldir.\r\n\r\nVergi değeri, mükellefiyetin başlangıç yılını takip eden yıldan itibaren her yıl, bir önceki yıl vergi değerinin Vergi Usul Kanunu hükümleri uyarınca aynı yıl için tespit edilen yeniden değerleme oranının yarısı nispetinde artırılması suretiyle bulunur.\r\n\r\nEmlak Vergisi Kanununa göre mükellefiyet tesisi gereken hallerde vergi değerinin hesaplanmasında, Vergi Usul Kanununa göre belirlenen arsa ve arazi birim değerleri, takdir işleminin yapıldığı yılı takip eden ikinci yıldan başlamak suretiyle her yıl, bir önceki yıl birim değerinin, Vergi Usul Kanunu hükümleri uyarınca aynı yıl için tespit edilen yeniden değerleme oranının yarısı nispetinde artırılması suretiyle dikkate alınır.\r\n\r\nEmlak Vergisinde Tarh ve Tahakkuk İşlemi Nasıl Yapılır?\r\n\r\nBina ve arazi vergisi, ilgili belediye tarafından;\r\n\r\nDört yılda bir defa olmak üzere takdir işlemlerinin yapıldığı yılı takip eden bütçe yılının ocak ve şubat aylarında,\r\nKanunun 33 üncü maddesinin (1) ilâ (7) numaralı fıkralarında yazılı vergi değerini tadil eden sebeplerle bildirim verilmesi icap eden hallerde, vergi değerini tadil eden sebeplerin meydana geldiği bütçe yılını takip eden yılın ocak ayı içinde, vergi değerini tadil eden sebep bütçe yılının son üç ayı içinde vuku bulmuş ve bildirim, vergi değerini tadil eden sebebin meydana geldiği bütçe yılını takip eden yılda verilmiş ise bildirimin verildiği tarihte,\r\nKanunun  33 üncü maddesinin (8) numaralı fıkrasında yazılı hallerde, takdir işlemlerinin yapıldığı bütçe yılını takip eden yılın ocak ve şubat aylarında,\r\nEmlak Vergisi Kanununa göre hesaplanan vergi değeri esas alınarak yıllık olarak tarh olunur.\r\n\r\nBildirim posta ile gönderilmiş ise vergi, bildirim verme süresinin son gününü takip eden yedi gün içinde tarh olunur. Bu suretle tarh olunan vergiler, tarh edilen tarihte tahakkuk etmiş sayılır ve mükellefe bir yazı ile bildirilir.\r\n\r\nYapılan tarh ve tahakkuku takip eden yıllarda, Emlak Vergisi Kanununa göre tespit edilen vergi değeri üzerinden hesaplanan bina ve arazi vergisi, her bütçe yılının başından itibaren o yıl için tahakkuk etmiş sayılır.\r\n\r\nBir il veya ilçe sınırları içerisinde birden fazla belediye olması halinde, belediye ve mücavir alan sınırları dışında bulunan bina ve araziye ait emlak vergisini tarha yetkili olacak belediye, ilgili valiler tarafından belirlenir.\r\n\r\nBina ve Arazi Vergisi Oranları Nedir?\r\n\r\nEmlak Vergisi Kanununa göre;\r\n\r\nBina vergisinin oranı meskenlerde binde 1, diğer binalarda binde 2,\r\nArazide binde 1, arsalarda ise binde 3’tür.\r\nBina, arsa ve arazilere ilişkin vergi oranları büyükşehir belediye sınırları ve mücavir alanlar içinde %100 artırımlı uygulanır.\r\n\r\n \r\n\r\nKonut\r\n\r\nİşyeri\r\n\r\nArsa\r\n\r\nArazi\r\n\r\nBüyükşehir belediyesi dışındaki yerlerde\r\n\r\nBinde 1\r\n\r\nBinde 2\r\n\r\nBinde 3\r\n\r\nBinde 1\r\n\r\nBüyükşehir belediye sınırları ve mücavir alanlarda\r\n\r\nBinde 2\r\n\r\nBinde 4\r\n\r\nBinde 6\r\n\r\nBinde 2\r\n\r\nYeni inşa edilen bina veya binaların vergisi, arsasının (veya arsa payının) vergisinden az olamaz. Bu kriter/şart, binaların inşalarının sona erdiği yılı takip eden bütçe yılından itibaren dört yıl uygulanır.','',4,'2026-02-25 07:50:37');
/*!40000 ALTER TABLE `municipal_guide` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacies`
--

DROP TABLE IF EXISTS `pharmacies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `is_on_duty` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacies`
--

LOCK TABLES `pharmacies` WRITE;
/*!40000 ALTER TABLE `pharmacies` DISABLE KEYS */;
INSERT INTO `pharmacies` VALUES (1,'Işık Eczanesi','05373972500','Diyarbakır Cad. No:85/B Çermik',38.13610000,39.44850000,0,'2026-02-23 12:50:33'),(2,'Saadet Eczanesi','05369542022','Tepe Mahallesi, Abdullah Can Apt. No:47/a Çermik',38.13880000,39.44520000,1,'2026-02-24 13:30:46');
/*!40000 ALTER TABLE `pharmacies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `places`
--

DROP TABLE IF EXISTS `places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `places` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` enum('Historical','Nature','Park','HotSpring','ParkAndGarden') NOT NULL,
  `description` text,
  `hastaliklar` text,
  `lat` varchar(50) DEFAULT NULL,
  `lng` varchar(50) DEFAULT NULL,
  `image_main` varchar(255) DEFAULT NULL,
  `image_gallery` text,
  `panorama_360` varchar(255) DEFAULT NULL,
  `ai_context` text,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `popular_score` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `places`
--

LOCK TABLES `places` WRITE;
/*!40000 ALTER TABLE `places` DISABLE KEYS */;
INSERT INTO `places` VALUES (14,'Gelincik Dağı','Nature','Çermik\'in 4 km kuzeybatısında yer alan Gelincik Dağı, yaklaşık 1500 metre yüksekliğindedir. Uzaktan bakıldığında insan dizisini andıran kaya oluşumlarıyla \'Güneydoğu\'nun Kapadokya\'sı\' olarak anılır. Paleolitik dönem izleri taşır.\r\nGöz kamaştırıcı güzelliğiyle insanları büyüleyen bu doğa harikası dağ, izleyenleri adeta hayal alemine sürüklemektedir. Karstik yeryüzü şekillerinden dolayı yağmur sularıyla eriyen ve bu erime sonucu oluşmuş kaya kütleleri ülkemizin Kapadokya yöresindeki peri bacalarını andırmaktadır. Uzaktan bakıldığında insan dizisi gibi görünen bu dağın halk arasında bir de efsanesi bulunmaktadır. \r\n	Gelincik Dağı Efsanesi: Vaktiyle bu dağın üzerinden bir gelin alayı geçmiş. Düğünde bulunanlardan birinin çocuğu altını kirletmiş. Çocuğun annesi bez aramış fakat bulamamış. Çaresiz kalan anne çocuğun ağlamasına dayanamayarak yanında bulunan yufka ekmeği ile altını temizlemiş. Bu olay sonucunda düğün alayının tamamı Tanrının gazabına uğrayarak taş kesilmiştir. Bu efsaneye dayanılarak bu dağa “Gelincik Dağı” denilmektedir.\r\n\r\n','','38.15500000','39.42500000','uploads/places/default.jpg','','','Sen bir doğa rehberisin. Gelincik Dağı\'nın efsanelerini (Gelin Alayı efsanesi gibi) ve kaya oluşumlarını anlatmalısın. Trekking rotaları hakkında bilgi ver.\r\nGöz kamaştırıcı güzelliğiyle insanları büyüleyen bu doğa harikası dağ, izleyenleri adeta hayal alemine sürüklemektedir. Karstik yeryüzü şekillerinden dolayı yağmur sularıyla eriyen ve bu erime sonucu oluşmuş kaya kütleleri ülkemizin Kapadokya yöresindeki peri bacalarını andırmaktadır. Uzaktan bakıldığında insan dizisi gibi görünen bu dağın halk arasında bir de efsanesi bulunmaktadır. \r\n	Gelincik Dağı Efsanesi: Vaktiyle bu dağın üzerinden bir gelin alayı geçmiş. Düğünde bulunanlardan birinin çocuğu altını kirletmiş. Çocuğun annesi bez aramış fakat bulamamış. Çaresiz kalan anne çocuğun ağlamasına dayanamayarak yanında bulunan yufka ekmeği ile altını temizlemiş. Bu olay sonucunda düğün alayının tamamı Tanrının gazabına uğrayarak taş kesilmiştir. Bu efsaneye dayanılarak bu dağa “Gelincik Dağı” denilmektedir.\r\n\r\n',NULL,0,'2026-02-22 09:52:36'),(15,'Şeyhandede Şelalesi','Nature','Çermik\'in Şeyhandede köyünde yer alan bu doğa harikası, yaklaşık 30 metre yükseklikten dökülür. Tabiat Parkı statüsündedir ve derin bir vadi içerisinde Akdeniz kanyonlarını andıran bir yapıya sahiptir.\r\nŞeyhandede Şelalesi: İsmini ilçeye 33 km uzaklıkta bulunan Şeyhandede köyünden almaktadır. Şeyhandede Köprüsü’nden güneye doğru 3 km. uzaklıktadır. Şelalenin yüksekliği 20 m. civarıdır. Doğa harikası olan bu şelalenin çevresi yaz aylarında piknik yapanların vazgeçilmez adresidir.','','38.0268348037483','39.28476877293375','uploads/places/default.jpg','','','Serin ve huzurlu bir ses tonu kullan. Şelalenin döküldüğü gölette yüzülebileceğini ve çevredeki mağaraları vurgula.\r\nŞeyhandede Şelalesi: İsmini ilçeye 33 km uzaklıkta bulunan Şeyhandede köyünden almaktadır. Şeyhandede Köprüsü’nden güneye doğru 3 km. uzaklıktadır. Şelalenin yüksekliği 20 m. civarıdır. Doğa harikası olan bu şelalenin çevresi yaz aylarında piknik yapanların vazgeçilmez adresidir.',NULL,0,'2026-02-22 09:52:36'),(16,'Sinek Çayı Şelalesi','Nature','İlçemizin hemen kuzeyinde bir yay çizerek, kıvrıla kıvrıla Fırat Nehri’ne dökülen Sinek Çayı üzerinde yer alan Sinek Çayı Şelalesi Çermik-Çüngüş yolunun 10. Kilometresinde sol tarafa ayrılan stabilize yoldan 1,5 km. sonra varılan 2. Sinek Köprüsü’nün memba akarında bulunmaktadır. Özellikle yaz aylarında halkımızın piknik ve dinlenme amacıyla uğrak yerlerinden biri olan Sinek Çayı Şelalesi bahar aylarında yüksek debisinden dolayı rafting yapmaya da uygundur. ','','38.17281522451144','39.45498264862447','uploads/places/default.jpg','','','Yerel bir dost gibi konuş. Piknik alanları ve en iyi yüzme noktalarını öner.',NULL,0,'2026-02-22 09:52:36'),(17,'Gaban Kral Yolu','Nature','Pers Kral Yolu\'nun bir parçası olan Gaban Antik Yolu, iri taşlarla kaplı stratejik bir güzergahtır. Dağ yamacını takip eden bu yolda teraslamalar ve geniş merdivenler bulunur.\r\nGaban Kral Yolu: İlçe merkezinin kuzeybatı çıkışında Sinek Köprüsünün devamında Gelincik Dağı eteklerinde yer alan “Gaban Kral Yolu” tarihi ticaret yollarının bir kolu olarak düşünülmektedir. Yol iri taşlarla kaplanarak kimi yerlerde ise mevcut kayalar düzeltilerek yapılmıştır. Dağın yamacını takip ederek devam eden yolda eğimin arttığı yerlerde teraslama yapılarak geniş merdivenler oluşturulmuştur. Günümüzde de halen kullanılmaktadır. ','','38.14232217617323',' 39.44325487969017','uploads/places/default.jpg','','','Tarihçi bir kaşif gibi davran. Antik ticaret yollarının öneminden ve yolun yapım tekniğinden bahset.\r\nPers Kral Yolu\'nun bir parçası olan Gaban Antik Yolu, iri taşlarla kaplı stratejik bir güzergahtır. Dağ yamacını takip eden bu yolda teraslamalar ve geniş merdivenler bulunur.\r\nGaban Kral Yolu: İlçe merkezinin kuzeybatı çıkışında Sinek Köprüsünün devamında Gelincik Dağı eteklerinde yer alan “Gaban Kral Yolu” tarihi ticaret yollarının bir kolu olarak düşünülmektedir. Yol iri taşlarla kaplanarak kimi yerlerde ise mevcut kayalar düzeltilerek yapılmıştır. Dağın yamacını takip ederek devam eden yolda eğimin arttığı yerlerde teraslama yapılarak geniş merdivenler oluşturulmuştur. Günümüzde de halen kullanılmaktadır. ',NULL,0,'2026-02-22 09:52:36'),(18,'Sinek Çayı ve Kaynağı ve Kayaaltı Sığınağı','Nature','Çermik\'in kuzeyinden doğan ve bölge köylerine hayat veren su kaynağıdır. Çevresindeki mağaralar ve ilginç kaya oluşumlarıyla sporcuların ve doğaseverlerin odak noktasıdır.\r\nÇermik’in 7.5 km kuzeyinde yer alan Sinek Çayı kaynağı yakınında keşfedilen bu sığınakta bir sürek avını yansıtan insan ve hayvan resimlerinin Anadolu’nun bilinen en eski resimleri olduğu tespit edilmiştir. \r\nİstanbul Üniversitesi Avrasya Arkeoloji Enstitüsü Müdürü Prof. Dr. Oktay BELLİ’nin haziran 2005’te ortaya çıkardığı resimler 1.60x2.40 metre büyüklüğündeki bir alanda yer almaktadır. Resimlerde betimlenen sürek avını düzenleyen 11 yaylı avcının, günümüzden 15000 ile 13000 yıl öncesinde (Üst Paleolitik dönem ile Mezolitik Çağ) yaşamış olduğu tahmin ediliyor.  Prof. Dr. BELLİ, 16 hayvan figürü ile 11 insan figürünün ortaya çıktığı kayaaltı sığınağının bir bölümünün kalker tabakası ile kaplı olduğunu, bu tabakanın altında başka resimler de bulunduğunu belirtmektedir. \r\nArkeozoolog Doç. Dr. Vedat ONAR, hayvanlardan ondördünün dağ keçisi, birinin oğlak, diğerinin ise kedigillerden bir hayvan olabileceğini düşünüyor. Kaya yüzeyine yapılan resimlerin ana konusunun çeşitli av hayvanları ile bunları yay ve ok ile avlayan insan figürleri oluşturuyor. Prof. Dr. BELLİ, Anadoluda bugüne kadar yay ve okla dağ keçisi avlayanları betimleyen benzer bir sürek avı sahnesine rastlanmadığına dikkat çekerek, “burada resmedilen 11 avcı şimdilik Anadolu’nun bilinen en eski avcıları” diyor. \r\n\r\nSığınaktaki Figürlerin Özellikleri: \r\n\r\nHayvan figürlerinin yapımında iki farklı yöntem uygulanmıştır. Bunlardan ilkinde figürlerin gövdeleri tümüyle dövme tekniği ile oyulmuş, ikincisinde ise figürlerin gövdelerinin dış konturları kalın ve derin bir çizgi ile belirtilmiştir.\r\nSinek Çayı Kayaaltı Sığınağındaki sürek avını yansıtan resimlerin  en yakın benzerine, İspanya’nın Castellon bölgesinde yedi yaylı avcının yaban domuzu avını gösteren- Mezolitik Çağ’a tarihlenen- sahnede rastlandı. \r\nKayaaltı Sığınağının bir kült merkezi olarak kullanan avcıların, hayvanları gövdelerine ok saplanmış olarak çizmesi, avın başarılı gerçekleşmesi için büyüye gereksinim duyduklarının bir göstergesi olarak nitelendirilmektedir.\r\nİnsan figürlerinin uzunluğu,9-14 cm. arasında değişiyor. Dağ keçilerinin boyu 18-25 cm. genişliği ise 30-36 cm. arasındadır.\r\n','','38.17699223173219','39.447719806934934','uploads/places/default.jpg','','','Suyun önemini ve saflığını vurgula. Kaynaktan çıkan suyun tarımda nasıl kullanıldığını anlat.\r\nÇermik’in 7.5 km kuzeyinde yer alan Sinek Çayı kaynağı yakınında keşfedilen bu sığınakta bir sürek avını yansıtan insan ve hayvan resimlerinin Anadolu’nun bilinen en eski resimleri olduğu tespit edilmiştir. \r\nİstanbul Üniversitesi Avrasya Arkeoloji Enstitüsü Müdürü Prof. Dr. Oktay BELLİ’nin haziran 2005’te ortaya çıkardığı resimler 1.60x2.40 metre büyüklüğündeki bir alanda yer almaktadır. Resimlerde betimlenen sürek avını düzenleyen 11 yaylı avcının, günümüzden 15000 ile 13000 yıl öncesinde (Üst Paleolitik dönem ile Mezolitik Çağ) yaşamış olduğu tahmin ediliyor.  Prof. Dr. BELLİ, 16 hayvan figürü ile 11 insan figürünün ortaya çıktığı kayaaltı sığınağının bir bölümünün kalker tabakası ile kaplı olduğunu, bu tabakanın altında başka resimler de bulunduğunu belirtmektedir. \r\nArkeozoolog Doç. Dr. Vedat ONAR, hayvanlardan ondördünün dağ keçisi, birinin oğlak, diğerinin ise kedigillerden bir hayvan olabileceğini düşünüyor. Kaya yüzeyine yapılan resimlerin ana konusunun çeşitli av hayvanları ile bunları yay ve ok ile avlayan insan figürleri oluşturuyor. Prof. Dr. BELLİ, Anadoluda bugüne kadar yay ve okla dağ keçisi avlayanları betimleyen benzer bir sürek avı sahnesine rastlanmadığına dikkat çekerek, “burada resmedilen 11 avcı şimdilik Anadolu’nun bilinen en eski avcıları” diyor. \r\n\r\nSığınaktaki Figürlerin Özellikleri: \r\n\r\nHayvan figürlerinin yapımında iki farklı yöntem uygulanmıştır. Bunlardan ilkinde figürlerin gövdeleri tümüyle dövme tekniği ile oyulmuş, ikincisinde ise figürlerin gövdelerinin dış konturları kalın ve derin bir çizgi ile belirtilmiştir.\r\nSinek Çayı Kayaaltı Sığınağındaki sürek avını yansıtan resimlerin  en yakın benzerine, İspanya’nın Castellon bölgesinde yedi yaylı avcının yaban domuzu avını gösteren- Mezolitik Çağ’a tarihlenen- sahnede rastlandı. \r\nKayaaltı Sığınağının bir kült merkezi olarak kullanan avcıların, hayvanları gövdelerine ok saplanmış olarak çizmesi, avın başarılı gerçekleşmesi için büyüye gereksinim duyduklarının bir göstergesi olarak nitelendirilmektedir.\r\nİnsan figürlerinin uzunluğu,9-14 cm. arasında değişiyor. Dağ keçilerinin boyu 18-25 cm. genişliği ise 30-36 cm. arasındadır.\r\n',NULL,0,'2026-02-22 09:52:36'),(19,'Haburman Köprüsü','Historical','1179 yılında Artuklular döneminde Zübeyde Hatun tarafından yaptırılan köprü, Sinek Çayı üzerindedir. Üç gözlü yapısı ve kalker taş işçiliği ile Artuklu mimarisinin simgelerindendir.\r\n: İlçenin batı çıkışında Sinek Çayı üzerindedir. İsmini bu köprünün batısında yer alan Haburman Köyünden almaktadır. Yontma iri beyaz taştan yapılan ve biri ana göz olmak üzere toplam üç gözden oluşan köprünün uzunluğu 108 m, genişliği 5,5 m, en yüksek yeri ise 12,5 m’dir. Bu yükseklik kenarlara doğru gidildikçe azalmaktadır. Köprünün duvarlarında üç kitabe bulunmaktadır. Bunlardan ikisi yapılışına, diğeri ise tamirine aittir. Bu kitabeden şunlar okunabilmektedir: \r\n“Bismillahir Rahman ir Rahim. (Haz)a ma tetavva’at bi’amelihi Zübeyde Hatun İbneti El-Emir Ül Ecel, Necmettin Albi ibn Timurtaş hamahal-lah. Fi seneti Hamse ve Seb’ine ve Hamse-mi’e 575 (1179)\r\nTürkçesi: Besmele. Bunun yapımını değerli Emir Necmeddin Albi’nin kızı Zübeyde Hatun’un yardımları ile Tanrı onu korusun.595 yılında inşa etti.\r\nBu kitabeden Haburman Köprüsünün Artuk oğlu Necmeddin Albi’nin  (1152-1176)   ölümünden üç yıl sonra, kızı Zübeyde Hatun tarafından, kardeşi II. İlgazi (1176-1184) döneminde ve H.575 (1179) tarihinde yaptırıldığı anlaşılmaktadır. Köprünün büyüklüğü ve gerektirdiği harcama göz önünde bulundurulursa köprünün yapıldığı devirde bu yolun çok işlek ve önemli yol olduğu görülmektedir. Bu yolun batı yönünde iki önemli merkez vardır. Diyarbakır Halep yolunun, Karacadağ üzerinden güneybatı yönünde uzandığı bilinmektedir. Bu durumda köprü üzerinden yolun ulaşabileceği tek önemli merkez Malatya olmaktadır. \r\nKöprünün doğu tarafında ve güney yüzündeki ikinci kitabe tahrip olduğundan okunamamaktadır. Yer yer okunabilen birkaç kelimeden, köprünün 1241(1825-26) tarihlerinde onarıldığı anlaşılmaktadır. \r\n','','38.13112502837112','39.44267086546826','uploads/places/default.jpg','','https://s.insta360.com/p/86a209eb9b1ebae06a3f8c77c1cade08?region=SG','Artuklu tarihinden ve Zübeyde Hatun\'un hayırseverliğinden bahset. Mimari detaylara (kemer yapıları) değin.\r\n: İlçenin batı çıkışında Sinek Çayı üzerindedir. İsmini bu köprünün batısında yer alan Haburman Köyünden almaktadır. Yontma iri beyaz taştan yapılan ve biri ana göz olmak üzere toplam üç gözden oluşan köprünün uzunluğu 108 m, genişliği 5,5 m, en yüksek yeri ise 12,5 m’dir. Bu yükseklik kenarlara doğru gidildikçe azalmaktadır. Köprünün duvarlarında üç kitabe bulunmaktadır. Bunlardan ikisi yapılışına, diğeri ise tamirine aittir. Bu kitabeden şunlar okunabilmektedir: \r\n“Bismillahir Rahman ir Rahim. (Haz)a ma tetavva’at bi’amelihi Zübeyde Hatun İbneti El-Emir Ül Ecel, Necmettin Albi ibn Timurtaş hamahal-lah. Fi seneti Hamse ve Seb’ine ve Hamse-mi’e 575 (1179)\r\nTürkçesi: Besmele. Bunun yapımını değerli Emir Necmeddin Albi’nin kızı Zübeyde Hatun’un yardımları ile Tanrı onu korusun.595 yılında inşa etti.\r\nBu kitabeden Haburman Köprüsünün Artuk oğlu Necmeddin Albi’nin  (1152-1176)   ölümünden üç yıl sonra, kızı Zübeyde Hatun tarafından, kardeşi II. İlgazi (1176-1184) döneminde ve H.575 (1179) tarihinde yaptırıldığı anlaşılmaktadır. Köprünün büyüklüğü ve gerektirdiği harcama göz önünde bulundurulursa köprünün yapıldığı devirde bu yolun çok işlek ve önemli yol olduğu görülmektedir. Bu yolun batı yönünde iki önemli merkez vardır. Diyarbakır Halep yolunun, Karacadağ üzerinden güneybatı yönünde uzandığı bilinmektedir. Bu durumda köprü üzerinden yolun ulaşabileceği tek önemli merkez Malatya olmaktadır. \r\nKöprünün doğu tarafında ve güney yüzündeki ikinci kitabe tahrip olduğundan okunamamaktadır. Yer yer okunabilen birkaç kelimeden, köprünün 1241(1825-26) tarihlerinde onarıldığı anlaşılmaktadır. \r\n',NULL,0,'2026-02-22 09:52:36'),(20,'Ulu Cami','Historical','Ulu Cami: İlçenin batısındaki Kale Mahallesi’nde bulunmaktadır. Yapı ayrı zamanlarda inşa edilmiş, bitişik iki camiden oluşmaktadır. Batı kesimi ilk yapıdan kalmış, doğudaki tek kubbeli kısım ise sonradan ilave edilmiştir. Doğudan batıya doğru uzanan üç tonoz ile üzeri kaplı olan Ulu Cami, üçer kemerli iki sıra tonoz duvarlarıyla içeriden bölünmüştür. Batıda bulunan kapısı orta tonusun altından açılmaktadır. \r\nUlu Caminin orta bölümünde iki adet kitabe bulunmaktadır. Yapılış kitabesinde şu bilgiler yer almaktadır: \r\n‘‘Cüddede İmaret’ül - Mescid El Halid bi Ebubekr Ali bin El-hac Ömer bin Mahmut. Fi seneti Erbaa…”\r\n	Türkçesi: Bunun yapılmasının, efendi, malik, adil, nimetlerin sahibi Fahreddin Kara Arslan Bin Davut, Bin Sökmen, Bin Artuk - Tanrının rahmeti onun üzerine olsun- Müslüman cemaatinin koruyucusu, din uğruna savaşan İlaldı Bin….-Tanrı….devamlı kılsın - emretti. \r\nYukarıdaki kitabede adı geçen Fahreddin Kara Arslan Bin Davut, Bin Sökmen, Bin Artuk seceresinde de anlaşıldığı gibi Artuklu Hanedanının Hısn Keyfa koluna mensup olup, bu kolun 4. Sultanıdır. 539/1144-45 yılında tahta geçmiş ve 22 Ramazan 562/12 Temmuz 1167 de vefat ederek Hısn Keyfa’da defnedilmiştir.\r\n	Onarım kitabesinin Türkçesi: Bu mescidi Hacı Halid Bin Ebubekir Ali Bin Hacı Ömer Bin Mahmut 640/1242-43 yılında onarmıştır.\r\n	Bu kitabelerden anlaşılacağı üzere Ulu Cami 539/1144-45 tarihinde Hısn Keyfa (Hasan Keyf) Artuklularından Fahreddin Kara Arslan zamanında, İnaloğullarından Ebu Mansur İlaldı Bin İbrahim tarafından inşa ettirilmiştir. Yaklaşık olarak 100 yıl sonra 640(1240-43) yılında Hacı Halid Bin Ebubekir adında bir zat caminin harap olan kısımlarını onarmıştır.\r\n	Moğol istilası sonucu tahrip olan cami 1297-1302 yıllarında Selçuklu Sultanı III.Alâaddin Keykubad zamanında tekrar onarılmıştır. Son olarak 923/1517 tarihinde, caminin harap olan doğu kanadına bitişik olarak Şah Ali Bey Camisi inşa edilmiştir. \r\nUlu Camide halk arasında kutsal emanet olarak bilinen peygamberimizin tabutu üzerine sarılmış lacivert renkli bir şal parçası bulunmaktadır. Bu şal ilçede yaşayan Mütevelliler Kabilesi tarafından korunmakta ve her sene Kurban Bayramının arifesinde ikindi namazından sonra tekbirlerle yeniden alınarak camide bulunan cemaate gösterilmektedir. 70x75 cm ölçülerinde olan bu kutsal emanette şu yazılar okunmaktadır: “Allah Celle Celaluh, Allah, Lailahe-illallah Muhammed’ün Resullullah.”\r\n\r\n','','38.13532243705375','39.44598973347841','uploads/places/default.jpg','','','Ulu Cami: İlçenin batısındaki Kale Mahallesi’nde bulunmaktadır. Yapı ayrı zamanlarda inşa edilmiş, bitişik iki camiden oluşmaktadır. Batı kesimi ilk yapıdan kalmış, doğudaki tek kubbeli kısım ise sonradan ilave edilmiştir. Doğudan batıya doğru uzanan üç tonoz ile üzeri kaplı olan Ulu Cami, üçer kemerli iki sıra tonoz duvarlarıyla içeriden bölünmüştür. Batıda bulunan kapısı orta tonusun altından açılmaktadır. \r\nUlu Caminin orta bölümünde iki adet kitabe bulunmaktadır. Yapılış kitabesinde şu bilgiler yer almaktadır: \r\n‘‘Cüddede İmaret’ül - Mescid El Halid bi Ebubekr Ali bin El-hac Ömer bin Mahmut. Fi seneti Erbaa…”\r\n	Türkçesi: Bunun yapılmasının, efendi, malik, adil, nimetlerin sahibi Fahreddin Kara Arslan Bin Davut, Bin Sökmen, Bin Artuk - Tanrının rahmeti onun üzerine olsun- Müslüman cemaatinin koruyucusu, din uğruna savaşan İlaldı Bin….-Tanrı….devamlı kılsın - emretti. \r\nYukarıdaki kitabede adı geçen Fahreddin Kara Arslan Bin Davut, Bin Sökmen, Bin Artuk seceresinde de anlaşıldığı gibi Artuklu Hanedanının Hısn Keyfa koluna mensup olup, bu kolun 4. Sultanıdır. 539/1144-45 yılında tahta geçmiş ve 22 Ramazan 562/12 Temmuz 1167 de vefat ederek Hısn Keyfa’da defnedilmiştir.\r\n	Onarım kitabesinin Türkçesi: Bu mescidi Hacı Halid Bin Ebubekir Ali Bin Hacı Ömer Bin Mahmut 640/1242-43 yılında onarmıştır.\r\n	Bu kitabelerden anlaşılacağı üzere Ulu Cami 539/1144-45 tarihinde Hısn Keyfa (Hasan Keyf) Artuklularından Fahreddin Kara Arslan zamanında, İnaloğullarından Ebu Mansur İlaldı Bin İbrahim tarafından inşa ettirilmiştir. Yaklaşık olarak 100 yıl sonra 640(1240-43) yılında Hacı Halid Bin Ebubekir adında bir zat caminin harap olan kısımlarını onarmıştır.\r\n	Moğol istilası sonucu tahrip olan cami 1297-1302 yıllarında Selçuklu Sultanı III.Alâaddin Keykubad zamanında tekrar onarılmıştır. Son olarak 923/1517 tarihinde, caminin harap olan doğu kanadına bitişik olarak Şah Ali Bey Camisi inşa edilmiştir. \r\nUlu Camide halk arasında kutsal emanet olarak bilinen peygamberimizin tabutu üzerine sarılmış lacivert renkli bir şal parçası bulunmaktadır. Bu şal ilçede yaşayan Mütevelliler Kabilesi tarafından korunmakta ve her sene Kurban Bayramının arifesinde ikindi namazından sonra tekbirlerle yeniden alınarak camide bulunan cemaate gösterilmektedir. 70x75 cm ölçülerinde olan bu kutsal emanette şu yazılar okunmaktadır: “Allah Celle Celaluh, Allah, Lailahe-illallah Muhammed’ün Resullullah.”\r\n\r\n',NULL,0,'2026-02-22 09:52:36'),(21,'Çeteci Abdullah Paşa Medresesi','Historical','1756 yılında Osmanlı valisi Çeteci Abdullah Paşa tarafından yaptırılmıştır. Günümüzde cami olarak hizmet veren yapı, klasik Osmanlı medrese mimarisini yansıtır.\r\nÇarşının orta yerinde ve ana caddenin güney yanında bulunan medrese, İran Hükümdarı Afşarlı Nadir Şah’ı yenerek onun Doğu Anadolu’yu istilasına engel olan yiğit serdarlarımızdan Çermikli Çeteci-Abdullah Paşa tarafından Diyarbakır Valiliği zamanında H. 1170/1756-57 yıllarında yapılmıştır. Yapı genel hatları ile dikdörtgen bir plana sahiptir. Medresede revaklar ve bunların gerisinde yer alan hücreler, merkezi avlunun üç kenarına sıralanmış, avlunun kuzey kenarı boş bırakılmıştır. Revakların avluya bakan yüzleri koyu gri renkli bazalt ve bej renkli kesme taşlarla örülmüştür. Revaklar sekiz taş üzerine oturtulmuştur. Revakların üzeri pandantifler üzerine oturan on bir küçük kubbe ile örtülüdür. Hücrelerin hepsi beşik tonozlarla örtülüdür. Medreseye güney revakının ortasından açılan bir kapıyla içeri girilmektedir. Bu kapının üzerinde tunç bir levha üzerine kazılmış üç satırlık bir inşa kitabesi yer almaktadır. Bu kitabede şöyle yazılmaktadır:\r\n“Buniye Hazihi’l-Medreset’ül-Mübareket’ül-ilm ve’l bereketi bi kuvveti’l-Aziz’ül-Kadir Abdullah El-Vezir üş şehir bi-Çeteci.Fi seneti Seb’ine ve Mi’e ve Elf. Ketebehü bi-hatt.” 1170\r\nTürkçesi: Bu mübarek medrese ilim ve bereket için, Çeteci lakabıyla tanınan aziz ve kudretli Abdullah’ın yardımlarıyla 1170/1756-57 yılında inşa edildi. Bu yazıyı kendisi yazdı.\r\nSultan II. Abdülhamid zamanında bir ara “Çermik Rüşdiye Mektebi” olarak kullanılan medrese, cumhuriyet devrinde ise Çermik Camii Yaptırma Derneği tarafından tamir edilerek cami olarak kullanılmaya başlanmıştır. \r\n','','38.135220218074245','39.449006334689834','uploads/places/default.jpg','','https://s.insta360.com/p/11a4c9923795e59abbdea8dcf3d8a824?region=SG','Osmanlı eğitim sisteminden ve Çeteci Abdullah Paşa\'nın ilmi kişiliğinden bahset.\r\nÇarşının orta yerinde ve ana caddenin güney yanında bulunan medrese, İran Hükümdarı Afşarlı Nadir Şah’ı yenerek onun Doğu Anadolu’yu istilasına engel olan yiğit serdarlarımızdan Çermikli Çeteci-Abdullah Paşa tarafından Diyarbakır Valiliği zamanında H. 1170/1756-57 yıllarında yapılmıştır. Yapı genel hatları ile dikdörtgen bir plana sahiptir. Medresede revaklar ve bunların gerisinde yer alan hücreler, merkezi avlunun üç kenarına sıralanmış, avlunun kuzey kenarı boş bırakılmıştır. Revakların avluya bakan yüzleri koyu gri renkli bazalt ve bej renkli kesme taşlarla örülmüştür. Revaklar sekiz taş üzerine oturtulmuştur. Revakların üzeri pandantifler üzerine oturan on bir küçük kubbe ile örtülüdür. Hücrelerin hepsi beşik tonozlarla örtülüdür. Medreseye güney revakının ortasından açılan bir kapıyla içeri girilmektedir. Bu kapının üzerinde tunç bir levha üzerine kazılmış üç satırlık bir inşa kitabesi yer almaktadır. Bu kitabede şöyle yazılmaktadır:\r\n“Buniye Hazihi’l-Medreset’ül-Mübareket’ül-ilm ve’l bereketi bi kuvveti’l-Aziz’ül-Kadir Abdullah El-Vezir üş şehir bi-Çeteci.Fi seneti Seb’ine ve Mi’e ve Elf. Ketebehü bi-hatt.” 1170\r\nTürkçesi: Bu mübarek medrese ilim ve bereket için, Çeteci lakabıyla tanınan aziz ve kudretli Abdullah’ın yardımlarıyla 1170/1756-57 yılında inşa edildi. Bu yazıyı kendisi yazdı.\r\nSultan II. Abdülhamid zamanında bir ara “Çermik Rüşdiye Mektebi” olarak kullanılan medrese, cumhuriyet devrinde ise Çermik Camii Yaptırma Derneği tarafından tamir edilerek cami olarak kullanılmaya başlanmıştır. \r\n',NULL,0,'2026-02-22 09:52:36'),(22,'Beyler Sarayı','Historical','15. yüzyıl başlarında Çermik Ocaklı Beyleri tarafından yaptırılmıştır. Selamlık, harem, mescit ve zindan bölümleriyle bir yönetim merkezi olarak kullanılmıştır.\r\nSaray Mahallesinin orta kesiminde, yüksek ve müstahkem duvarlarla çevrili bu sarayın eyvanları, yazlık, kışlık, selamlık ve harem daireleri, mescidi, hamamı ve zindanları bulunmaktadır. Çermik Ocaklı Beyleri tarafından XV. yüzyıl başlarında yaptırılmıştır. Bu sarayın birçok bölümü günümüzde bile varlığını sürdürmektedir. ','','38.136582553421746','39.44940152545274','uploads/places/default.jpg','','https://s.insta360.com/p/ed03b336db893d8f709b99170ece171a?region=SG','Çermik beylerinin yaşantısını ve sarayın otoritesini anlatan bir dille konuş.\r\nSaray Mahallesinin orta kesiminde, yüksek ve müstahkem duvarlarla çevrili bu sarayın eyvanları, yazlık, kışlık, selamlık ve harem daireleri, mescidi, hamamı ve zindanları bulunmaktadır. Çermik Ocaklı Beyleri tarafından XV. yüzyıl başlarında yaptırılmıştır. Bu sarayın birçok bölümü günümüzde bile varlığını sürdürmektedir. ',NULL,0,'2026-02-22 09:52:36'),(23,'Saray Hamamı','Historical','Beyler Sarayı\'nın bir parçası olan bu hamam, 16. yüzyıl Osmanlı sivil mimarisinin güzel bir örneğidir. Günümüzde Çermik Belediyesi Kültür Evi olarak hizmet vermektedir.\r\nBeyler Sarayının güney kısmında, ilçe merkezinde yer almaktadır. Dört kubbeli ve ak mermerlerle kara taştan yapılmış olan bu yapı,  sivil mimarimizin bir şah eseridir. XVI ve XVII. yüzyıllardan kalma bir eser olan hamamda herhangi bir kitabeye rastlanılmamıştır. \r\nBinanın dış duvarları kırma taşlarla inşa edilmiştir. Soyunma yerin ortasında bir havuz vardır. Soyunma yerinin kuzeybatısında bulunan basık kemerle örtülü bir kapıdan dikdörtgen biçimindeki bir başka mekâna geçilmektedir. Doğu batı istikametinde uzanan kırık kemerli tonoz bu mekânı örtmektedir. Mekânın batı duvarı üzerinde bir ışık penceresi, tonoz tepesi üzerinde ayrıca bir ışık feneri bulunmaktadır. Buradan aynı tipte bir kapı ile kırık kemerli bir tonozla örtülü dikdörtgen planlı bir başka mekâna varılır. Bu mekânın doğu tarafında, yarım sekizgen planlı, dilimli bir yarım kubbe ile örtülü eyvan dikkati çeker. Kuzeybatı köşesindeki basık kemerli bir kapı sıcaklığa açılmaktadır. Yan duvarlar boyunca eyvanlar, köşelerde ise yıkanma hücreleri bulunur. Eyvanlar kırık kemerli tonozlarla örtülüdür. Soyunma kısmı ve diğer kısımların tabanı parke taşlarla kaplıdır.\r\n','','38.1359273465142','39.448784346476565','uploads/places/default.jpg','','https://s.insta360.com/p/0de9caf7a798dc33fcc89afb95b5596c?region=SG','Osmanlı hamam kültürü ve yapının Kültür Evi\'ne dönüşüm hikayesini paylaş.\r\nBeyler Sarayının güney kısmında, ilçe merkezinde yer almaktadır. Dört kubbeli ve ak mermerlerle kara taştan yapılmış olan bu yapı,  sivil mimarimizin bir şah eseridir. XVI ve XVII. yüzyıllardan kalma bir eser olan hamamda herhangi bir kitabeye rastlanılmamıştır. \r\nBinanın dış duvarları kırma taşlarla inşa edilmiştir. Soyunma yerin ortasında bir havuz vardır. Soyunma yerinin kuzeybatısında bulunan basık kemerle örtülü bir kapıdan dikdörtgen biçimindeki bir başka mekâna geçilmektedir. Doğu batı istikametinde uzanan kırık kemerli tonoz bu mekânı örtmektedir. Mekânın batı duvarı üzerinde bir ışık penceresi, tonoz tepesi üzerinde ayrıca bir ışık feneri bulunmaktadır. Buradan aynı tipte bir kapı ile kırık kemerli bir tonozla örtülü dikdörtgen planlı bir başka mekâna varılır. Bu mekânın doğu tarafında, yarım sekizgen planlı, dilimli bir yarım kubbe ile örtülü eyvan dikkati çeker. Kuzeybatı köşesindeki basık kemerli bir kapı sıcaklığa açılmaktadır. Yan duvarlar boyunca eyvanlar, köşelerde ise yıkanma hücreleri bulunur. Eyvanlar kırık kemerli tonozlarla örtülüdür. Soyunma kısmı ve diğer kısımların tabanı parke taşlarla kaplıdır.\r\n',NULL,0,'2026-02-22 09:52:36'),(24,'Karakaya Hanı','Historical','Tarihi İpek Yolu üzerinde, kervanların konaklaması için Anadolu Selçuklu döneminde inşa edilmiştir. 700 yıllık bu han, restorasyon sonrası turizme kazandırılmıştır.\r\nÇermik İlçe merkezine 25 km. güneybatısında yer alan Karakaya Köyünde bulunmaktadır. Han, kuzey-güney uzanan genel hatlarıyla dikdörtgen bir yapıdır. Taş kapısı güney kısmındadır. Yapı iki sıra halinde düzenlenmiş sekiz adet taş paye ile üç sahına bölünmüştür. Kuzey-güney yönünde uzanan sahınlar hafif sivri kemerli tonozlarla örtülüdür. Yapının tamamı düzgün sıralar halinde dizilmiş kırma taşlarla inşa edilmiştir. Tonozların inşasında ise yassı taş plakalar kullanılmıştır. Yapılan araştırmalarda avlusuz Selçuklu Hanlarının geç devir eseri olduğu belirtilen Karakaya Hanı XIII yy sonları ile XIV yy başlarına tarihlenmektedir.','','38.06058258241191','39.30632683708964','uploads/places/default.jpg','','','İpek Yolu kervanlarının hikayelerini ve hanın mimari sağlamlığını anlat.\r\nÇermik İlçe merkezine 25 km. güneybatısında yer alan Karakaya Köyünde bulunmaktadır. Han, kuzey-güney uzanan genel hatlarıyla dikdörtgen bir yapıdır. Taş kapısı güney kısmındadır. Yapı iki sıra halinde düzenlenmiş sekiz adet taş paye ile üç sahına bölünmüştür. Kuzey-güney yönünde uzanan sahınlar hafif sivri kemerli tonozlarla örtülüdür. Yapının tamamı düzgün sıralar halinde dizilmiş kırma taşlarla inşa edilmiştir. Tonozların inşasında ise yassı taş plakalar kullanılmıştır. Yapılan araştırmalarda avlusuz Selçuklu Hanlarının geç devir eseri olduğu belirtilen Karakaya Hanı XIII yy sonları ile XIV yy başlarına tarihlenmektedir.',NULL,0,'2026-02-22 09:52:36'),(25,'Sinagog ve Kilise','Historical','Çermik\'in çok kültürlü geçmişinin tanıklarıdır. 1416 yılında inşa edilen Sinagog ve Kale yakınındaki Kilise kalıntıları, bölgedeki inanç mozaiğini temsil eder.\r\nSinagog: İlçemizin Kale Mahallesinde Yahudilerden kalma bir sinagog bulunmaktadır. Tamamı siyah ve beyaz bazalt taşlardan yapılan Sinagogun hangi döneme ait olduğu bilinmemektedir. Günümüzde ev olarak kullanılan bu yapı tek bir bölümden oluşmaktadır. Yapının üzerinde, İbranice olduğu tahmin edilen bir kitabe yer almaktadır. Dış cephesinde yan yana bulunan iki kemerli bölümden, sol taraftakinde orijinal su düzeneği mevcuttur. Sinagogun içindeki odada nişler bulunmaktadır. İki sütunlu üç kemerli bölüm odayı ikiye ayırmaktadır. \r\n\r\nKilise: Kilisenin bir kısmı bugün ev olarak kullanılmaktadır. Yıkık haldedir. Ne zaman ve kimin tarafından yaptırıldığı bilinmemektedir. Evin taşlarında Ermenice yazılar vardır. İlçedeki; cami, kilise ve sinagogun yan yana bulunması ilçemizde geçmişten beri çeşitli dinlere mensup insanların birlikte yaşadıklarının en iyi kanıtıdır. \r\n\r\n','','38.13590000','39.45020000','uploads/places/default.jpg','','','Çermik\'in hoşgörü iklimini ve farklı dinlerin bir arada yaşama tarihini vurgula.\r\nSinagog: İlçemizin Kale Mahallesinde Yahudilerden kalma bir sinagog bulunmaktadır. Tamamı siyah ve beyaz bazalt taşlardan yapılan Sinagogun hangi döneme ait olduğu bilinmemektedir. Günümüzde ev olarak kullanılan bu yapı tek bir bölümden oluşmaktadır. Yapının üzerinde, İbranice olduğu tahmin edilen bir kitabe yer almaktadır. Dış cephesinde yan yana bulunan iki kemerli bölümden, sol taraftakinde orijinal su düzeneği mevcuttur. Sinagogun içindeki odada nişler bulunmaktadır. İki sütunlu üç kemerli bölüm odayı ikiye ayırmaktadır. \r\n\r\nKilise: Kilisenin bir kısmı bugün ev olarak kullanılmaktadır. Yıkık haldedir. Ne zaman ve kimin tarafından yaptırıldığı bilinmemektedir. Evin taşlarında Ermenice yazılar vardır. İlçedeki; cami, kilise ve sinagogun yan yana bulunması ilçemizde geçmişten beri çeşitli dinlere mensup insanların birlikte yaşadıklarının en iyi kanıtıdır. \r\n\r\n',NULL,0,'2026-02-22 09:52:36'),(26,'Çermik Kaplıcaları','HotSpring','Çermik Melike Belkıs Kaplıcaları:  İlçe merkezinin Hamambaşı mevkiinde bulunan Melike Belkıs Kaplıcaları, tedavi özellikleri ve niteliği bakımından dünyanın en iyi kaplıcalarından biri olup tarihin çok eski dönemlerinden beri insanlığa şifa dağıtmaya devam etmektedir. \r\nBüyük Paşa” ve “Küçük Paşa” denilen tarihi hamamların yanında iki adet localı ve bir adet “Özel Aile Kabinleri” olmak üzere beş ayrı bölümde hizmet verilmektedir. Çermik Melike Belkıs Kaplıcaları dört mevsim hizmete açıktır. Tedavi amaçlı kullanımı genellikle haziran-eylül ayları arasında yoğunluk kazanmaktadır. Kaplıcaların su sıcaklığı 48 0C  olup tavsiye edilen kullanım süresi 21 kür’dür.\r\nİlçemizde geçmiş yıllarda Melike Belkıs Festivali düzenlenmiş fakat bu festivaller sürekli hale getirilememiştir. Kaymakamlık ve Belediye Başkanlığınca bu Festivallerin geleneksel hale getirilmesi için çalışmalar sürdürülmektedir.\r\n  \r\n\r\nKaplıca Suyunun Özellikleri: Kaplıca suyumuz en son 05/04/2005 yılında 1192 Protokol numarasıyla “Sağlık Bakanlığı Refik Saydam Hıfzıssıhha Merkezi başkanlığı Çevre Sağlığı Araştırma Müdürlüğü” tarafından analiz edilmiştir. Analiz sonuçlarına göre kaplıca suyumuz kimyasal ve bakteriyolojik yönden insan sağlığına elverişli olduğu görülmüştür.\r\n','1- İltihabi Romatizmalar\r\n  2- Kronik Bel Ağrıları\r\n 3- Kireçlenmeler\r\n 4- Eklem Hastalıkları\r\n 5-Kas ağrıları ve Kas Romatizmaları\r\n 6- Yaralanma ve Cilt Hastalıkları\r\n 7- Yumuşak doku Hastalıkları\r\n  8- Sinir sistemiyle ilgili hastalıklar\r\n  9- Genel Stres Bozuklukları\r\n10- Spor yaralanmaları ve tedavisi\r\n11- Kadın hastalıkları\r\n12- Kemik Erimesi\r\n13- İdrar yolları ve safra kesesi Rahatsızlıkları\r\n14- Ortopedik Operasyonların, Beyin ve Sinir Cerrahisi sonrası gibi  uzun süreli hareketsiz kalma durumlarında etkilidir. \r\n','38.14025954147257','39.47934183416329','assets/img/kaplica_default.jpg','','https://s.insta360.com/p/314ed3120341e7a5b2375c850955f37b?region=SG','İlçemizin Tarihi ve Turistik Yerleri\r\nÇermik Melike Belkıs Kaplıcaları:  İlçe merkezinin Hamambaşı mevkiinde bulunan Melike Belkıs Kaplıcaları, tedavi özellikleri ve niteliği bakımından dünyanın en iyi kaplıcalarından biri olup tarihin çok eski dönemlerinden beri insanlığa şifa dağıtmaya devam etmektedir. \r\nBüyük Paşa” ve “Küçük Paşa” denilen tarihi hamamların yanında iki adet localı ve bir adet “Özel Aile Kabinleri” olmak üzere beş ayrı bölümde hizmet verilmektedir. Çermik Melike Belkıs Kaplıcaları dört mevsim hizmete açıktır. Tedavi amaçlı kullanımı genellikle haziran-eylül ayları arasında yoğunluk kazanmaktadır. Kaplıcaların su sıcaklığı 48 0C  olup tavsiye edilen kullanım süresi 21 kür’dür.\r\nİlçemizde geçmiş yıllarda Melike Belkıs Festivali düzenlenmiş fakat bu festivaller sürekli hale getirilememiştir. Kaymakamlık ve Belediye Başkanlığınca bu Festivallerin geleneksel hale getirilmesi için çalışmalar sürdürülmektedir.\r\n  \r\n\r\nKaplıca Suyunun Özellikleri: Kaplıca suyumuz en son 05/04/2005 yılında 1192 Protokol numarasıyla “Sağlık Bakanlığı Refik Saydam Hıfzıssıhha Merkezi başkanlığı Çevre Sağlığı Araştırma Müdürlüğü” tarafından analiz edilmiştir. Analiz sonuçlarına göre kaplıca suyumuz kimyasal ve bakteriyolojik yönden insan sağlığına elverişli olduğu görülmüştür.\r\n\r\nMELİKE BELKIS KAPLICALARI SU ANALİZ SONUÇLARI\r\nİncelenen Parametreler	Yöntem	Analiz Sonuçları\r\n		Mg/L	Meg/L	Milival\r\nKoku Tat	Organoleptik	Kükürt	Kokulu	\r\nRenk (Pt/Co Skalası)	Fotometrik	26.0	-	-\r\nBulanıklık ( mg/lt SiO2 )	Türbimetrik	15.2	-	-\r\npH	Elektrometrik	7.77	-	-\r\nElektriksel İletkenliği (umhos; 25 °C)	Elektrometrik	1100	-	-\r\nKarbondioksit (gr/lt)	Titrimetrik	0,49	-	-\r\nSodyum	ICP	231,4	10,06	75,475\r\nPotasyum	ICP	25,7	0,659	4,944\r\nAmonyum		0,9	-	-\r\nMagnezyum	ICP	11,18	0,920	6,902\r\nKalsiyum	ICP	33,87	1,690	12,679\r\nMangan	ICP	0,0036	-	-\r\nDemir	ICP	0,0114	-	-\r\nFlorür	İyon seçici elektrod	5,1	0,269	2,016\r\nKlorür	Titrimetrik	127,8	3,6	26,976\r\nBromür	İyon seçici elektrod	1,89	0,024	0,180\r\nİyodür	İyon seçici elektrod	0,12	0,0009	0,0066\r\nNitrit	Fotometrik	<0,005	-	-\r\nNitrat	Spektrofotometrik	<1,0	-	-\r\nSülfat	Spektrofotometrik	63,77	1,329	9,959\r\nBikarbonat	Titrimetrik	494,1	8,1	60,697\r\nHidrojensültür/ Sülfür	Fotometrik	<0,1		\r\nFosfat	Fotometrik	0,69	0,022	0,165\r\nSilikat Asidi	ICP	48,43	-	-\r\nBorik Asit	ICP	3,33	-	-\r\nArserik	ICP	<0,001		\r\nKadmiyum	ICP	0,0006		\r\nKrom	ICP	0,0005		\r\nCiva	ICP	0,0005		\r\nNikel	ICP	0,0005		\r\nKurşun	ICP	0,001		\r\nAntimon	ICP	0,001		\r\nSelenyum	ICP	0,0016		\r\nBaryum	ICP	0,301		\r\nBakır	ICP	0,0004		\r\nÇinko	ICP	0,026		\r\nAlüminyum	ICP	0,009		\r\nMikrobiyolojik Ölçümler\r\nİncelenen Parametreler	Yöntem	Yönetmelik Değeri	Analiz Sonuçları	\r\nToplam Jerm sayısı / ml (20 ± 2 °C de 48 saatte )	PP	0-5	0	\r\nToplam Jerm sayısı / ml (20 ± 2 °C de 72 saatte SPC\'deki koloni sayısı)	PP	0-30	0	\r\nTotal Koliform/100 ml	MPN	0	0	\r\nFakal Koliform /100 ml	MPN	0	0	\r\nFakal Streptococ /100 ml	MF	0	0	\r\nSülfit Redükte Eden Bakteriler	ST	0	0	\r\nPaeudomonas eruginosa	MF	0	0	\r\nEcoli	MF	0	0	\r\n\r\nKaplıca Suyundan Şifa Bulan Hastalıklar: Kaplıca suyunun niteliğine göre tıbbi açıdan değerlendirilmesi yapılmış ve Sağlık Bakanlığının 06.06.2005 tarihinde yapılan Tıbbi Değerlendirme Kurulu Toplantısında aşağıda belirtilen hastalıkların tedavisinde kullanılabilir olduğu anlaşılmıştır. \r\nBu hastalıklar:\r\n\r\n  1- İltihabi Romatizmalar\r\n  2- Kronik Bel Ağrıları\r\n 3- Kireçlenmeler\r\n 4- Eklem Hastalıkları\r\n 5-Kas ağrıları ve Kas Romatizmaları\r\n 6- Yaralanma ve Cilt Hastalıkları\r\n 7- Yumuşak doku Hastalıkları\r\n  8- Sinir sistemiyle ilgili hastalıklar\r\n  9- Genel Stres Bozuklukları\r\n10- Spor yaralanmaları ve tedavisi\r\n11- Kadın hastalıkları\r\n12- Kemik Erimesi\r\n13- İdrar yolları ve safra kesesi Rahatsızlıkları\r\n14- Ortopedik Operasyonların, Beyin ve Sinir Cerrahisi sonrası gibi  uzun süreli hareketsiz kalma durumlarında etkilidir. \r\n\r\nKonaklama ve Ulaşım: Melike Belkıs Kaplıcaları çevresinde birçok otel ve pansiyon bulunmaktadır. Yatak kapasitesi 1200 ün üzerinde olan bu otel ve pansiyonlarda her bütçeye uygun barınma olanağı mevcuttur. Çevre İl ve İlçelere yakınlığı nedeniyle kaplıcalara günübirlik ziyaret yapılabilmektedir. \r\nÇermik Kaplıcalarına ulaşım konusunda herhangi bir sıkıntı yaşanmamaktadır. Günün her saatinde Diyarbakır, Ergani ve Siverek’e otobüs ve minibüs seferleri düzenli olarak yapılmaktadır.\r\n\r\nÇermik Kaplıcalarının Efsanesi: Rivayete göre, Çermik’te hüküm süren dönemin hükümdarının Melike Belkıs adında çok güzel bir kızı varmış. Bu kız bir gün hastalanmış ve vücudunda birtakım yaralar çıkmıştır. Zamanın hekimleri, Melike Belkıs’ı tedavi etmek için çok çabalar sarf etmişler fakat hastalığına bir türlü çare bulamamışlar. Zamanla Melike Belkıs’ın vücuduna kurtlar düşmüş ve vücudu çok pis kokular yaymaya başlamıştır. Öyle ki bu kokulardan Melike Belkıs’ın bulunduğu saraya girilemez olmuş. Hükümdar, bu durum üzerine kızını saraydan uzaklaştırmak için yanına muhafızlar vererek bu günkü kaplıcaların bulunduğu bölgeye göndermiştir. Melike Belkıs etrafta gezinirken sıcak bir su kaynağına rastlamış ve vakit geçirmek için her gün bu su ile oynamaya başlamış. Kısa süre sonra Melike Belkıs’ın vücudunun su ile temas eden bölgelerinde yaralarının iyileştiği görülmüş. Durumu fark eden Melike Belkıs, bu suyla 21 defa yıkandıktan sonra yaralarından tamamen kurtulmuştur. \r\n	Melike Belkıs’ın tümüyle iyileştiğini gören muhafızlar büyük sevinç içerisinde durumu saraya bildirmişler. Bu sevindirici haberi alan hükümdar, saray ahalisi ile birlikte büyük bir coşku içerisinde Melike Belkıs’ı almaya gitmişler. Melike Belkıs kendisine şifa olan suyu babasına göstermesi üzerine Hükümdar ustalarına emir vererek bugünkü ‘Büyük Paşa’   dediğimiz hamamı inşa ettirmiştir. \r\n\r\n\r\n',NULL,0,'2026-02-22 20:01:46'),(27,'15 Temmuz Parkı','','Diyarbakır\'ın Çermik ilçesinde bulunan 15 Temmuz Şehitler Parkı, ilçenin en önemli sosyal donatı ve mesire alanlarından biridir. Parkın genel özellikleri ve öne çıkan tarihi dönüşümü hakkında bilmen gereken detaylar şunlardır:\r\n\r\nGenel Bilgi ve Konum\r\nKonum: Çermik ilçesinin kuzey kısmında, Tepe Mahallesi sınırları içerisinde ve Çüngüş karayolu üzerinde yer almaktadır.\r\n\r\nSeyirtepe Özelliği: Konumu itibarıyla ilçeye yüksekten bakan bir noktada bulunduğu için halk arasında \"Seyirtepe\" olarak da adlandırılır. Bu sayede ziyaretçilerine muazzam bir ilçe ve doğa manzarası sunar.\r\n\r\nBüyüklük: Yaklaşık 33,7 dönümlük (33.700 metrekare) devasa bir alan üzerine kurulmuştur ve bu yönüyle ilçeye kazandırılmış en büyük parklardan biridir.\r\n\r\nÇarpıcı Bir \"Kentsel Dönüşüm\" Hikayesi\r\nParkın bulunduğu alan, yapımından önce yaklaşık 30 yıl boyunca ilçenin vahşi çöp döküm alanı olarak kullanılıyordu. Yayılan ağır koku ve duman hem Çermik hem de Çüngüş halkını ciddi şekilde rahatsız ediyordu.\r\n\r\n2017 Yılındaki Proje: Çermik Belediyesi, 2017 yılında çöp alanını ilçe merkezinden 1,5 kilometre uzağa taşıyarak bu 30 yıllık sorunu çözdü.\r\n\r\nMühendislik Çalışması: Eski çöp alanının parka dönüştürülmesi sürecinde, toprak altında metan gazı sıkışması ve patlaması riskine karşı uzman mühendislerce özel altyapı ve gaz tahliye çalışmaları yapıldı. Ardından bu alan rehabilite edilerek bugünkü modern görünümüne kavuşturuldu.\r\n\r\nParkın İçerisindeki Sosyal Alanlar ve Donanımlar\r\nİlçe halkının ve Çermik\'e gelen turistlerin nefes alabileceği bir yaşam merkezi olarak tasarlanan parkın içerisinde şu imkanlar bulunmaktadır:\r\n\r\nManzara ve Seyir Alanları: İlçeyi kuşbakışı izleme fırsatı veren seyir noktaları.\r\n\r\nYürüyüş Yolları: Sabah sporları ve akşam yürüyüşleri için düzenlenmiş geniş parkurlar.\r\n\r\nPiknik Alanları: Ailelerin hafta sonları vakit geçirebileceği, doğayla iç içe dinlenme alanları.\r\n\r\nKafeterya: Ziyaretçilerin yeme-içme ihtiyaçlarını karşılayabileceği sosyal tesis.\r\n\r\nÇocuk Oyun Parkları: Çocukların güvenle vakit geçirebileceği geniş oyun grupları.\r\n\r\nÖzetle 15 Temmuz Şehitler Parkı; sadece bir dinlenme alanı değil, çevre kirliliği yaratan atıl bir bölgenin halka kazandırıldığı çok başarılı bir çevre düzenleme projesidir.','','38.14661067215807','39.45390046969794','uploads/places/default.jpg','','','Diyarbakır\'ın Çermik ilçesinde bulunan 15 Temmuz Şehitler Parkı, ilçenin en önemli sosyal donatı ve mesire alanlarından biridir. Parkın genel özellikleri ve öne çıkan tarihi dönüşümü hakkında bilmen gereken detaylar şunlardır:\r\n\r\nGenel Bilgi ve Konum\r\nKonum: Çermik ilçesinin kuzey kısmında, Tepe Mahallesi sınırları içerisinde ve Çüngüş karayolu üzerinde yer almaktadır.\r\n\r\nSeyirtepe Özelliği: Konumu itibarıyla ilçeye yüksekten bakan bir noktada bulunduğu için halk arasında \"Seyirtepe\" olarak da adlandırılır. Bu sayede ziyaretçilerine muazzam bir ilçe ve doğa manzarası sunar.\r\n\r\nBüyüklük: Yaklaşık 33,7 dönümlük (33.700 metrekare) devasa bir alan üzerine kurulmuştur ve bu yönüyle ilçeye kazandırılmış en büyük parklardan biridir.\r\n\r\nÇarpıcı Bir \"Kentsel Dönüşüm\" Hikayesi\r\nParkın bulunduğu alan, yapımından önce yaklaşık 30 yıl boyunca ilçenin vahşi çöp döküm alanı olarak kullanılıyordu. Yayılan ağır koku ve duman hem Çermik hem de Çüngüş halkını ciddi şekilde rahatsız ediyordu.\r\n\r\n2017 Yılındaki Proje: Çermik Belediyesi, 2017 yılında çöp alanını ilçe merkezinden 1,5 kilometre uzağa taşıyarak bu 30 yıllık sorunu çözdü.\r\n\r\nMühendislik Çalışması: Eski çöp alanının parka dönüştürülmesi sürecinde, toprak altında metan gazı sıkışması ve patlaması riskine karşı uzman mühendislerce özel altyapı ve gaz tahliye çalışmaları yapıldı. Ardından bu alan rehabilite edilerek bugünkü modern görünümüne kavuşturuldu.\r\n\r\nParkın İçerisindeki Sosyal Alanlar ve Donanımlar\r\nİlçe halkının ve Çermik\'e gelen turistlerin nefes alabileceği bir yaşam merkezi olarak tasarlanan parkın içerisinde şu imkanlar bulunmaktadır:\r\n\r\nManzara ve Seyir Alanları: İlçeyi kuşbakışı izleme fırsatı veren seyir noktaları.\r\n\r\nYürüyüş Yolları: Sabah sporları ve akşam yürüyüşleri için düzenlenmiş geniş parkurlar.\r\n\r\nPiknik Alanları: Ailelerin hafta sonları vakit geçirebileceği, doğayla iç içe dinlenme alanları.\r\n\r\nKafeterya: Ziyaretçilerin yeme-içme ihtiyaçlarını karşılayabileceği sosyal tesis.\r\n\r\nÇocuk Oyun Parkları: Çocukların güvenle vakit geçirebileceği geniş oyun grupları.\r\n\r\nÖzetle 15 Temmuz Şehitler Parkı; sadece bir dinlenme alanı değil, çevre kirliliği yaratan atıl bir bölgenin halka kazandırıldığı çok başarılı bir çevre düzenleme projesidir.',NULL,0,'2026-02-23 09:09:22'),(28,'15 Temmuz Parkı','','','','','','uploads/places/default.jpg','','','',NULL,0,'2026-02-23 09:11:13'),(30,'15 Temmuz Parkı','ParkAndGarden','Yaklaşık 33,7 dönümlük (33.700 metrekare) devasa bir alan üzerine kurulmuştur ve bu yönüyle ilçeye kazandırılmış en büyük parklardan biridir.\r\nManzara ve Seyir Alanları: İlçeyi kuşbakışı izleme fırsatı veren seyir noktaları.\r\n\r\nYürüyüş Yolları: Sabah sporları ve akşam yürüyüşleri için düzenlenmiş geniş parkurlar.\r\n\r\nPiknik Alanları: Ailelerin hafta sonları vakit geçirebileceği, doğayla iç içe dinlenme alanları.\r\n\r\nKafeterya: Ziyaretçilerin yeme-içme ihtiyaçlarını karşılayabileceği sosyal tesis.\r\n\r\nÇocuk Oyun Parkları: Çocukların güvenle vakit geçirebileceği geniş oyun grupları.','','38.14660223464918','39.45391119853282','uploads/places/default.jpg','','','Diyarbakır\'ın Çermik ilçesinde bulunan 15 Temmuz Şehitler Parkı, ilçenin en önemli sosyal donatı ve mesire alanlarından biridir. Parkın genel özellikleri ve öne çıkan tarihi dönüşümü hakkında bilmen gereken detaylar şunlardır:\r\n\r\nGenel Bilgi ve Konum\r\nKonum: Çermik ilçesinin kuzey kısmında, Tepe Mahallesi sınırları içerisinde ve Çüngüş karayolu üzerinde yer almaktadır.\r\n\r\nSeyirtepe Özelliği: Konumu itibarıyla ilçeye yüksekten bakan bir noktada bulunduğu için halk arasında \"Seyirtepe\" olarak da adlandırılır. Bu sayede ziyaretçilerine muazzam bir ilçe ve doğa manzarası sunar.\r\n\r\nBüyüklük: Yaklaşık 33,7 dönümlük (33.700 metrekare) devasa bir alan üzerine kurulmuştur ve bu yönüyle ilçeye kazandırılmış en büyük parklardan biridir.\r\n\r\nÇarpıcı Bir \"Kentsel Dönüşüm\" Hikayesi\r\nParkın bulunduğu alan, yapımından önce yaklaşık 30 yıl boyunca ilçenin vahşi çöp döküm alanı olarak kullanılıyordu. Yayılan ağır koku ve duman hem Çermik hem de Çüngüş halkını ciddi şekilde rahatsız ediyordu.\r\n\r\n2017 Yılındaki Proje: Çermik Belediyesi, 2017 yılında çöp alanını ilçe merkezinden 1,5 kilometre uzağa taşıyarak bu 30 yıllık sorunu çözdü.\r\n\r\nMühendislik Çalışması: Eski çöp alanının parka dönüştürülmesi sürecinde, toprak altında metan gazı sıkışması ve patlaması riskine karşı uzman mühendislerce özel altyapı ve gaz tahliye çalışmaları yapıldı. Ardından bu alan rehabilite edilerek bugünkü modern görünümüne kavuşturuldu.\r\n\r\nParkın İçerisindeki Sosyal Alanlar ve Donanımlar\r\nİlçe halkının ve Çermik\'e gelen turistlerin nefes alabileceği bir yaşam merkezi olarak tasarlanan parkın içerisinde şu imkanlar bulunmaktadır:\r\n\r\nManzara ve Seyir Alanları: İlçeyi kuşbakışı izleme fırsatı veren seyir noktaları.\r\n\r\nYürüyüş Yolları: Sabah sporları ve akşam yürüyüşleri için düzenlenmiş geniş parkurlar.\r\n\r\nPiknik Alanları: Ailelerin hafta sonları vakit geçirebileceği, doğayla iç içe dinlenme alanları.\r\n\r\nKafeterya: Ziyaretçilerin yeme-içme ihtiyaçlarını karşılayabileceği sosyal tesis.\r\n\r\nÇocuk Oyun Parkları: Çocukların güvenle vakit geçirebileceği geniş oyun grupları.\r\n\r\nÖzetle 15 Temmuz Şehitler Parkı; sadece bir dinlenme alanı değil, çevre kirliliği yaratan atıl bir bölgenin halka kazandırıldığı çok başarılı bir çevre düzenleme projesidir.',NULL,0,'2026-02-23 09:19:19'),(31,'Millet Parkı','ParkAndGarden','Çermik kaplıcalar bölgesinde (Hamambaşı mevkii) yapılan park ve rekreasyon düzenlemeleri, bölgeyi sadece şifa aranan bir yer olmaktan çıkarıp modern bir dinlenme ve sosyal yaşam alanına dönüştürmeyi amaçlıyor.\r\n\r\nUygulaman olan \"Çermik Yerel Rehber\" için bu bölgeyi şu başlıklarla tanımlayabilirsin:\r\n\r\n1. Bölgenin Yeni Yüzü: Rekreasyon ve Peyzaj\r\nKaplıca bölgesindeki park, tarihi hamamlar ile modern termal oteller arasında bir köprü görevi görüyor.\r\n\r\nYeşil Odaklı Tasarım: Betonlaşmanın önüne geçmek için geniş çim alanlar, süs havuzları ve bölgenin iklimine uygun ağaçlandırma çalışmaları yapılmıştır.\r\n\r\nDinlenme Alanları: Kaplıca sonrası vücudun dinlenmesi için tasarlanmış gölgelikli kamelyalar ve banklar parkın ana unsurlarıdır.\r\n\r\n2. Sinek Çayı ile Bütünleşik Yapı\r\nBu park projesinin en büyük özelliği, hemen yanı başından geçen Sinek Çayı ile görsel ve fiziksel bir bağ kurmasıdır.\r\n\r\nYürüyüş Parkurları: Çay kenarı boyunca uzanan yürüyüş yolları, termal tatilciler için doğa içinde spor yapma imkanı sunar.\r\n\r\nManzara Seyir Noktaları: Park içerisinden tarihi Haburman Köprüsü ve çevresindeki doğal kayalıkları izleyebileceğiniz özel seyir terasları bulunur.\r\n\r\n3. Sosyal Donatılar\r\nParkın içinde ailelerin ve çocukların vakit geçirebileceği şu alanlar öne çıkar:\r\n\r\nModern Çocuk Parkları: Kaplıca bölgesine gelen ailelerin çocukları için güvenli oyun alanları.\r\n\r\nSpor Aletleri: Açık havada fitness yapmaya olanak sağlayan spor parkurları.\r\n\r\nIşıklandırma: Akşam saatlerinde de kaplıca bölgesinin canlı kalmasını sağlayan modern ve estetik gece aydınlatmaları.','','38.140750591684245','39.479059414538426','uploads/places/default.jpg','','','Çermik kaplıcalar bölgesinde (Hamambaşı mevkii) yapılan park ve rekreasyon düzenlemeleri, bölgeyi sadece şifa aranan bir yer olmaktan çıkarıp modern bir dinlenme ve sosyal yaşam alanına dönüştürmeyi amaçlıyor.\r\n\r\nUygulaman olan \"Çermik Yerel Rehber\" için bu bölgeyi şu başlıklarla tanımlayabilirsin:\r\n\r\n1. Bölgenin Yeni Yüzü: Rekreasyon ve Peyzaj\r\nKaplıca bölgesindeki park, tarihi hamamlar ile modern termal oteller arasında bir köprü görevi görüyor.\r\n\r\nYeşil Odaklı Tasarım: Betonlaşmanın önüne geçmek için geniş çim alanlar, süs havuzları ve bölgenin iklimine uygun ağaçlandırma çalışmaları yapılmıştır.\r\n\r\nDinlenme Alanları: Kaplıca sonrası vücudun dinlenmesi için tasarlanmış gölgelikli kamelyalar ve banklar parkın ana unsurlarıdır.\r\n\r\n2. Sinek Çayı ile Bütünleşik Yapı\r\nBu park projesinin en büyük özelliği, hemen yanı başından geçen Sinek Çayı ile görsel ve fiziksel bir bağ kurmasıdır.\r\n\r\nYürüyüş Parkurları: Çay kenarı boyunca uzanan yürüyüş yolları, termal tatilciler için doğa içinde spor yapma imkanı sunar.\r\n\r\nManzara Seyir Noktaları: Park içerisinden tarihi Haburman Köprüsü ve çevresindeki doğal kayalıkları izleyebileceğiniz özel seyir terasları bulunur.\r\n\r\n3. Sosyal Donatılar\r\nParkın içinde ailelerin ve çocukların vakit geçirebileceği şu alanlar öne çıkar:\r\n\r\nModern Çocuk Parkları: Kaplıca bölgesine gelen ailelerin çocukları için güvenli oyun alanları.\r\n\r\nSpor Aletleri: Açık havada fitness yapmaya olanak sağlayan spor parkurları.\r\n\r\nIşıklandırma: Akşam saatlerinde de kaplıca bölgesinin canlı kalmasını sağlayan modern ve estetik gece aydınlatmaları.',NULL,0,'2026-02-24 10:36:49'),(32,'Ömer Halisdemir Parkı','ParkAndGarden','1. Bölgenin Yeni Yüzü: Rekreasyon ve Peyzaj\r\nKaplıca bölgesindeki park, tarihi hamamlar ile modern termal oteller (Gıran Park, Helinamin vb.) arasında bir köprü görevi görüyor.\r\n\r\nYeşil Odaklı Tasarım: Betonlaşmanın önüne geçmek için geniş çim alanlar, süs havuzları ve bölgenin iklimine uygun ağaçlandırma çalışmaları yapılmıştır.\r\n\r\nDinlenme Alanları: Kaplıca sonrası vücudun dinlenmesi için tasarlanmış gölgelikli kamelyalar ve banklar parkın ana unsurlarıdır.\r\n\r\n2. Sinek Çayı ile Bütünleşik Yapı\r\nBu park projesinin en büyük özelliği, hemen yanı başından geçen Sinek Çayı ile görsel ve fiziksel bir bağ kurmasıdır.\r\n\r\nYürüyüş Parkurları: Çay kenarı boyunca uzanan yürüyüş yolları, termal tatilciler için doğa içinde spor yapma imkanı sunar.\r\n\r\nManzara Seyir Noktaları: Park içerisinden tarihi Haburman Köprüsü ve çevresindeki doğal kayalıkları izleyebileceğiniz özel seyir terasları bulunur.\r\n\r\n3. Sosyal Donatılar\r\nParkın içinde ailelerin ve çocukların vakit geçirebileceği şu alanlar öne çıkar:\r\n\r\nModern Çocuk Parkları: Kaplıca bölgesine gelen ailelerin çocukları için güvenli oyun alanları.\r\n\r\nSpor Aletleri: Açık havada fitness yapmaya olanak sağlayan spor parkurları.\r\n\r\nIşıklandırma: Akşam saatlerinde de kaplıca bölgesinin canlı kalmasını sağlayan modern ve estetik gece aydınlatmaları.\r\n\r\nUygulaman İçin İpucu (Kullanıcı Rehberi)\r\nUygulamanda bu parkı anlatırken \"Kaplıca Sonrası Terapi\" başlığını kullanabilirsin.\r\n\r\n“Şifalı sulardan çıktıktan sonra Sinek Çayı’nın sesi eşliğinde parkta yürüyüş yapmak, termal tedavinin etkisini ikiye katlar.','','38.1298891060308','39.44687391486745','uploads/places/default.jpg','','','1. Bölgenin Yeni Yüzü: Rekreasyon ve Peyzaj\r\nKaplıca bölgesindeki park, tarihi hamamlar ile modern termal oteller (Gıran Park, Helinamin vb.) arasında bir köprü görevi görüyor.\r\n\r\nYeşil Odaklı Tasarım: Betonlaşmanın önüne geçmek için geniş çim alanlar, süs havuzları ve bölgenin iklimine uygun ağaçlandırma çalışmaları yapılmıştır.\r\n\r\nDinlenme Alanları: Kaplıca sonrası vücudun dinlenmesi için tasarlanmış gölgelikli kamelyalar ve banklar parkın ana unsurlarıdır.\r\n\r\n2. Sinek Çayı ile Bütünleşik Yapı\r\nBu park projesinin en büyük özelliği, hemen yanı başından geçen Sinek Çayı ile görsel ve fiziksel bir bağ kurmasıdır.\r\n\r\nYürüyüş Parkurları: Çay kenarı boyunca uzanan yürüyüş yolları, termal tatilciler için doğa içinde spor yapma imkanı sunar.\r\n\r\nManzara Seyir Noktaları: Park içerisinden tarihi Haburman Köprüsü ve çevresindeki doğal kayalıkları izleyebileceğiniz özel seyir terasları bulunur.\r\n\r\n3. Sosyal Donatılar\r\nParkın içinde ailelerin ve çocukların vakit geçirebileceği şu alanlar öne çıkar:\r\n\r\nModern Çocuk Parkları: Kaplıca bölgesine gelen ailelerin çocukları için güvenli oyun alanları.\r\n\r\nSpor Aletleri: Açık havada fitness yapmaya olanak sağlayan spor parkurları.\r\n\r\nIşıklandırma: Akşam saatlerinde de kaplıca bölgesinin canlı kalmasını sağlayan modern ve estetik gece aydınlatmaları.\r\n\r\nUygulaman İçin İpucu (Kullanıcı Rehberi)\r\nUygulamanda bu parkı anlatırken \"Kaplıca Sonrası Terapi\" başlığını kullanabilirsin.\r\n\r\n“Şifalı sulardan çıktıktan sonra Sinek Çayı’nın sesi eşliğinde parkta yürüyüş yapmak, termal tedavinin etkisini ikiye katlar.',NULL,0,'2026-02-24 10:43:24'),(33,'Sütçü İmam Parkı','ParkAndGarden','Konum ve Tarihçe\r\nEski Meteoroloji Alanı: Park, ilçedeki eski meteoroloji istasyonunun bulunduğu yaklaşık 4.000 metrekarelik alan üzerine inşa edilmiştir.\r\n\r\nKardeşlik Köprüsü: Kahramanmaraş ve Çermik arasındaki kardeşlik bağını simgeler; bu yüzden ismi Kurtuluş Savaşı kahramanı Sütçü İmam’dan gelmektedir.\r\n\r\nParkın Öne Çıkan Özellikleri\r\nBu park, yapıldığı dönemde \"7 yıldızlı park\" olarak nitelendirilmiş ve modern peyzajıyla ilçeye yeni bir soluk getirmiştir:\r\n\r\nPeyzaj ve Yeşil Alan: Yoğun ağaçlandırma ve mevsimlik çiçeklerle donatılmış, dinlendirici bir atmosfer sunar.\r\n\r\nÇocuk ve Spor: İçerisinde standart çocuk oyun grupları ve yetişkinler için açık hava spor aletleri mevcuttur.\r\n\r\nOturma Alanları: Ailelerin vakit geçirebileceği kamelyalar (pergola) ve modern oturma bankları alanın her yerine yayılmıştır.\r\n\r\nKullanım Amacı\r\nÖzellikle mahalle aralarında kalan bir park olduğu için yerel halkın akşamüstü çaylarını içtiği, çocukların güvenle oynadığı huzurlu bir dinlenme noktası olarak bilinir.','','38.13697114904886','39.46431470457382','uploads/places/default.jpg','','','Konum ve Tarihçe\r\nEski Meteoroloji Alanı: Park, ilçedeki eski meteoroloji istasyonunun bulunduğu yaklaşık 4.000 metrekarelik alan üzerine inşa edilmiştir.\r\n\r\nKardeşlik Köprüsü: Kahramanmaraş ve Çermik arasındaki kardeşlik bağını simgeler; bu yüzden ismi Kurtuluş Savaşı kahramanı Sütçü İmam’dan gelmektedir.\r\n\r\nParkın Öne Çıkan Özellikleri\r\nBu park, yapıldığı dönemde \"7 yıldızlı park\" olarak nitelendirilmiş ve modern peyzajıyla ilçeye yeni bir soluk getirmiştir:\r\n\r\nPeyzaj ve Yeşil Alan: Yoğun ağaçlandırma ve mevsimlik çiçeklerle donatılmış, dinlendirici bir atmosfer sunar.\r\n\r\nÇocuk ve Spor: İçerisinde standart çocuk oyun grupları ve yetişkinler için açık hava spor aletleri mevcuttur.\r\n\r\nOturma Alanları: Ailelerin vakit geçirebileceği kamelyalar (pergola) ve modern oturma bankları alanın her yerine yayılmıştır.\r\n\r\nKullanım Amacı\r\nÖzellikle mahalle aralarında kalan bir park olduğu için yerel halkın akşamüstü çaylarını içtiği, çocukların güvenle oynadığı huzurlu bir dinlenme noktası olarak bilinir.',NULL,0,'2026-02-24 10:49:40');
/*!40000 ALTER TABLE `places` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Çermik Tavası',150.00,NULL,'Meşhur yerel tava yemeği.'),(2,1,'Kuyulu Kebap',180.00,NULL,'Geleneksel kuyu kebabı.'),(3,2,'Standart Oda',1200.00,NULL,'Kahvaltı dahil çift kişilik oda.'),(4,2,'King Suite',2500.00,NULL,'Lüks suit oda.'),(5,3,'MEFTÜNE',400.00,'uploads/products/food_1771835371_7261.gif','YOKTUR'),(7,4,'Standart Oda',3500.00,'uploads/products/food_1771930703_8113.jpg','\r\nÜcretsiz kablosuz bağlantı\r\n\r\nÜcretsiz kahvaltı\r\n\r\nÜcretsiz park alanı\r\n\r\nEngellilere uygun\r\n\r\nKapalı havuz\r\n\r\nKlimalı\r\n\r\nÇamaşır yıkama hizmeti\r\n\r\nOda servisi\r\n\r\nÇocuklar için uygun\r\n\r\nRestoran\r\n\r\nKüvet\r\n\r\nSpa\r\n\r\nSpor salonu\r\n\r\nBar\r\n ');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `road_stops`
--

DROP TABLE IF EXISTS `road_stops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `road_stops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_place_id` int(11) DEFAULT NULL,
  `poi_name` varchar(255) NOT NULL,
  `trigger_radius` int(11) DEFAULT '100',
  `audio_script` text,
  PRIMARY KEY (`id`),
  KEY `parent_place_id` (`parent_place_id`),
  CONSTRAINT `road_stops_ibfk_1` FOREIGN KEY (`parent_place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `road_stops`
--

LOCK TABLES `road_stops` WRITE;
/*!40000 ALTER TABLE `road_stops` DISABLE KEYS */;
INSERT INTO `road_stops` VALUES (8,14,'Kaya Silüetleri Başlangıcı',150,'Sağ tarafınızdaki kayalara bakın, sanki bir insan dizisi gibi duruyorlar değil mi? Efsaneye göre burası bir gelin alayıdır.'),(9,14,'Zirve Manzara Noktası',100,'Harika bir manzara! Buradan tüm Çermik ayaklarınızın altında. Derinden bir nefes alın.'),(10,15,'Vadi Girişi',200,'Kanyona girmek üzeresiniz. Havanın serinlediğini hissetmeye başlayacaksınız.'),(11,15,'Ana Şelale Görüş Alanı',50,'İşte o muazzam ses! Şeyhandede Şelalesi tüm ihtişamı ile karşınızda.'),(12,16,'Tarihi Sinek Köprüsü',100,'Bu eski köprü, Sinek Çayı\'nın en karakteristik yapılarından biridir. Fotoğraf çekmek için durabilirsiniz.'),(13,16,'Şelale Yanı Piknik Alanı',100,'Burada bir mola verip Sinek Çayı\'nın buz gibi suyunda karpuz çatlatmaya ne dersiniz?'),(14,17,'Antik Basamaklar',50,'Binlerce yıl önce buradan kervanların geçtiğini hayal edin. Bu taş basamaklar o günlerden kalma.'),(15,17,'Ticaret Yolu Kavşağı',100,'Burası doğu ile batıyı bağlayan ana damarlardan biriydi.'),(16,18,'Gözeler Mevkii',100,'Suyun yeraltından kaynadığı noktaya çok yaklaştınız. Su burada en saf halindedir.'),(17,18,'Mağaralar Bölgesi',150,'Solunuzdaki yamaçlarda keşfedilmeyi bekleyen birçok gizemli mağara bulunuyor.');
/*!40000 ALTER TABLE `road_stops` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `status` tinyint(4) DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (5,'Çermik Belediyesi Melike Belkıs Kaplıcalarını Yenileyerek Modern Hale Getirdik.','2016 YILINDA HİZMETE GELDİĞİMİZ GÜNDEN İTİBAREN ESKİ HALDE OLAN KAPLICALARIMIZI MODERN HALE GETİREREK HALKMIZIN HİZMETİNE SUNDUK.\r\n\r\n',1,'uploads/projects/1772008365_saat kulesi.jpg'),(6,'Doğalgaz ','Şimdiye kadar kömür ve diğer katı atıklar ile ilçemiz halkının ısınma ve diğer ticari faaliyet alanlarında kullandığı kötü koku ve hava kirliliğine neden olan yakıt türlerini son vermek için enerji ve tabii kaynaklar bakanlığı ile yapmış olduğumuz görüşmeler neticesinde halkımıza söz verdiğimiz gibi doğal gaz alt yapı hizmetlerini tamamlayarak ilçe halkımızın hizmetine sunduk.',1,'uploads/projects/1772011283_WhatsApp Image 2019-03-19 at 15.54.38.jpeg'),(7,'İçme Suyu','İlçe merkezimizde 50 yılı aşkın süredir kullanımı gerçekleştirilen sağlıksız aspes borular ile hane ve iş yerlerine giden içme suyu şebekemizin alt yapısının tamamını Diyarbakır Büyükşehir Belediyesine bağlı DİSKİ genel Müdürlüğü ile yapılan mutabakatlar sonucunda içme suyu şebekesini yenileterek halkımızın hizmetine sunduk.\r\n\r\n',1,'uploads/projects/1772011493_hikaye kapak.jpg'),(8,'Hizmet Binası','1960 yılından bu yana bir çok belediye başkanı tarafından kullanılan günümüz şartlarından halkımıza hizmet konusunda eksik kalan hizmet binamızı daha modern hale getirerek yeniledik.\r\n\r\n',1,''),(9,'Sokak Sağlıklaştırma','İlçemizin Merkez tarihi çarşısında hizmet veren işyerlerinin görüntü kirliği oluşturduğu bu doğrultuda daha modern hizmet verecek iş yerleri haline getirerek Diyarbakır Büyükşehir Belediyesinin katkıları ile halkımız esnafları caddesinin çehresini değiştirdik.',1,''),(10,'Kuruyemiş Pazarı','İlçemizde yıllardır hizmet veren kuruyemiş Pazar esnafları ile yapılan istişare sonucunda esnaf ve sanatkarlarımızın talepleri neticesinde Çermik Belediyesi olarak yapmış olduğumuz proje ile iş yerlerini daha konforlu hale getirip esnaf ve halkımızın hizmetine sunduk.',1,''),(11,'Halı Saha','Başta gençlerimiz ve tüm spor severerler için yapmış olduğumuz halı saha projelerimizi spor toto ile imzalamış olduğumuz protokollerle 2 adet ilçe Merkezimizde 10 adet Kırsal Mahallelerimizde olmak üzere 12 adet halı sahanın yapımını gerçekleştirdik.',1,''),(12,'Işıklandırma','İlçemiz Tepe Mahallesi Belediye hizmet binası önünden Meslek yüksek okulu mevkisine kadar alt yapı ve üst yapı çalışmalarını bitirmiş olduğumuz caddemize modern cadde-sokak aydınlatmaları ile ilçemize ayrı bir hava kattık.\r\n\r\n',1,''),(13,'Park ve Bahçeler','Göreve geldiğimiz 2016 yılından yapmış olduğumuz izlenim sonucunda ilçemizin büyük eksikliklerinden olan çocuk oyun alanı , yetişkin spor alanı ve yeşil alanların eksikliği görülmüştür. Bu eksikliklerin tamamlanması için uzun yıllardır ilçemize kötü bir imaj veren katı atık toplama alanının yerini değiştirerek mesire alanına çevirdik.Bunun akabininde ilçemizin farklı noktalarına park ve bahçeler kurararak , gerekli personeller ile gerek temizlik gerek güvenlik önlemleri alarak halkımızın hizmetine sunduk.',1,''),(14,'TOKİ','Uzun yıllardır talep edilip bir türlü ilçemize gelmesi hayal olan toki konutları için dönemin Çevre Şehircilik Bakanı Sn.Murat KURUM ile yapılan ikili görüşmeler sonucunda ilçe merkezimizde 158 adet Toplu Konut inşaasını ilçemize kazandırdık.',1,''),(15,'Kültür ve Sanat','Günümüzün gençliğine verdiğimiz önem neticesinde eksiklik olarak görülen fuar ve etkinlik alanında önemli çalışmalar yaparak bir çok etkinliğe öncülük ettik. Çermik Kitap Fuarı Konser ve aktiviteler Şehir gezileri',1,''),(16,'Kasaplar Sebze Pazarı','Kasaplar bölgesi ve sebze pazarının alt yapı ve üst yapılarını tamamlayarak üstünün tamamını yağmur güneş ve olumsuz hava şartlarına karşı kapatılmasını sağladık.',1,''),(17,'Devlet Hastanesi','İlçemizde yıllardır vatandaşlarımızın kanayan yanası olan hastane ihtiyacını karşılamak için çevre ilçe ve illere giderek hastalıklarına derman arıyorlardı. Halkımızın bu sorununun çözümü için İlçemize yeni bir devlet hastanesi yapılması için ilgili kurumlarla yapılan istişareler sonucunda ilçemize Devlet hastanesi kazandırdık.',1,''),(18,'Gelincik Dağı , Dravşa Yolu','Gelincikdağı , dravşa yolunu tamamen parke taşı yaparak konumda yaşayan halkımız ve ilçe dışından turizm seyahatine gelen misafirlerimiz için ulaşıma kolay hale getirdik.',1,''),(19,'Tarihi Saray Hamamı','Tarihi hamamızı atıl durumundan kurtararak yeniden restorasyon sürecini başlattık. Tarihi geçmişimize yakışır bir hale getirdik.',1,''),(20,'Hayvan Pazarı ve Mezbahane','',1,''),(21,'İlçemiz girişinde Siverek - Diyarbakır yolu kavşağında saat kulesi.','',1,''),(22,'Fen ve Sanat Yapıları','Kırsal mahallerimizde yolların bozulmasına sebep olan dere yatakları yağmur sularını deşarj edebilmek için sanat yapıları ( düz boru,menfez ) çalışmaları yaparak kırsal mahallelerimizin sorunlarını çözüme kavuşturduk.',0,''),(23,'Sıcak Asfalt ve tretuar','İlçe merkezinde sıcak asfalt ve tretuar çalışmaları yaparak ana yollar üzerindeki iklimsel sorunları tamamen ortadan kaldırdık.',0,''),(24,'Kamulaştırma','Merkez mahallerimizde araçların girmekte sorun yaşadığı , bazı bölgelerde yolların tamamen tıkanmasına sebep olan ev ve arsaları kamulaştırma yaparak halkımızın hizmetine sunduk.',0,''),(25,'Kilitli Parke Taşı','İlçemiz merkezinde alt yapı ve üst yapı çalışmalarını tamamladığımız cadde ve sokaklarımızda kilitli parke taşı döşeyerek vatandaşlarımızın hizmetine sunduk. Kırsal mahallelerimizin bir çoğunda görülen eksiklikler sonucunda kilitli parke taşı yol yapım işlemlerimize başladık ve devam ediyoruz.',0,'');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'mayor_name','Şeyhmus Karamehmetoğlu','2026-02-24 10:38:41'),(2,'mayor_title','Belediye Başkanı','2026-02-24 10:38:41'),(3,'mayor_image','assets/img/baskan.png','2026-02-24 10:40:48');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `image_path` varchar(255) DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (1,2,'Seşaöm','haburman köprüsü','uploads/submissions/699c338d3335a.png',1,'2026-02-23 11:01:33');
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_visits`
--

DROP TABLE IF EXISTS `user_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `place_id` int(11) DEFAULT NULL,
  `visit_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `place_id` (`place_id`),
  CONSTRAINT `user_visits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_visits_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_visits`
--

LOCK TABLES `user_visits` WRITE;
/*!40000 ALTER TABLE `user_visits` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `activation_code` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_image` varchar(255) DEFAULT 'assets/img/default-avatar.png',
  `last_login_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,NULL,NULL,NULL,'biltektasarim@gmail.com','5327104230','$2y$10$5X9ndeLXCMfNrFYkUN7LFulEBflpF/TYktTfM2Cfv.Xv6peAE8SCW','4dfd191b534eb83f41d01f3bc295c1ce',0,'2026-02-23 08:15:51','assets/img/default-avatar.png',NULL),(2,NULL,NULL,NULL,NULL,'admin@admin.com',NULL,'$2y$10$u7M/Dy36vrEy/ZNM9eIoZO9kH.yQBCCGnHPoQGV2W3EqAAwU2l25m',NULL,1,'2026-02-23 10:57:10','uploads/avatars/avatar_2_1771844307.png','2026-02-25 09:57:37');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-25 14:18:01
