<?php
$f = 'C:\AppServ\www\REHBER\assets\css\style.css';
$c = file_get_contents($f);
$c = str_replace('z-index: 1000;', 'z-index: 99999; pointer-events: auto;', $c);
$c = str_replace('.back-btn {', '.back-btn { z-index: 99999; ', $c);
file_put_contents($f, $c);
echo "CSS updated.\n";
