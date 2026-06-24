<?php
header('Content-Type: application/json');
require_once '../config.php';
session_start();

$question = $_GET['q'] ?? '';
$lat = $_GET['lat'] ?? 0;
$lng = $_GET['lng'] ?? 0;
$page_context = $_GET['context'] ?? '';

// Genel Bilgileri Çek
$yerel_bilgi = "";
$stmt_info = $pdo->query("SELECT baslik, icerik FROM genel_bilgiler");
$infos = $stmt_info->fetchAll();
foreach ($infos as $info) {
    $yerel_bilgi .= "Konu: " . $info['baslik'] . " - Bilgi: " . $info['icerik'] . "\n";
}

// Hastane Bilgilerini Çek
$stmt_hosp = $pdo->query("SELECT name, description FROM hospitals");
$hospitals = $stmt_hosp->fetchAll();
foreach ($hospitals as $h) {
    $yerel_bilgi .= "Hastane: " . $h['name'] . " - Detay: " . $h['description'] . "\n";
}

// Çermik Rehberliği için Sistem Talimatı
$system_instruction = "Sen Çermik belediyesinin dijital rehberisin. Nazik, yardımsever ve samimi bir dille konuşursun. Kullanıcının koordinatlarına göre en yakın yerleri biliyorsun. \n\nŞu anki sayfa bağlamı: " . $page_context . "\n\nÇermik Hakkında Genel Bilgiler:\n" . $yerel_bilgi;

// 1. Önce sayfa bağlamını (Context) kontrol et
$context_info = null;
if ($page_context && $page_context !== 'Genel Çermik Rehberi') {
    $stmt_ctx = $pdo->prepare("SELECT * FROM places WHERE name = ? LIMIT 1");
    $stmt_ctx->execute([$page_context]);
    $context_info = $stmt_ctx->fetch();
}

// 2. Soru içindeki anahtar kelimeleri ara
$keyword_match = null;
$q_lower = mb_strtolower($question, 'UTF-8');
if (!empty($question)) {
    // Özel durumlar (Örn: kaplıca kelimesi geçiyorsa doğrudan kaplıcayı getir)
    if (mb_strpos($q_lower, 'kaplıca') !== false) {
        $stmt_match = $pdo->prepare("SELECT * FROM places WHERE category = 'HotSpring' LIMIT 1");
        $stmt_match->execute();
        $keyword_match = $stmt_match->fetch();
    }
    
    // Genel mekan araması (Soru içinde mekan adı geçiyor mu?)
    if (!$keyword_match) {
        // Tüm mekanları çek ve soru içinde geçip geçmediğine bak
        $stmt_all = $pdo->query("SELECT * FROM places");
        $all_places = $stmt_all->fetchAll();
        $best_match = null;
        $max_len = 0;
        
        foreach ($all_places as $p) {
            $p_name = mb_strtolower($p['name'], 'UTF-8');
            if (mb_strpos($q_lower, $p_name) !== false) {
                // En uzun eşleşmeyi seç (Örn: "Ulu Cami" vs "Cami")
                if (mb_strlen($p_name) > $max_len) {
                    $max_len = mb_strlen($p_name);
                    $best_match = $p;
                }
            }
        }
        $keyword_match = $best_match;
    }
    
    // Eğer mekanlarda yoksa hastanelerde ara
    if (!$keyword_match) {
        $stmt_h_match = $pdo->prepare("SELECT * FROM hospitals WHERE ? LIKE CONCAT('%', LOWER(name), '%') LIMIT 1");
        $stmt_h_match->execute([$q_lower]);
        $keyword_match = $stmt_h_match->fetch();
        if ($keyword_match) $keyword_match['is_hospital'] = true;
    }
}

// 3. Mevcut konuma göre en yakın yeri bul (Fallback)
$stmt_near = $pdo->prepare("SELECT *, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance FROM places ORDER BY distance ASC LIMIT 1");
$stmt_near->execute([$lat, $lng, $lat]);
$nearest_place = $stmt_near->fetch();

