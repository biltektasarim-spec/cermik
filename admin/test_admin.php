<?php
$_SERVER['REQUEST_URI'] = '/admin/index.php';
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_role'] = 'SUPER_ADMIN';
$_SESSION['admin_district_id'] = 0;
require 'index.php';
