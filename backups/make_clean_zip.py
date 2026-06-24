import os
import zipfile

def create_zip():
    folder = 'c:/AppServ/www/REHBER'
    zip_path = 'c:/AppServ/www/REHBER/REHBER_GUNCEL.zip'
    
    # Files and folders to strictly exclude to save space and avoid errors
    exclude_dirs = {'.agents', '.idea', 'node_modules', '_eski_sql_dosyalari', 'sessions', 'CermikRehberiApp'}
    exclude_files = {
        'REHBER_GUNCEL.zip', 'deploy_fixed.zip', 'deploy_payload.php', 
        'database_backup.sql', 'db_import_helper.php', 'auth_debug.log', 
        'sms_debug.log', 'rehber_hosting_raw.sql', 'firebase-adminsdk.json'
    }

    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(folder):
            # Mutate dirs in place to skip excluded directories
            dirs[:] = [d for d in dirs if d not in exclude_dirs]
            
            for file in files:
                if file in exclude_files:
                    continue
                    
                file_path = os.path.join(root, file)
                
                try:
                    # Calculate arcname (relative path inside zip)
                    arcname = os.path.relpath(file_path, folder)
                    zipf.write(file_path, arcname)
                except Exception as e:
                    print(f"Skipping {file_path}: {e}")

    print(f"Success! Created {zip_path}")
    print(f"Size: {os.path.getsize(zip_path)/1024/1024:.2f} MB")

if __name__ == '__main__':
    create_zip()
