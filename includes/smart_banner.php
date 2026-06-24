<style>
#smart-app-banner {
    display: none;
    position: fixed;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 18px;
    padding: 12px 16px;
    z-index: 999999;
    box-shadow: 0 15px 35px rgba(0,0,0,0.6);
    align-items: center;
    justify-content: space-between;
}
.sab-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    object-fit: cover;
}
.sab-text {
    flex: 1;
    margin: 0 12px;
    color: white;
}
.sab-title {
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 2px;
    letter-spacing: 0.5px;
}
.sab-subtitle {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 400;
}
.sab-btn {
    background: linear-gradient(135deg, #00c9ff, #92fe9d);
    color: #000;
    text-decoration: none;
    padding: 8px 18px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    box-shadow: 0 4px 10px rgba(0, 201, 255, 0.3);
}
.sab-close {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 24px;
    padding: 0 0 0 12px;
    cursor: pointer;
    line-height: 1;
}
</style>

<div id="smart-app-banner">
    <img src="<?php echo isset($prefix) ? $prefix : ''; ?>rotarehber2.png" class="sab-icon" alt="RotaRehber Icon">
    <div class="sab-text">
        <div class="sab-title">RotaRehber</div>
        <div class="sab-subtitle" id="sab-desc">Orijinal Mobil Uygulama</div>
    </div>
    <a href="#" id="sab-download-btn" class="sab-btn">İNDİR</a>
    <button class="sab-close" onclick="closeSmartBanner()">&times;</button>
</div>

<script>
function closeSmartBanner() {
    document.getElementById('smart-app-banner').style.display = 'none';
    sessionStorage.setItem('hideSmartBanner', 'true');
}

window.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('hideSmartBanner') === 'true') return;
    
    const banner = document.getElementById('smart-app-banner');
    const downloadBtn = document.getElementById('sab-download-btn');
    const prefix = '<?php echo isset($prefix) ? $prefix : ""; ?>';
    
    const userAgent = navigator.userAgent || navigator.vendor || window.opera;
    
    if (/android/i.test(userAgent)) {
        downloadBtn.href = prefix + 'app.apk';
        downloadBtn.innerText = 'YÜKLE';
        banner.style.display = 'flex';
    } else if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
        // İleride iOS App Store linkini buraya ekleyeceğiz.
        downloadBtn.href = '#'; 
        downloadBtn.innerText = 'APP STORE';
        // banner.style.display = 'flex'; // iOS linki eklenince bu yorum satırını kaldırın
    }
});
</script>
