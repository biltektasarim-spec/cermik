<?php
$f = 'C:\AppServ\www\REHBER\admin\place_edit.php';
$c = file_get_contents($f);
$search = '<option value="ParkAndGarden" <?php echo $place[\'category\'] == \'ParkAndGarden\' ? \'selected\' : \'\'; ?>>Park ve Bahçeler</option>';
$replace = $search . "\n                            <option value=\"HotSpring\" <?php echo \$place['category'] == 'HotSpring' ? 'selected' : ''; ?>>Kaplıca</option>\n                            <option value=\"Kuruyemis\" <?php echo \$place['category'] == 'Kuruyemis' ? 'selected' : ''; ?>>Kuruyemiş Pazarı</option>";
$c = str_replace($search, $replace, $c);
file_put_contents($f, $c);
echo "Patched place_edit.php\n";
