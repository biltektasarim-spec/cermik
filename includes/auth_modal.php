    <?php 
    if (defined('AUTH_MODAL_INCLUDED')) return; 
    define('AUTH_MODAL_INCLUDED', true); 
    
    // Uygulama (WebView) Tespiti
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $is_android_app = (stripos($ua, 'RotaRehberApp') !== false);
    ?>
    
<!-- Login/Register Modal -->
    <div id="auth-modal" class="modal">
        <div class="modal-content animate-in">
            <div class="modal-close" onclick="app.closeAuthModal ? app.closeAuthModal() : app.toggleAuthModal()">
                <i class="fa-solid fa-times"></i>
            </div>
            <h2 id="auth-title">Giriş Yap</h2>
            <div id="auth-warning" class="auth-alert"></div>
            
            <div id="auth-forms">
                <!-- NEW: Quick Login Form (Primary) -->
                <form id="login-form" onsubmit="event.preventDefault(); app.handleAuth('login')">
                    <input type="text" name="email" placeholder="E-posta veya Telefon" required autocomplete="username">
                    <input type="password" name="password" placeholder="<?php echo __('password'); ?>" required autocomplete="current-password">
                    
                    <div style="text-align: right; margin-bottom: 15px;">
                        <a href="javascript:void(0)" onclick="app.toggleAuthMode('forgot')" style="color: var(--secondary); font-size: 0.85rem; text-decoration: none;"><?php echo __('forgot_password'); ?></a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-right-to-bracket"></i> <?php echo __('login_now'); ?>
                    </button>

                    <p style="margin-top: 15px; font-size: 0.85rem; text-align: center; color: var(--text-secondary);">
                        Henüz hesabınız yok mu? <a href="javascript:void(0)" onclick="app.toggleAuthMode('register')" style="color: var(--secondary); font-weight: 700; text-decoration: none;">Kayıt Ol</a>
                    </p>
                    <p style="margin-top: 10px; font-size: 0.8rem; text-align: center; color: var(--text-secondary);">
                        veya <a href="javascript:void(0)" onclick="app.toggleAuthMode('quick_login')" style="color: var(--secondary); font-weight: 600; text-decoration: none;">SMS ile Hızlı Giriş</a>
                    </p>
                </form>

                <form id="quick_login_request-form" style="display: none;" onsubmit="event.preventDefault(); app.handleAuth('quick_login_request')">
                    <p style="text-align:center; font-size:0.9rem; margin-bottom:20px; color:var(--text-secondary);">
                        Telefon numaranızı girerek SMS ile hızlıca giriş yapın.
                    </p>
                    <input type="tel" name="phone" placeholder="Telefon Numaranız (05xx...)" required autocomplete="tel">
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Kod Gönder
                    </button>

                    <p style="margin-top: 15px; font-size: 0.85rem; text-align: center; color: var(--text-secondary);">
                        <a href="javascript:void(0)" onclick="app.toggleAuthMode('register')" style="color: var(--secondary); font-weight: 700; text-decoration: none;">Yeni Hesap Oluştur</a>
                        <br><br>
                        <a href="javascript:void(0)" onclick="app.toggleAuthMode('login')" style="color: var(--secondary); font-weight: 700; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Şifre ile Giriş Yap</a>
                    </p>
                </form>

                <form id="register-form" style="display: none;" onsubmit="event.preventDefault(); app.handleAuth('register')">
                    <p style="text-align:center; font-size:0.9rem; margin-bottom:20px; color:var(--text-secondary);">
                        RotaRehber'e hemen üye olun.
                    </p>
                    <div class="form-row">
                        <input type="text" name="first_name" placeholder="Adınız" required>
                        <input type="text" name="last_name" placeholder="Soyadınız" required>
                    </div>
                    <input type="email" name="email" placeholder="E-posta Adresiniz" required>
                    <input type="tel" name="phone" placeholder="Telefon Numaranız (05xx...)" required>
                    
                    <div class="form-row">
                        <select name="city" id="reg-city" required>
                            <option value="">İl Seçin</option>
                        </select>
                        <select name="district" id="reg-district" required>
                            <option value="">İlçe Seçin</option>
                        </select>
                    </div>

                    <input type="password" name="password" placeholder="Şifreniz (En az 6 karakter)" required>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-user-plus"></i> Kayıt Ol
                    </button>

                    <p style="margin-top: 15px; font-size: 0.85rem; text-align: center; color: var(--text-secondary);">
                        Zaten hesabınız var mı? <a href="javascript:void(0)" onclick="app.toggleAuthMode('login')" style="color: var(--secondary); font-weight: 700; text-decoration: none;">Giriş Yap</a>
                    </p>
                </form>

                <form id="forgot-form" style="display: none;" onsubmit="event.preventDefault(); app.handleAuth('forgot')">
                    <p style="text-align:center; font-size:0.9rem; margin-bottom:20px; color:var(--text-secondary);">
                        Şifrenizi sıfırlamak için telefon veya e-posta adresinizi girin.
                    </p>
                    <input type="text" name="identity" placeholder="<?php echo __('email_or_phone'); ?>" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-key"></i> Kod Gönder
                    </button>
                    <p style="margin-top: 25px; font-size: 0.9rem; text-align: center; color: var(--text-secondary);">
                        <a href="#" onclick="app.toggleAuthMode('login')" style="color: var(--secondary); font-weight: 700; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> <?php echo __('back_to_login'); ?></a>
                    </p>
                </form>

                <!-- NEW: Profile Completion Form -->
                <form id="complete_profile-form" style="display: none;" onsubmit="event.preventDefault(); app.handleAuth('complete_profile')">
                    <p style="text-align:center; font-size:0.95rem; margin-bottom:20px; color:var(--secondary); font-weight: 600;">
                        Lütfen Profilinizi Tamamlayın
                    </p>
                    <div class="form-row register-names-row">
                        <input type="text" name="first_name" placeholder="Adınız" required autocomplete="given-name">
                        <input type="text" name="last_name" placeholder="Soyadınız" required autocomplete="family-name">
                    </div>
                    <input type="email" name="email" placeholder="E-posta Adresiniz" required autocomplete="email">
                    <input type="password" name="password" placeholder="Yeni Şifreniz" required autocomplete="new-password">
                    <input type="password" name="password_confirm" placeholder="Şifrenizi Onaylayın" required autocomplete="new-password">

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check-double"></i> Tamamla ve Giriş Yap
                    </button>
                </form>

                <!-- OTP Verification Form -->
                <form id="verify_otp-form" style="display: none;" onsubmit="event.preventDefault(); app.handleAuth('verify_otp')">
                    <input type="hidden" name="user_id" id="otp-user-id">
                    <p style="text-align:center; font-size:0.9rem; margin-bottom:20px; color:var(--text-secondary);">
                        Telefonunuza gelen 6 haneli doğrulama kodunu girin.
                    </p>
                    <input type="number" name="otp_code" placeholder="000000" maxlength="6" style="text-align:center; font-size:1.5rem; letter-spacing:5px;" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-shield-check"></i> Kodu Doğrula
                    </button>
                    <p style="margin-top: 25px; font-size: 0.9rem; text-align: center; color: var(--text-secondary);">
                        <a href="#" onclick="app.toggleAuthMode('quick_login')" style="color: var(--secondary); font-weight: 700; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Vazgeç</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
