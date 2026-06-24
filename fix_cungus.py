import os

def replace_in_file(filepath, old_str, new_str):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    if old_str in content:
        new_content = content.replace(old_str, new_str)
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    return False

root_dir = "c:\\AppServ\\www\\REHBER"
cungus_dir = os.path.join(root_dir, "cungus")

replaces = [
    ("Çermik", "Çüngüş"),
    ("cermik", "cungus"),
    ("district.php?slug=cungus", "index.php"),
    ("district.php?slug=cermik", "../cermik/index.php")
]

for filename in os.listdir(cungus_dir):
    if filename.endswith(".php"):
        filepath = os.path.join(cungus_dir, filename)
        for old_s, new_s in replaces:
            replace_in_file(filepath, old_s, new_s)

print("Cungus replacements done.")
