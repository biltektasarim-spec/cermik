import os

def fix_paths(directory, slug):
    for filename in os.listdir(directory):
        if filename.endswith(".php"):
            filepath = os.path.join(directory, filename)
            with open(filepath, "r", encoding="utf-8") as f:
                content = f.read()
            
            # Fix require/include paths
            content = content.replace("'config.php'", "'../config.php'")
            content = content.replace('"config.php"', '"../config.php"')
            content = content.replace("'includes/", "'../includes/")
            content = content.replace('"includes/', '"../includes/')
            
            # Fix asset paths
            content = content.replace("'assets/", "'../assets/")
            content = content.replace('"assets/', '"../assets/')
            
            # Fix API paths
            content = content.replace("'api/", "'../api/")
            content = content.replace('"api/', '"../api/')
            
            # Specific fix for slug in index.php (which was district.php)
            if filename == "index.php":
                content = content.replace("$slug = isset($_GET['slug']) ? $_GET['slug'] : 'cermik';", f"$slug = '{slug}';")
                # Also fix the bottom nav links if they are relative
                content = content.replace('href="index.php"', 'href="../index.php"')
            
            with open(filepath, "w", encoding="utf-8") as f:
                f.write(content)

fix_paths("c:\\AppServ\\www\\REHBER\\cermik", "cermik")
fix_paths("c:\\AppServ\\www\\REHBER\\cungus", "cungus")
print("Paths fixed successfully.")
