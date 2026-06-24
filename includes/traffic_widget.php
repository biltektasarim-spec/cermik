<?php
/**
 * Live Traffic and Density Widget
 * Requires: $widget_lat, $widget_lng, $widget_name
 */
if (!isset($widget_lat) || !isset($widget_lng)) return;
$widget_name = $widget_name ?? 'Konum';
?>
<!-- Google Maps Traffic Section -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places"></script>
<style>
    .traffic-section {
        margin: 30px 0;
        padding: 20px;
        background: var(--card-bg);
        border-radius: var(--radius);
        border: 1px solid rgba(255,255,255,0.05);
        backdrop-filter: blur(10px);
    }
    #traffic-map {
        width: 100%;
        height: 300px;
        border-radius: 15px;
        margin-bottom: 20px;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .density-table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text-primary);
        font-size: 0.9rem;
    }
    .density-table th, .density-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .density-table th { color: var(--secondary); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
    .status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }
    .status-low { background: rgba(72, 187, 120, 0.2); color: #48bb78; }
    .status-med { background: rgba(237, 137, 54, 0.2); color: #ed8936; }
    .status-high { background: rgba(245, 101, 101, 0.2); color: #f56565; }
</style>

<div class="traffic-section animate-in">
    <h3 style="margin-bottom: 20px; color: var(--secondary);"><i class="fa-solid fa-map-location-dot"></i> <?php echo __('live_traffic_density'); ?></h3>
    <div id="traffic-map"></div>
    
    <table class="density-table">
        <thead>
            <tr>
                <th><?php echo __('region_point'); ?></th>
                <th><?php echo __('traffic_status'); ?></th>
                <th><?php echo __('estimated_density'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($widget_name); ?></td>
                <td><span id="traffic-status" class="status-badge status-low"><?php echo __('traffic_sakin'); ?></span></td>
                <td><span id="density-status" class="status-badge status-low"><?php echo __('density_hafif'); ?></span></td>
            </tr>
        </tbody>
    </table>
    <p style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 15px; opacity: 0.6;">
        <?php echo __('google_maps_data_hint'); ?>
    </p>
</div>

<script>
    function initTrafficMap() {
        const myLatLng = { lat: <?php echo $widget_lat; ?>, lng: <?php echo $widget_lng; ?> };
        const map = new google.maps.Map(document.getElementById("traffic-map"), {
            zoom: 15,
            center: myLatLng,
            mapTypeId: 'roadmap',
            disableDefaultUI: true,
            zoomControl: true,
            styles: [
                { "elementType": "geometry", "stylers": [{"color": "#242f3e"}] },
                { "elementType": "labels.text.stroke", "stylers": [{"color": "#242f3e"}] },
                { "elementType": "labels.text.fill", "stylers": [{"color": "#746855"}] },
                { "featureType": "road", "elementType": "geometry", "stylers": [{"color": "#38414e"}] },
                { "featureType": "road", "elementType": "geometry.stroke", "stylers": [{"color": "#212a37"}] },
                { "featureType": "road", "elementType": "labels.text.fill", "stylers": [{"color": "#9ca5b3"}] },
                { "featureType": "road.highway", "elementType": "geometry", "stylers": [{"color": "#746855"}] },
                { "featureType": "water", "elementType": "geometry", "stylers": [{"color": "#17263c"}] }
            ]
        });

        const trafficLayer = new google.maps.TrafficLayer();
        trafficLayer.setMap(map);

        const marker = new google.maps.Marker({
            position: myLatLng,
            map: map,
            title: "<?php echo addslashes($widget_name); ?>"
        });
        const trafficStatusEl = document.getElementById('traffic-status');
        const densityStatusEl = document.getElementById('density-status');
        
        const lang = "<?php echo $_SESSION['lang'] ?? 'tr'; ?>";
        const translations = {
            'hesaplaniyor': "<?php echo __('calculating_distance') ?? 'Hesaplanıyor...'; ?>",
            'yogun': "<?php echo __('traffic_yogun'); ?>",
            'orta': "<?php echo __('density_orta'); ?>",
            'normal': "<?php echo __('traffic_normal'); ?>",
            'sakin': "<?php echo __('traffic_sakin'); ?>",
            'akici': "<?php echo __('traffic_akici'); ?>",
            'hafif': "<?php echo __('density_hafif'); ?>",
            'error': "Bilinmiyor"
        };

        trafficStatusEl.textContent = translations['hesaplaniyor'];
        densityStatusEl.textContent = "...";
        
        function updateTrafficUI(delayPercent) {
            let delayRounded = Math.round(delayPercent * 10) / 10;
            if (delayPercent < 0) {
                trafficStatusEl.textContent = "Veri Yok";
                trafficStatusEl.className = "status-badge";
                trafficStatusEl.style.background = "rgba(128, 128, 128, 0.2)";
                trafficStatusEl.style.color = "#a0aec0";
                
                densityStatusEl.textContent = "Bilinmiyor";
                densityStatusEl.className = "status-badge";
                densityStatusEl.style.background = "rgba(128, 128, 128, 0.2)";
                densityStatusEl.style.color = "#a0aec0";
            } else if (delayPercent <= 5) {
                trafficStatusEl.textContent = translations['akici'] + " (+" + delayRounded + "%)";
                trafficStatusEl.className = "status-badge status-low";
                trafficStatusEl.style = "";
                densityStatusEl.textContent = translations['hafif'];
                densityStatusEl.className = "status-badge status-low";
                densityStatusEl.style = "";
            } else if (delayPercent > 5 && delayPercent <= 15) {
                trafficStatusEl.textContent = translations['normal'] + " (+" + delayRounded + "%)";
                trafficStatusEl.className = "status-badge status-med";
                trafficStatusEl.style = "";
                densityStatusEl.textContent = translations['orta'];
                densityStatusEl.className = "status-badge status-med";
                densityStatusEl.style = "";
            } else {
                trafficStatusEl.textContent = translations['yogun'] + " (+" + delayRounded + "%)";
                trafficStatusEl.className = "status-badge status-high";
                trafficStatusEl.style = "";
                densityStatusEl.textContent = translations['yogun']; 
                densityStatusEl.className = "status-badge status-high";
                densityStatusEl.style = "";
            }
        }

        const service = new google.maps.DistanceMatrixService();
        const fallbackOrigin = { lat: myLatLng.lat - 0.02, lng: myLatLng.lng - 0.02 };

        function fetchMatrix(originPoint) {
            service.getDistanceMatrix({
                origins: [originPoint],
                destinations: [myLatLng],
                travelMode: google.maps.TravelMode.DRIVING,
                drivingOptions: {
                    departureTime: new Date(),
                    trafficModel: 'bestguess'
                }
            }, function(response, status) {
                if (status === 'OK') {
                    const result = response.rows[0].elements[0];
                    if (result && result.duration && result.duration_in_traffic) {
                        const normalTime = result.duration.value;
                        const trafficTime = result.duration_in_traffic.value;
                        const delayPercent = ((trafficTime - normalTime) / normalTime) * 100;
                        updateTrafficUI(delayPercent < 0 ? 0 : delayPercent);
                    } else {
                        updateTrafficUI(-1); // Data missing fallback
                    }
                } else {
                    updateTrafficUI(-1); // API error fallback
                }
            });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => fetchMatrix({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                err => fetchMatrix(fallbackOrigin),
                { timeout: 5000 }
            );
        } else {
            fetchMatrix(fallbackOrigin);
        }
    }

    // Attach to window load or execute if already loaded
    if (typeof google !== 'undefined' && google.maps) {
        initTrafficMap();
    } else {
        // Fallback for async load if needed, but script tag above is synchronous
        window.addEventListener('load', initTrafficMap);
    }
</script>
