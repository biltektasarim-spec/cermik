import re

tables_to_restore = ['places', 'districts', 'businesses', 'events', 'announcements', 'hospitals', 'pharmacies', 'settings']
input_file = 'c:/AppServ/www/REHBER/database.sql'
output_file = 'c:/AppServ/www/REHBER/restore_data.sql'

with open(input_file, 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

with open(output_file, 'w', encoding='utf-8') as f:
    f.write("SET NAMES utf8mb4;\n")
    f.write("SET FOREIGN_KEY_CHECKS = 0;\n\n")
    
    for table in tables_to_restore:
        # Find the DROP TABLE, CREATE TABLE and INSERT INTO sections for each table
        # This is a simplified regex, assuming standard mysqldump format
        pattern = rf"--\n-- Table structure for table `{table}`\n--\n.*?-- Dumping data for table `{table}`\n--\n.*?(INSERT INTO `{table}` VALUES .*?;)"
        match = re.search(pattern, content, re.DOTALL)
        
        if match:
            # We want to DROP and RECREATE or just DELETE and INSERT?
            # To be safe and preserve structural changes if any, let's just DELETE and INSERT
            f.write(f"-- Restoring table: {table}\n")
            f.write(f"DELETE FROM `{table}`;\n")
            f.write(match.group(1) + "\n\n")
    
    f.write("SET FOREIGN_KEY_CHECKS = 1;\n")

print(f"Extraction complete. {output_file} created.")
