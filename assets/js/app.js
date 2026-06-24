console.log("%c RotaRehber JS Loaded v6.2", "color: #00c9ff; font-size: 24px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);");
window.app = {
    init: function () {
        console.log("App initialized");
        this.hideSplashScreen();
        this.loadPopularPlaces();
        this.loadAnnouncements();
        this.loadEvents();
        this.loadServices();
        this.loadMunicipalGuide();
        this.updateGPS();
        setInterval(() => this.updateGPS(), 30000);

        // Hava durumu
        this.loadWeather();
        setInterval(() => this.loadWeather(), 1800000);

        this.changeBG('default');

        // Handle URL tab parameter
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab) {
            const tabBtn = document.querySelector(`.nav-item[onclick*="switchTab('${tab}'"]`);
            if (tabBtn) this.switchTab(tab, tabBtn);
        }

        // Handle auth-related URL parameters and clean URL afterwards
        if (urlParams.get('login_error')) {
            // Remove params from URL
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);

            setTimeout(() => {
                this.toggleAuthModal();
                this.showAuthMessage('Giriş yapılırken bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            }, 500);
        }

        this.initCityDistrictDropdowns();
        this.initAnalytics();

        // Bell icon listener (Once)
        const bellBtn = document.getElementById('bell-icon') || document.querySelector('.header-icons .fa-bell');
        if (bellBtn) {
            bellBtn.addEventListener('click', () => {
                console.log("Bell clicked");
                this.toggleAnnouncementsModal();
            });
        }
    },

    hideSplashScreen: function() {
        const splash = document.getElementById('app-splash-screen');
        if (splash) {
            setTimeout(() => {
                splash.style.opacity = '0';
                setTimeout(() => splash.remove(), 500);
            }, 1000);
        }
    },

    vibrate: function(ms = 15) {
        if ('vibrate' in navigator) {
            navigator.vibrate(ms);
        }
    },

    initCityDistrictDropdowns: function () {
        if (typeof turkeyData !== 'undefined') {
            const citySelect = document.getElementById('reg-city');
            if (!citySelect) return;

            const cities = Object.keys(turkeyData).sort((a, b) => a.localeCompare(b, 'tr'));

            const placeholder = document.body.getAttribute('data-select-city') || 'İl Seçiniz';
            citySelect.innerHTML = `<option value="">${placeholder}</option>`;
            cities.forEach(city => {
                const opt = document.createElement('option');
                opt.value = city;
                opt.textContent = city;
                if (city === 'Diyarbakır') opt.selected = true;
                citySelect.appendChild(opt);
            });

            // Initial districts for Diyarbakır (Empty default so it shows placeholder)
            this.updateDistricts('Diyarbakır', '');
            
            // Re-trigger updateDistricts if city changes
            citySelect.onchange = (e) => this.updateDistricts(e.target.value);
        }
    },

    updateDistricts: function (city, defaultDistrict = '') {
        const distSelect = document.getElementById('reg-district');
        if (!distSelect) return;

        const placeholder = document.body.getAttribute('data-select-district') || 'İlçe Seçiniz';
        distSelect.innerHTML = `<option value="">${placeholder}</option>`;

        if (city && turkeyData[city]) {
            turkeyData[city].sort((a, b) => a.localeCompare(b, 'tr')).forEach(dist => {
                const opt = document.createElement('option');
                opt.value = dist;
                opt.textContent = dist;
                if (dist === defaultDistrict) opt.selected = true;
                distSelect.appendChild(opt);
            });
        }
    },

    getApiUrl: function (endpoint) {
        // Use API_BASE if defined, otherwise guess based on path
        let base = window.API_BASE || 'api/';
        
        if (!window.API_BASE) {
            const isPhysicalSub = !window.location.pathname.endsWith('district.php') && 
                                 !window.location.pathname.endsWith('index.php') && 
                                 window.location.pathname.split('/').filter(Boolean).length >= 2;
            base = isPhysicalSub ? '../api/' : 'api/';
        }
        
        const fullUrl = base + endpoint;
        const buster = (fullUrl.includes('?') ? '&' : '?') + 'v=' + Date.now();
        const finalUrl = fullUrl + buster;
        console.log(`[getApiUrl] ${endpoint} -> ${finalUrl}`);
        return finalUrl;
    },

    initAnalytics: function() {
        const businessId = document.body.getAttribute('data-business-id');
        if (businessId) {
            this.trackAnalytics('view', businessId);
        }

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-track-direction]');
            if (btn) {
                const bId = btn.getAttribute('data-track-direction');
                this.trackAnalytics('direction', bId);
            }
        });
    },

    trackAnalytics: function (action, businessId) {
        if (!businessId) return;

        if (action === 'view') {
            const cacheKey = `analytics_view_cooldown_${businessId}`;
            const lastView = localStorage.getItem(cacheKey);
            const now = Date.now();
            if (lastView && (now - parseInt(lastView)) < 86400000) {
                console.log('[Analytics] Bypassed view count due to 24h cooldown');
                return;
            }
            localStorage.setItem(cacheKey, now.toString());
        }

        const formData = new FormData();
        formData.append('business_id', businessId);
        formData.append('action', action);

        fetch(this.getApiUrl('track_business.php'), {
            method: 'POST',
            body: formData
        }).then(res => res.json())
          .then(data => console.log('[Analytics]', action, data))
          .catch(err => console.error('[Analytics Error]', err));
    },

    switchTab: function (tabId, el) {
        this.vibrate(10);
        
        if (tabId === 'profile') {
            if (this.isLoggedIn) {
                // Eğer zaten profil sayfasındaysak, sayfayı yenileme
                const isProfilePage = window.location.pathname.toLowerCase().endsWith('profile.php');
                if (isProfilePage) {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    // Aktif tab'ı görsel olarak seçili tutmak için nav item'ı güncelle
                    if (el) {
                        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                        el.classList.add('active');
                    }
                    return;
                }
                window.location.href = 'profile.php';
            } else {
                this.toggleAuthModal();
            }
            return;
        }

            // Cross-page handling
        if (!document.getElementById(tabId)) {
            const districtSlug = document.body.getAttribute('data-district-slug');
            const inDistrictDir = /\/(cermik|cungus)\//i.test(window.location.pathname);
            
            if (inDistrictDir) {
                // Eğer bir ilçenin alt klasöründeysek (örn: cermik/places_archive.php)
                window.location.href = 'index.php?tab=' + tabId;
            } else if (districtSlug) {
                // Eğer ana dizindeysek ama ilçe bağlamı belliyse (örn: district.php?slug=cermik)
                window.location.href = 'district.php?slug=' + districtSlug + '&tab=' + tabId;
            } else {
                // Hiçbir ilçe bağlamı yoksa kök dizine git
                let base = '/';
                const pathL = window.location.pathname.toLowerCase();
                if (window.location.hostname.includes('localhost')) {
                    if (pathL.includes('/rehber/')) base = '/REHBER/';
                    else if (pathL.includes('/son/')) base = '/SON/';
                    else base = '/';
                }
                window.location.href = base + 'index.php?tab=' + tabId;
            }
            return;
        }

        // Get element safely
        const tabEl = document.getElementById(tabId);
        if (!tabEl) return;

        // Remove active class from all tabs and nav items
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));

        // Add active class to selected tab and nav item
        tabEl.classList.add('active');
        if (el) el.classList.add('active');

        // Refetch data for specific tabs to ensure fresh content
        if (tabId === 'events') this.loadEvents();
        if (tabId === 'explore') {
            this.loadPopularPlaces();
            this.loadAnnouncements();
        }

        // Update AI context based on tab
        const contexts = {
            'explore': 'Genel Rehber',
            'events': 'Etkinlik ve Duyurular',
            'services': 'Belediye Hizmetleri ve Projeler',
            'guide': 'Kent Rehberi ve Önemli Yerler',
            'profile': 'Vatandaş Profil Sayfası'
        };
        if (contexts[tabId]) {
            document.body.setAttribute('data-page-context', contexts[tabId]);
        }

        // Play subtle haptic-like animation
        el.style.transform = "scale(0.9)";
        setTimeout(() => el.style.transform = "scale(1)", 100);
    },

    navigateTo: function (type, id) {
        // Simplified navigation to avoid pathing errors
        if (type === 'home') {
            window.location.href = 'index.php';
        } else if (type === 'hotspring') {
            window.location.href = 'kaplica.php';
        } else if (type === 'restaurant') {
            window.location.href = 'lokantalar.php';
        } else if (type === 'hotel') {
            window.location.href = 'oteller.php';
        } else if (type === 'historical' || type === 'Historical') {
            window.location.href = 'places_archive.php?category=Historical';
        } else if (type === 'nature' || type === 'Nature') {
            window.location.href = 'places_archive.php?category=Nature';
        } else if (type === 'pharmacy') {
            window.location.href = 'pharmacy.php';
        } else if (type === 'parks_gardens' || type === 'ParkAndGarden') {
            window.location.href = 'places_archive.php?category=ParkAndGarden';
        } else if (type === 'kuruyemis') {
            window.location.href = 'kuruyemis.php';
        } else if (type === 'businesses' || type === 'Businesses') {
            window.location.href = 'places_archive.php?category=Businesses';
        } else if (type === 'cek_gonder') {
            if (!this.isLoggedIn) {
                this.showToast('Çek Gönder hizmetini kullanmak için üye girişi yapmalısınız. Yönlendiriliyorsunuz.', 'warning');
                setTimeout(() => {
                    this.switchTab('profile');
                }, 1500);
                return;
            }
            let distName = 'Bu ilçe';
            const titleEl = document.getElementById('site-header-title');
            if (titleEl && titleEl.textContent && titleEl.textContent.trim() !== '') {
                distName = titleEl.textContent.trim().replace(/ Rehberi$/i, '').replace(/ Belediyesi$/i, '');
            } else {
                const slug = document.body.getAttribute('data-district-slug');
                if(slug) distName = slug.charAt(0).toUpperCase() + slug.slice(1);
            }
            this.showToast('⚠️ ' + distName + ' bölgesi için Çek Gönder hizmetini kullanıyorsunuz. Yönlendiriliyorsunuz...', 'warning');
            setTimeout(() => {
                window.location.href = 'cek_gonder.php';
            }, 2000);
        } else if (type === 'place') {
            window.location.href = `place_detail.php?id=${id}`;
        } else if (type === 'business') {
            window.location.href = `business_detail.php?id=${id}`;
        }
    },

    handleCheckIn: function (targetId, targetType = 'place') {
        const btn = document.getElementById('checkin-btn') || document.querySelector(`[data-checkin-id="${targetId}"]`);

        if (!this.isLoggedIn) {
            this.showBalloonTooltip(btn, 'Check-in yapabilmek için üye olmanız gerekmektedir.');
            setTimeout(() => {
                this.toggleAuthModal();
            }, 2000);
            return;
        }

        if (!navigator.geolocation) {
            this.showToast('⚠️ Check-in yapabilmek için GPS gereklidir.', 'warning');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Konum alınıyor...';
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            const formData = new FormData();
            formData.append('target_id', targetId);
            formData.append('target_type', targetType);
            formData.append('lat', pos.coords.latitude);
            formData.append('lng', pos.coords.longitude);

            fetch(this.getApiUrl('check_in.php'), {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.showToast('🎉 ' + res.message, 'success');
                        if (btn) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Ziyaret Kaydedildi!';
                            btn.style.background = 'linear-gradient(45deg,#27ae60,#2ecc71)';
                            btn.style.opacity = '1';
                        }
                    } else {
                        const msg = res.distance
                            ? `📍 Henüz mekana yeterince yakın değilsiniz. (${res.distance} uzakta) Ziyaret için 100 m içinde olmanız gerekiyor.`
                            : res.message;
                        this.showToast(msg, 'warning');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Mekan Ziyareti Yap';
                        }
                    }
                })
                .catch(err => {
                    console.error('Check-in error:', err);
                    this.showToast('Sunucuya ulaşılamadı. Lütfen tekrar deneyin.', 'warning');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Mekan Ziyareti Yap';
                    }
                });
        }, (err) => {
            this.showToast('📡 Konumunuz alınamadı. Lütfen konum iznini etkinleştirin.', 'warning');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i> Mekan Ziyareti Yap';
            }
        }, { enableHighAccuracy: true, timeout: 10000 });
    },

    showBalloonTooltip: function (targetEl, msg) {
        if (!targetEl) return;
        let tooltip = document.getElementById('balloon-tooltip');
        if (tooltip) tooltip.remove();

        tooltip = document.createElement('div');
        tooltip.id = 'balloon-tooltip';
        tooltip.style.cssText = `
            position: absolute;
            background: linear-gradient(135deg,#e67e22,#f39c12);
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            z-index: 99999;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            text-align: center;
            max-width: 250px;
        `;
        tooltip.innerHTML = msg + `<div style="position:absolute; bottom:-4px; left:50%; transform:translateX(-50%) rotate(45deg); width:10px; height:10px; background:#f39c12;"></div>`;

        document.body.appendChild(tooltip);

        const rect = targetEl.getBoundingClientRect();
        tooltip.style.left = (rect.left + window.scrollX + (rect.width / 2)) + 'px';
        tooltip.style.top = (rect.top + window.scrollY - 10) + 'px';
        tooltip.style.transform = 'translate(-50%, -100%)';

        setTimeout(() => { tooltip.style.opacity = '1'; }, 10);

        setTimeout(() => {
            if (tooltip) tooltip.style.opacity = '0';
            setTimeout(() => { if (tooltip) tooltip.remove(); }, 300);
        }, 2000);
    },

    showToast: function (msg, type = 'success') {
        let toast = document.getElementById('app-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'app-toast';
            toast.style.cssText = 'position:fixed;bottom:90px;left:50%;transform:translateX(-50%);padding:14px 24px;border-radius:20px;font-size:0.95rem;font-weight:600;z-index:9999;max-width:90vw;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,0.4);transition:all 0.4s;opacity:0;pointer-events:none;';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        if (type === 'success') {
            toast.style.background = 'linear-gradient(135deg,#10b981,#059669)';
        } else if (type === 'error') {
            toast.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
        } else {
            toast.style.background = 'linear-gradient(135deg,#f59e0b,#d97706)';
        }
        toast.style.color = '#fff';
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => { 
            toast.style.opacity = '0'; 
            toast.style.transform = 'translateX(-50%) translateY(20px)';
        }, 4000);
    },

    loadPopularPlaces: function () {
        const popularList = document.getElementById('popular-list');
        if (!popularList) return;

        fetch(this.getApiUrl('get_places.php'))
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    let data = res.data;

                    // Sort by distance if GPS is available
                    if (this.currentCoords) {
                        data.sort((a, b) => {
                            const distA = this.calculateDistance(this.currentCoords.lat, this.currentCoords.lng, parseFloat(a.lat), parseFloat(a.lng));
                            const distB = this.calculateDistance(this.currentCoords.lat, this.currentCoords.lng, parseFloat(b.lat), parseFloat(b.lng));
                            return distA - distB;
                        });
                    }

                    popularList.innerHTML = data.map(place => `
                        <div class="card animate-in" onclick="app.navigateTo('place', ${place.id})" data-lat="${place.lat}" data-lng="${place.lng}" data-id="${place.id}">
                            <img src="${place.image_main || 'https://via.placeholder.com/400x200?text=' + place.name}" style="width:100%; border-radius:15px; margin-bottom:10px;" alt="${place.name}">
                            <div style="display:flex; justify-content:space-between; align-items:start;">
                                <div>
                                    <h3>${place.name}</h3>
                                    <p>${place.category}</p>
                                </div>
                                <span class="distance-info" style="font-size:0.8rem; font-weight:700; color:var(--secondary);"></span>
                            </div>
                        </div>
                    `).join('');
                    this.displayDistances();
                }
            })
            .catch(err => {
                if (popularList) popularList.innerHTML = `<p>Hata: Veri yüklenemedi.</p>`;
            });
    },

    loadAnnouncements: function () {
        console.log("loadAnnouncements called");
        const bellBtn = document.getElementById('bell-icon');
        const bellBtnFallback = document.querySelector('.header-icons .fa-bell');
        const activeBell = bellBtn || bellBtnFallback;
        console.log("ActiveBell found:", !!activeBell);
        
        // Don't return if no bell, still load for the modal
        const apiUrl = this.getApiUrl('get_announcements.php');
        console.log("Fetching from:", apiUrl);

        fetch(apiUrl)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const modalList = document.getElementById('announcements-list-modal');
                    if (modalList) {
                        // Check if we are in a subfolder (either by API_BASE or path segments)
                        const isSub = (window.API_BASE && window.API_BASE.includes('..')) || 
                                     (window.location.pathname.includes('/cungus/') || window.location.pathname.includes('/cermik/') || window.location.pathname.includes('/egil/'));
                        if (res.data.length === 0) {
                            modalList.innerHTML = `<div style="text-align:center; padding:40px; opacity:0.6;">
                                <i class="fa-solid fa-bullhorn" style="font-size:3rem; margin-bottom:15px; display:block;"></i>
                                <p>Şu an güncel bir duyuru bulunmuyor.</p>
                            </div>`;
                        } else {
                            modalList.innerHTML = res.data.map((a, index) => {
                                const imgSrc = a.image ? (isSub ? '../' + a.image : a.image) : '';
                                return `
                                    <div class="announcement-item" style="padding: 15px 0; ${index < res.data.length - 1 ? 'border-bottom: 1px solid rgba(255,255,255,0.08);' : ''}">
                                        <div style="font-size:0.75rem; color:var(--secondary); margin-bottom:8px; font-weight:600; display:flex; align-items:center; gap:6px;">
                                            <i class="fa-solid fa-calendar-day"></i>
                                            ${new Date(a.created_at).toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })}
                                        </div>
                                        ${a.image ? `
                                        <div style="width:100%; border-radius:14px; overflow:hidden; margin-bottom:10px; cursor:zoom-in; position:relative;"
                                             onclick="app.openAnnouncementImage('${imgSrc.replace(/'/g, "\\'")}')">
                                            <img src="${imgSrc}"
                                                 style="width:100%; height:auto; max-height:400px; object-fit:contain; background:rgba(0,0,0,0.2); display:block; transition:transform 0.3s;"
                                                 onmouseover="this.style.transform='scale(1.03)'"
                                                 onmouseout="this.style.transform='scale(1)'"
                                                 onerror="this.closest('div').style.display='none'">
                                            <div style="position:absolute; bottom:8px; right:8px; background:rgba(0,0,0,0.5); border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center;">
                                                <i class="fa-solid fa-expand" style="color:white; font-size:0.75rem;"></i>
                                            </div>
                                        </div>` : ''}
                                        <div style="color:rgba(255,255,255,0.9); line-height:1.6; font-size:0.85rem;">${a.content}</div>
                                    </div>
                                `;
                            }).join('');
                        }
                    }
                }
            })
            .catch(err => {
                console.error("Announcements error:", err);
                const modalList = document.getElementById('announcements-list-modal');
                if (modalList) modalList.innerHTML = `<p style="text-align:center; padding:20px; opacity:0.5;">Duyurular yüklenirken bir hata oluştu.</p>`;
            });
    },

    loadWeather: function () {
        const weatherWidget = document.getElementById('weather-widget');
        if (!weatherWidget) return;

        const iconEl = document.getElementById('weather-icon');
        const tempEl = document.getElementById('weather-temp');

        // Yükleniyor animasyonu
        if (iconEl) iconEl.classList.add('loading');

        const slug = document.body.getAttribute('data-district-slug') || 'cermik';
        fetch(this.getApiUrl(`get_weather.php?slug=${slug}`))
            .then(res => res.json())
            .then(data => {
                if (!iconEl || !tempEl) return;
                iconEl.classList.remove('loading');

                if (data.status === 'success') {
                    // İkon güncelle
                    iconEl.className = `fa-solid ${data.icon}`;

                    // Sıcaklık: kalın derece
                    const t = data.temp;
                    tempEl.textContent = `${t}°`;

                    // Tooltip: detaylı bilgi
                    const parts = [
                        `Çermik ${t}°C`,
                        data.condition || '',
                        data.humidity != '--' ? `Nem %${data.humidity}` : '',
                        data.wind ? `Rüzgar: ${data.wind}` : '',
                        data.min_temp != null && data.min_temp !== '--' ? `Min: ${data.min_temp}° / Maks: ${data.max_temp}°` : '',
                        `Üst: ${data.fetched_at}`
                    ].filter(Boolean);
                    weatherWidget.title = parts.join(' | ');
                } else {
                    iconEl.className = 'fa-solid fa-cloud';
                    tempEl.textContent = '--°';
                    weatherWidget.title = 'Hava durumu alınamadı';
                }
            })
            .catch(() => {
                if (iconEl) { iconEl.classList.remove('loading'); iconEl.className = 'fa-solid fa-cloud'; }
                if (tempEl) tempEl.textContent = '--°';
            });
    },

    openAnnouncementImage: function (src) {
        this.openImageLightbox(src);
    },

    openImageLightbox: function (src) {
        if (!src) return;
        const existing = document.getElementById('app-lightbox');
        if (existing) existing.remove();

        const lb = document.createElement('div');
        lb.id = 'app-lightbox';
        lb.style.cssText = 'position:fixed;inset:0;z-index:2000000;background:rgba(15,23,42,0.96);display:flex;align-items:center;justify-content:center;padding:20px;cursor:zoom-out;backdrop-filter:blur(10px);animation: fadeIn 0.3s ease;';

        lb.innerHTML = `
            <button id="lb-close"
                style="position:absolute;top:30px;right:30px;background:rgba(255,255,255,0.15);
                border:1px solid rgba(255,255,255,0.3);color:white;font-size:1.5rem;width:52px;height:52px;
                border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);
                box-shadow:0 8px 32px rgba(0,0,0,0.5);z-index:2000001;transition:0.3s;">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img src="${src}"
                style="max-width:95%;max-height:90vh;border-radius:16px;object-fit:contain;
                box-shadow:0 30px 80px rgba(0,0,0,0.8);animation: zoomIn 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);"
                onclick="event.stopPropagation()">
        `;

        lb.onclick = () => {
            lb.style.opacity = '0';
            setTimeout(() => lb.remove(), 300);
        };
        document.body.appendChild(lb);

        const onKey = (e) => {
            if (e.key === 'Escape') { 
                lb.style.opacity = '0';
                setTimeout(() => lb.remove(), 300);
                document.removeEventListener('keydown', onKey); 
            }
        };
        document.addEventListener('keydown', onKey);
    },

    toggleAnnouncementsModal: function () {
        const modal = document.getElementById('announcements-modal');
        if (modal) {
            modal.classList.toggle('active');
            if (modal.classList.contains('active')) {
                this.loadAnnouncements(); // Ensure they are loaded
            }
        }
    },

    togglePolicyModal: function () {
        document.getElementById('policy-modal').classList.toggle('active');
    },

    showPolicy: function (type) {
        const titleEl = document.getElementById('policy-title');
        const contentEl = document.getElementById('policy-content');
        const labels = document.getElementById('policy-labels');

        if (type === 'kvkk') {
            titleEl.textContent = labels ? labels.dataset.kvkkTitle : 'KVKK';
            contentEl.innerHTML = document.getElementById('kvkk-content-data').innerHTML || 'Henüz metin girilmemiş.';
        } else if (type === 'cookie') {
            titleEl.textContent = labels ? labels.dataset.cookieTitle : 'Çerez Politikası';
            contentEl.innerHTML = document.getElementById('cookie-content-data').innerHTML || 'Henüz metin girilmemiş.';
        }

        this.togglePolicyModal();
    },

    loadEvents: function () {
        const eventsList = document.getElementById('events-list');
        if (!eventsList) return;

        fetch(this.getApiUrl('get_events.php'))
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    const isSub = (window.API_BASE && window.API_BASE.includes('..')) || 
                                 (window.location.pathname.includes('/cungus/') || window.location.pathname.includes('/cermik/') || window.location.pathname.includes('/egil/'));
                    eventsList.innerHTML = res.data.map(event => {
                        const imgSrc = event.image ? (isSub ? '../' + event.image : event.image) : '';
                        return `
                        <div class="card animate-in" style="padding: 0; overflow: hidden; ${event.image ? 'cursor: pointer;' : ''}" ${event.image ? `onclick="app.openImageLightbox('${imgSrc.replace(/'/g, "\\'")}')"` : ''}>
                            ${event.image ? `
                            <div class="event-img-container" style="position: relative; width: 100%; aspect-ratio: 1/1; background: rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center;">
                                <img src="${imgSrc}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                                <div style="position:absolute; bottom:15px; right:15px; background:rgba(0,0,0,0.5); border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
                                    <i class="fa-solid fa-expand" style="color:white; font-size:0.9rem;"></i>
                                </div>
                            </div>` : ''}
                            <div style="padding: 20px;">
                                <h3 style="margin: 0; margin-bottom: 10px; color: var(--secondary);">${event.title}</h3>
                                <p style="margin-bottom: 15px;">${event.description}</p>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--text-secondary); background: rgba(255,255,255,0.05); padding: 5px 12px; border-radius: 10px; width: fit-content;">
                                    <i class="fa-solid fa-calendar-alt"></i> ${new Date(event.event_date).toLocaleDateString('tr-TR')}
                                </div>
                            </div>
                        </div>
                    `;
                    }).join('');
                } else if (eventsList) {
                    eventsList.innerHTML = `<div class="card"><p style="text-align: center; opacity: 0.5;">Şu an aktif bir etkinlik bulunmamaktadır.</p></div>`;
                }
            });
    },

    filterProjects: function (status, btn) {
        // Tab styling
        document.querySelectorAll('.project-tab').forEach(b => {
            b.classList.remove('active');
            b.style.background = 'transparent';
            b.style.color = 'var(--text-secondary)';
            b.style.borderColor = 'var(--glass-bg)';
        });
        btn.classList.add('active');
        btn.style.background = 'rgba(0,201,255,0.1)';
        btn.style.color = 'var(--secondary)';
        btn.style.borderColor = 'var(--secondary)';

        this.loadServices(status);
    },

    toggleSidebar: function () {
        document.querySelector('.sidebar-menu').classList.toggle('active');
        document.querySelector('.sidebar-overlay').classList.toggle('active');
    },

    loadMunicipalGuide: function () {
        const guideMenu = document.getElementById('municipal-guide-menu');
        if (!guideMenu) return;

        fetch(this.getApiUrl('get_guide.php'))
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    guideMenu.innerHTML = res.data.map(item => {
                        let html = `<a href="municipal_guide_detail.php?id=${item.id}" class="sidebar-item">
                            <i class="fa-solid fa-chevron-right" style="font-size: 0.8rem; opacity: 0.5;"></i>
                            <span>${item.title}</span>
                        </a>`;
                        return html;
                    }).join('');
                } else {
                    guideMenu.innerHTML = `<div style="padding: 10px 25px; font-size: 0.8rem; color: var(--text-secondary); opacity: 0.5;">Henüz içerik eklenmemiş.</div>`;
                }
            })
            .catch(err => {
                console.error("Guide load error:", err);
            });
    },

    changeBG: function (type) {
        const bg = document.getElementById('app-bg');
        if (!bg) return;

        const bgs = {
            'default': 'assets/img/bg/bg_default.jpg',
            'historical': 'assets/img/bg/bg_historical.jpg',
            'nature': 'assets/img/bg/bg_nature.jpg',
            'hotspring': 'assets/img/bg/bg_hotspring.jpg',
            'parks_gardens': 'assets/img/bg/bg_parks.jpg',
            'restaurant': 'assets/img/bg/bg_restaurant.jpg',
            'hotel': 'assets/img/bg/bg_hotel.jpg'
        };

        const isSub = (window.API_BASE && window.API_BASE.includes('..')) || 
                     (window.location.pathname.includes('/cungus/') || window.location.pathname.includes('/cermik/') || window.location.pathname.includes('/egil/'));
        const imgPath = isSub ? '../' + (bgs[type] || bgs['default']) : (bgs[type] || bgs['default']);

        // Image check
        const img = new Image();
        img.onload = () => {
            bg.style.backgroundImage = `url('${imgPath}')`;
        };
        img.onerror = () => {
            bg.style.background = "linear-gradient(45deg, #0f172a, #1e293b)";
        };
        img.src = imgPath;
    },

    loadServices: function (filterStatus = null) {
        const servicesList = document.getElementById('services-list');
        if (!servicesList) return;

        // Header and filter layout
        let headerHtml = `
            <div class="services-header-compact">
                <div class="services-filter-tabs">
                    <button class="s-filter-btn ${filterStatus === 0 ? 'active' : ''}" onclick="app.loadServices(0)">
                        <i class="fa-solid fa-clock-rotate-left"></i> Devam Eden
                    </button>
                    <button class="s-filter-btn ${filterStatus === 1 || filterStatus === null ? 'active' : ''}" onclick="app.loadServices(1)">
                        <i class="fa-solid fa-check-double"></i> Tamamlanan
                    </button>
                </div>
            </div>
            <div id="services-grid-container" class="services-grid-container">
                <div style="text-align:center; padding: 40px; grid-column: 1/-1;"><i class="fa-solid fa-circle-notch fa-spin"></i> <span class="i18n-loading">Yükleniyor...</span></div>
            </div>
        `;
        
        servicesList.innerHTML = headerHtml;
        const gridContainer = document.getElementById('services-grid-container');

        fetch(this.getApiUrl(`get_services.php`))
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    let filteredData = res.data;
                    if (filterStatus !== null) {
                        filteredData = res.data.filter(s => s.status == filterStatus);
                    }

                    if (filteredData.length > 0) {
                        const isSub = (window.API_BASE && window.API_BASE.includes('..')) || 
                                     (window.location.pathname.includes('/cungus/') || window.location.pathname.includes('/cermik/') || window.location.pathname.includes('/egil/'));
                        gridContainer.innerHTML = filteredData.map(svc => {
                            const desc = svc.description || '';
                            const needsTruncation = desc.length > 100;
                            const imgSrc = svc.image ? (isSub ? '../' + svc.image : svc.image) : (isSub ? '../assets/img/project_default.jpg' : 'assets/img/project_default.jpg');
                            
                            return `
                            <div class="card service-compact-card animate-in">
                                <div class="svc-img-wrap" onclick="app.showServiceImage('${imgSrc}')">
                                    <img src="${imgSrc}" alt="${svc.title}">
                                    <div class="svc-status-tag ${svc.status == 1 ? 'completed' : 'ongoing'}">
                                        ${svc.status == 1 ? 'Tamamlandı' : 'Devam Ediyor'}
                                    </div>
                                </div>
                                <div class="svc-content">
                                    <h3>${svc.title}</h3>
                                    <div class="service-desc-wrapper">
                                        <p class="service-desc">${desc}</p>
                                        ${needsTruncation ? `<span class="service-read-more" onclick="app.toggleServiceDesc(this)">...devamı</span>` : ''}
                                    </div>
                                </div>
                            </div>
                        `}).join('');
                    } else {
                        gridContainer.innerHTML = `<div class="card" style="grid-column: 1/-1;"><p style="text-align:center; opacity: 0.5;">Seçilen kategoride proje bulunamadı.</p></div>`;
                    }
                } else {
                    gridContainer.innerHTML = `<div class="card" style="grid-column: 1/-1;"><p style="text-align:center; opacity: 0.5;">Henüz bir proje bulunmamaktadır.</p></div>`;
                }
            })
            .catch(err => {
                console.error("Services load error:", err);
                gridContainer.innerHTML = `<div class="card" style="grid-column: 1/-1;"><p style="text-align:center; opacity: 0.5;">Veriler yüklenirken bir hata oluştu.</p></div>`;
            });
    },

    _renderServiceList: function(data) {
        // Unused now but kept for compatibility if needed elsewhere
        return '';
    },
    showServiceImage: function (src) {
        let lightbox = document.getElementById('service-lightbox');
        if (!lightbox) {
            lightbox = document.createElement('div');
            lightbox.id = 'service-lightbox';
            lightbox.className = 'service-lightbox';
            lightbox.innerHTML = `
                <div class="service-lightbox-close" onclick="this.parentElement.classList.remove('active')"><i class="fa-solid fa-xmark"></i></div>
                <img class="service-lightbox-content" src="" alt="Lightbox">
            `;
            document.body.appendChild(lightbox);
            lightbox.onclick = (e) => { if (e.target === lightbox) lightbox.classList.remove('active'); };
        }
        const img = lightbox.querySelector('img');
        img.src = src;
        lightbox.classList.add('active');
    },
    toggleServiceDesc: function (btn) {
        const p = btn.previousElementSibling;
        if (p.classList.contains('expanded')) {
            p.classList.remove('expanded');
            btn.innerText = 'Devamını Oku';
        } else {
            p.classList.add('expanded');
            btn.innerText = 'Kapat';
        }
    },

    updateGPS: function () {
        if (!navigator.geolocation) {
            this.showDistanceError("GPS Desteklenmiyor");
            return;
        }

        // İlk gösterim için "Hesaplanıyor..." yazalım (Daha erkene aldık)
        this.showDistanceLoading();

        const options = { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 };

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.currentCoords = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude
                };
                console.log("Konum alındı:", this.currentCoords);
                this.displayDistances();
            },
            (err) => {
                console.warn("GPS Hassasiyet Hatası (Yedek deneniyor):", err.message);
                // Yüksek hassasiyet başarısız olursa düşük hassasiyetle tekrar dene
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.currentCoords = {
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude
                        };
                        this.displayDistances();
                    },
                    (err2) => {
                        console.error("Kesin GPS Hatası:", err2.message);
                        this.showDistanceError("Konum alınamadı");
                    },
                    { enableHighAccuracy: false, timeout: 5000 }
                );
            },
            options
        );
    },

    showDistanceLoading: function () {
        document.querySelectorAll('.distance-info').forEach(el => {
            el.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Hesaplanıyor...';
        });
    },

    showDistanceError: function (msg) {
        document.querySelectorAll('.distance-info').forEach(el => {
            el.innerHTML = `<i class="fa-solid fa-location-dot" style="opacity:0.5"></i> <small style="font-size:0.7rem; opacity:0.5">${msg}</small>`;
        });
    },

    // Haversine Mesafesi Hesapla
    calculateDistance: function (lat1, lon1, lat2, lon2) {
        const R = 6371; // Dünya yarıçapı (km)
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    },

    // Sayfadaki tüm mesafe alanlarını güncelle ve gerekirse DOM'u sırala
    displayDistances: function () {
        const targets = document.querySelectorAll('[data-lat][data-lng]');
        
        if (!this.currentCoords) {
            // Eğer koordinatlar henüz yoksa, spinner yerine bilgilendirici mesaj (isteğe bağlı)
            targets.forEach(el => {
                const distDisplay = el.querySelector('.distance-info');
                if (distDisplay && !distDisplay.dataset.fallbackSet) {
                    distDisplay.innerHTML = `<i class="fa-solid fa-location-dot"></i> <span>${window.__('waiting_location') || 'Konum bekleniyor...'}</span>`;
                    distDisplay.dataset.fallbackSet = "true";
                }
            });
            return;
        }

        // 1. Önce mesafeleri yazalım
        targets.forEach(el => {
            const rawLat = el.getAttribute('data-lat');
            const rawLng = el.getAttribute('data-lng');

            const tLat = parseFloat(rawLat);
            const tLng = parseFloat(rawLng);

            if (!isNaN(tLat) && !isNaN(tLng)) {
                const dist = this.calculateDistance(
                    this.currentCoords.lat,
                    this.currentCoords.lng,
                    tLat,
                    tLng
                );

                el.setAttribute('data-distance', dist); // Sıralama için veriyi sakla

                // Geofencing: 100 metreden (0.1 km) yakına gelince otomatik sayaç artırımı
                if (dist <= 0.1) {
                    const targetId = el.getAttribute('data-id') || el.getAttribute('onclick')?.match(/navigateTo\('place', (\d+)\)/)?.[1];
                    const targetType = el.getAttribute('data-type') || 'place';
                    const category = el.getAttribute('data-category');

                    const isExcluded = category && (
                        category.toLowerCase().includes('hospital') ||
                        category.toLowerCase().includes('pharmacy') ||
                        category.toLowerCase().includes('hastane') ||
                        category.toLowerCase().includes('eczane')
                    );

                    if (targetId && !isExcluded) {
                        this.recordPassiveVisit(targetId, targetType);
                    }
                }

                const distDisplay = el.querySelector('.distance-info');
                if (distDisplay) {
                    // Mesafe formatlama: 1km altı metre, üstü km (tek ondalık)
                    let formatted = '';
                    if (dist < 1) {
                        formatted = Math.round(dist * 1000) + ' m';
                    } else {
                        formatted = dist.toFixed(1) + ' km';
                    }
                    distDisplay.innerHTML = `<i class="fa-solid fa-location-arrow"></i> ${formatted}`;
                    distDisplay.style.opacity = "1";
                }
            }
        });

        // 2. DOM Sıralama (Liste konteynerleri için)
        const containers = document.querySelectorAll('#popular-list, #events-list, #services-list, #business-list, #archive-list .menu-list, #archive-list, #pharmacy-list, #hospital-list');
        containers.forEach(container => this.sortDOMByDistance(container));
    },

    sortDOMByDistance: function (container) {
        if (!container) return;
        const items = Array.from(container.children);
        
        // Sadece mesafe verisi olanları veya konteynerin tamamını kontrol et
        const hasDistData = items.some(item => item.hasAttribute('data-distance'));
        if (!hasDistData) return;

        // Önce konum verisi olanları başa al, sonra mesafeye göre sırala
        items.sort((a, b) => {
            const distA = a.hasAttribute('data-distance') ? parseFloat(a.getAttribute('data-distance')) : 999999;
            const distB = b.hasAttribute('data-distance') ? parseFloat(b.getAttribute('data-distance')) : 999999;
            return distA - distB;
        });

        // Eğer sıralama değişmişse güncelle (Layout thrashing önleme için basit kontrol eklenebilir ama şimdilik doğrudan)
        let changed = false;
        items.forEach((item, index) => {
            if (container.children[index] !== item) {
                changed = true;
            }
        });

        if (changed) {
            const fragment = document.createDocumentFragment();
            items.forEach(item => fragment.appendChild(item));
            container.appendChild(fragment);
        }
    },


    toggleAuthModal: function () {
        const modal = document.getElementById('auth-modal');
        if (!modal) {
            console.error("Auth modal not found in DOM");
            return;
        }

        if (modal.classList.contains('active')) {
            this.closeAuthModal();
        } else {
            modal.classList.add('active');
            document.body.classList.add('modal-open');
            // Ensure city/district is initialized if we are on register mode
            const regForm = document.getElementById('register-form');
            if (regForm && regForm.style.display === 'block') {
                this.initCityDistrictDropdowns();
            }
            if (typeof initGoogleAuth === 'function') {
                initGoogleAuth();
            }

            // Click outside to close
            modal._outsideHandler = (e) => {
                if (e.target === modal) this.closeAuthModal();
            };
            modal.addEventListener('click', modal._outsideHandler);
        }
    },

    closeAuthModal: function () {
        const modal = document.getElementById('auth-modal');
        if (!modal) return;
        modal.classList.remove('active');
        document.body.classList.remove('modal-open');
        if (modal._outsideHandler) {
            modal.removeEventListener('click', modal._outsideHandler);
            delete modal._outsideHandler;
        }
    },
    toggleAuthMode: function (mode) {
        const forms = document.querySelectorAll('#auth-forms form');
        forms.forEach(f => f.style.display = 'none');
        
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const quickLoginForm = document.getElementById('quick_login_request-form');
        const forgotForm = document.getElementById('forgot-form');
        const title = document.getElementById('auth-title');

        if (mode === 'login') {
            if (loginForm) loginForm.style.display = 'block';
            if (title) title.innerText = 'Giriş Yap';
        } else if (mode === 'register') {
            if (registerForm) registerForm.style.display = 'block';
            if (title) title.innerText = 'Kayıt Ol';
            if (typeof this.initCityDistrictDropdowns === 'function') {
                this.initCityDistrictDropdowns();
            }
        } else if (mode === 'quick_login') {
            if (quickLoginForm) quickLoginForm.style.display = 'block';
            if (title) title.innerText = 'SMS ile Hızlı Giriş';
        } else if (mode === 'forgot') {
            if (forgotForm) forgotForm.style.display = 'block';
            if (title) title.innerText = 'Şifremi Unuttum';
        } else if (mode === 'verify_otp') {
            const otpForm = document.getElementById('verify_otp-form');
            if (otpForm) otpForm.style.display = 'block';
            if (title) title.innerText = 'Doğrulama Kodu';
        } else if (mode === 'complete_profile') {
            const profileForm = document.getElementById('complete_profile-form');
            if (profileForm) profileForm.style.display = 'block';
            if (title) title.innerText = 'Profilinizi Tamamlayın';
        } else {
            // Default: login
            if (loginForm) loginForm.style.display = 'block';
            if (title) title.innerText = 'Giriş Yap';
        }
    },

    handleAuth: function (action) {
        // Password confirmation check for complete_profile
        if (action === 'complete_profile') {
            const pass = document.querySelector('#complete_profile-form [name="password"]').value;
            const confirm = document.querySelector('#complete_profile-form [name="password_confirm"]').value;
            if (pass !== confirm) {
                this.showAuthMessage('Şifreler eşleşmiyor.', 'error');
                return;
            }
            if (pass.length < 6) {
                this.showAuthMessage('Şifre en az 6 karakter olmalıdır.', 'error');
                return;
            }
        }

        const form = document.getElementById(action + '-form');
        const formData = new FormData(form);

        fetch(this.getApiUrl('user_auth.php?action=' + action), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                console.log("[Auth Response]", res);

                if (res.status === 'success') {
                    this.showAuthMessage(res.message || 'İşlem başarılı!', 'success');
                    setTimeout(() => {
                        this.isLoggedIn = true;
                        window.location.href = 'profile.php';
                    }, 1500);
                } else if (res.status === 'needs_otp') {
                    // Update user_id for OTP
                    const otpUserId = document.getElementById('otp-user-id');
                    if (otpUserId) {
                        otpUserId.value = res.user_id || res.temp_user_id || '';
                    }

                    this.showAuthMessage(res.message, 'warning');
                    setTimeout(() => this.toggleAuthMode('verify_otp'), 1000);
                } else if (res.status === 'needs_profile_completion') {
                    // Store user_id or handle it as session
                    this.showAuthMessage(res.message, 'warning');
                    setTimeout(() => this.toggleAuthMode('complete_profile'), 1000);
                } else {
                    this.showAuthMessage(res.message, 'error');
                }
            })
            .catch(err => {
                console.error("Auth error:", err);
                this.showAuthMessage("İşlem sırasında bir hata oluştu.", 'error');
            });
    },

    showAuthMessage: function (message, type = 'error') {
        const warningEl = document.getElementById('auth-warning');
        if (!warningEl) return;

        const icons = {
            'error': 'fa-circle-exclamation',
            'success': 'fa-circle-check',
            'warning': 'fa-triangle-exclamation'
        };

        const icon = icons[type] || icons['error'];
        
        warningEl.className = 'auth-alert auth-alert-' + type + ' show';
        warningEl.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
        
        // Modalın en üstüne kaydır (Mesaj görünür olsun)
        const modalContent = warningEl.closest('.modal-content');
        if (modalContent) {
            modalContent.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Otomatik gizleme (Başarı durumunda)
        if (type === 'success') {
            setTimeout(() => {
                // warningEl.classList.remove('show');
            }, 3000);
        }
    },

    visitedPlaces: new Set(),
    recordPassiveVisit: function (targetId, targetType = 'place') {
        const passiveKey = `passive_v3_${targetType}_${targetId}`;
        const lastVisit = localStorage.getItem(passiveKey);
        const now = Date.now();

        // 24 saatlik cooldown (Periyodik sunucu yükünü ve sayaç şişmesini önlemek için)
        if (lastVisit && (now - lastVisit < 86400000)) return;

        console.log(`[Proximity] Kayıt denemesi: ${targetType} #${targetId}`);

        const formData = new FormData();
        formData.append('target_id', targetId);
        formData.append('target_type', targetType);
        
        // İlçe ID tespiti
        const districtId = document.body.getAttribute('data-district-id') || 
                           (window.location.search.match(/slug=([^&]+)/)?.[1] === 'cermik' ? 1 : 
                            (window.location.search.match(/slug=([^&]+)/)?.[1] === 'cungus' ? 2 : 1));
        
        formData.append('district_id', districtId);

        if (this.currentCoords) {
            formData.append('lat', this.currentCoords.lat);
            formData.append('lng', this.currentCoords.lng);
        }

        fetch(this.getApiUrl('track_proximity.php'), {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                localStorage.setItem(passiveKey, now);
                console.log("[Proximity] Kayıt başarılı:", res.message);
            }
        })
        .catch(err => console.warn("[Proximity] Hata:", err));
    },

    recordVisit: function (targetId, targetType = 'place') {
        const visitKey = `visit_${targetType}_${targetId}`;
        const lastVisit = localStorage.getItem(visitKey);
        const now = Date.now();

        // 1 saatlik local cooldown (Sürekli API'yi yormamak için)
        if (!this.isLoggedIn || (lastVisit && (now - lastVisit < 3600000))) return;

        console.log("Oto-Ziyaret kaydediliyor:", visitKey);
        const formData = new FormData();
        formData.append('target_id', targetId);
        formData.append('target_type', targetType);
        if (this.currentCoords) {
            formData.append('lat', this.currentCoords.lat);
            formData.append('lng', this.currentCoords.lng);
        }

        fetch(this.getApiUrl('record_visit.php'), {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    localStorage.setItem(visitKey, now);
                    console.log("Ziyaret başarıyla kaydedildi.");
                } else {
                    console.log("Ziyaret kaydı atlandı:", res.message);
                    if (res.message.includes('zaten')) {
                        localStorage.setItem(visitKey, now); // Sunucuda cooldown varsa locale de yazalım
                    }
                }
            })
            .catch(err => console.error("Ziyaret kaydı hatası:", err));
    },

    toggleCollapsible: function (btn) {
        const wrapper = btn.closest('.collapsible-wrapper');
        const content = wrapper.querySelector('.collapsible-content');
        const span = btn.querySelector('span');
        const icon = btn.querySelector('i');

        content.classList.toggle('expanded');

        if (content.classList.contains('expanded')) {
            span.textContent = 'Daha Az Gör';
            if (icon) icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        } else {
            span.textContent = 'Devamını Oku';
            if (icon) icon.classList.replace('fa-chevron-up', 'fa-chevron-down');

            // Metin kapandığında bölümün başına yumuşak kaydır
            wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    },

    isLoggedIn: false,
    currentCoords: null,

    handleGoogleAuth: function(credential) {
        const formData = new FormData();
        formData.append('credential', credential);

        fetch(this.getApiUrl('user_auth.php?action=google_login'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                const modal = document.getElementById('auth-modal');
                if (modal && !modal.classList.contains('active')) {
                    this.toggleAuthModal();
                }

                if (res.status === 'success') {
                    this.isLoggedIn = true;
                    this.showAuthMessage(res.message || 'Google girişi başarılı!', 'success');
                    setTimeout(() => { window.location.href = 'profile.php'; }, 1000);
                } else if (res.status === 'needs_phone') {
                    this.toggleAuthMode('google_phone');
                    document.getElementById('gp_email').value = res.email || '';
                    document.getElementById('gp_first_name').value = res.first_name || '';
                    document.getElementById('gp_last_name').value = res.last_name || '';
                    document.getElementById('gp_credential').value = credential;
                    const gpPic = document.getElementById('gp_picture');
                    if (gpPic) gpPic.value = res.picture || '';
                } else if (res.status === 'needs_otp') {
                    if (res.message) this.showAuthMessage(res.message, 'warning');
                    setTimeout(() => { this.toggleAuthMode('register_otp'); }, 1500);
                } else {
                    this.showAuthMessage(res.message || 'Google girişi sırasında hata oluştu.', 'error');
                }
            })
            .catch(err => {
                console.error("Google Auth error:", err);
                this.showAuthMessage('Giriş yapılırken teknik bir hata oluştu.', 'error');
            });
    }
};

// DOMContentLoaded ile daha güvenli başlatma
document.addEventListener('DOMContentLoaded', () => {
    app.init();
});

/* Klavye ve Input Odaklanma Sorunu İçin (Android Uyumluluk) */
document.querySelectorAll('input, textarea').forEach(function(el) {
    el.addEventListener('click', function() {
        this.focus();
        // Klavyenin açılması için kısa bir gecikme ile sayfayı kaydır
        setTimeout(function() {
            el.scrollIntoView({behavior: "smooth", block: "center"});
        }, 300);
    });
});