// 4. Yanıtı Oluştur ve Filtrele
$ai_answer = "";

// Intent Tanıma (Soruda hastalık/şifa geçiyor mu?)
$is_health_query = false;
$health_keywords = ['iyi gelir', 'hastalık', 'şifa', 'fayda', 'tedavi', 'kür'];
foreach ($health_keywords as $hk) {
    if (mb_strpos($q_lower, $hk) !== false) {
        $is_health_query = true;
        break;
    }
}

// MANTIK SIRALAMASI:
// A. Eğer soru belirli bir mekanı soruyorsa (Keyword Match)
if ($keyword_match) {
    if (isset($keyword_match['is_hospital'])) {
        $ai_answer = $keyword_match['name'] . " hakkında bilgi: " . $keyword_match['description'];
    } else {
        // Eğer kaplıca ise ve hastalık soruluyorsa sadece hastalıkları döndür
        if ($keyword_match['category'] == 'HotSpring' && $is_health_query && !empty($keyword_match['hastaliklar'])) {
            $ai_answer = "Çermik Kaplıcaları şu hastalıklara şifa olmaktadır: " . $keyword_match['hastaliklar'];
        } else {
            $base_info = $keyword_match['ai_context'] ?: $keyword_match['description'];
            // Çok uzunsa ilk 500 karakteri al (Veya cümle bazlı bölme yapılabilir)
            if (mb_strlen($base_info) > 600) {
                $base_info = mb_substr($base_info, 0, 550) . "... (Detaylı bilgi için sayfanın altındaki tarihçe kısmına bakabilirsiniz)";
            }
            $ai_answer = $keyword_match['name'] . " hakkında bilgi: " . $base_info;
        }
    }
} 
// B. Eğer soru genel ama sayfa belli bir mekana aitse (Context)
else if ($context_info && (empty($question) || mb_strlen($question) < 10)) {
    $ai_answer = "Şu an " . $context_info['name'] . " sayfasındasınız. Bu mekan hakkında size her şeyi anlatabilirim. " . mb_substr($context_info['ai_context'], 0, 300);
}
// C. Eğer hiçbir eşleşme yoksa ama bir yere çok yakınsa (GPS)
else if ($nearest_place && $nearest_place['distance'] < 0.2) { 
    $ai_answer = "Şu an " . $nearest_place['name'] . " çok yakınındasınız. " . mb_substr($nearest_place['ai_context'], 0, 300);
}
// D. Varsayılan cevap (Bağlama göre özelleştirilmiş)
else {
    if ($page_context == 'Belediye Hizmetleri ve Projeler') {
        $ai_answer = "Belediyemizin yürüttüğü projeler ve sunduğumuz hizmetler hakkında bilgi almak ister misiniz? Örneğin 'Evde bakım hizmeti' veya 'Devam eden projeler' diye sorabilirsiniz.";
    } else if ($page_context == 'Çermik Etkinlik ve Duyurular') {
        $ai_answer = "Çermik'teki güncel etkinlikler ve önemli duyurulardan haberdar olmak için bana sorabilirsiniz. Yakında ne etkinlik var biliyorum.";
    } else if ($page_context == 'Nöbetçi Eczane / Hastane') {
        $ai_answer = "Sağlık rehberindesiniz. Çermik Devlet Hastanesi veya acil durumlar hakkında bilgi alabilirsiniz.";
    } else {
        $ai_answer = "Merhaba! Çermik rehberiniz olarak size nasıl yardımcı olabilirim? Kaplıcalarımızdan mı bahsetmemi istersiniz yoksa tarihi mekanlarımızdan mı?";
    }
}

// Log AI Chat
if (isset($_SESSION['user_id'])) {
    $stmt_log = $pdo->prepare("INSERT INTO ai_chat_logs (user_id, question, answer) VALUES (?, ?, ?)");
    $stmt_log->execute([$_SESSION['user_id'], $question, $ai_answer]);
}

echo json_encode([
    'status' => 'success',
    'answer' => $ai_answer
]);
?>
