<!-- Bottom Navigation -->
<nav class="nav-bar">
    <div class="nav-item <?php echo (!isset($active_tab) || $active_tab == 'explore') ? 'active' : ''; ?>" onclick="app.switchTab('explore', this)">
        <i class="fa-solid fa-compass"></i>
        <span><?php echo __('discover_tab'); ?></span>
    </div>
    <div class="nav-item <?php echo (isset($active_tab) && $active_tab == 'events') ? 'active' : ''; ?>" onclick="app.switchTab('events', this)">
        <i class="fa-solid fa-calendar-days"></i>
        <span><?php echo __('event_tab'); ?></span>
    </div>

    <!-- Tam Ortaya Yakındaki İlçeler Menüsü -->
    <?php 
        $base_path = '/';
        $req_uri = strtolower($_SERVER['REQUEST_URI'] ?? '');
        if (strpos($req_uri, '/rehber/') !== false) {
            $base_path = '/REHBER/';
        } elseif (strpos($req_uri, '/son/') !== false) {
            $base_path = '/SON/';
        } else {
            $base_path = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) ? '/REHBER/' : '/';
        }
    ?>
    <div class="nav-item" onclick="location.href='<?php echo $base_path; ?>'">
        <i class="fa-solid fa-location-dot"></i>
        <span><?php echo __('nearby_districts'); ?></span>
    </div>

    <div class="nav-item <?php echo (isset($active_tab) && $active_tab == 'services') ? 'active' : ''; ?>" onclick="app.switchTab('services', this)">
        <i class="fa-solid fa-hand-holding-heart"></i>
        <span><?php echo __('service_tab'); ?></span>
    </div>
    
    <div class="nav-item <?php echo (isset($active_tab) && $active_tab == 'profile') ? 'active' : ''; ?>"
         onclick="app.switchTab('profile', this)">
        <?php $is_logged_in = isset($_SESSION['user_id']); ?>
        <i class="fa-solid <?php echo $is_logged_in ? 'fa-user-check' : 'fa-user'; ?>" 
           style="<?php echo $is_logged_in ? '' : 'color: var(--text-secondary);'; ?>"></i>
        <span><?php echo __('profile_tab'); ?></span>
    </div>
</nav>

<?php 
// Ensure auth modal is available on all pages with bottom nav
// Use __DIR__ to get the absolute path to current file's directory, then find includes/ relative to project root
$_nav_dir = __DIR__; // = .../REHBER/includes
$_auth_modal_abs = $_nav_dir . DIRECTORY_SEPARATOR . 'auth_modal.php';
if (file_exists($_auth_modal_abs)) {
    include_once $_auth_modal_abs;
}
?>

<script>
// Sync login status for app.js navigation
if (window.app) {
    app.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
}
</script>
