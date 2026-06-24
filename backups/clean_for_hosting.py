import os
import shutil

source = r'c:\AppServ\www\REHBER'
backup = r'c:\AppServ\www\REHBER_SECURITY_BACKUP'

# List of precise files to MOVE to backup
to_move = [
    'database.sql', 'database_backup.sql', 'latest_database_export.sql', 
    'rehber_final.sql', 'rehber_hosting.sql', 'restore_data.sql', 
    'migrate_rotamiz.sql', 'rehber_final_v2.sql', 'rehber_final_v3.sql',
    'rehber_hosting_raw.sql', 'rehber_mariadb.sql', 'database_analytics.sql',
    'data.db', 'database.sqlite', 'rehber_db.sqlite',
    'config_remote.php', 'db_log.txt', 'record_check.txt', 'çerez.txt',
    'KVKK.txt', 'ROTAREHBER QR MENÜ OLUŞTURMA.docx'
]

# List of prefixes to DELETE or MOVE (I'll move them to backup to be safe)
prefixes_to_backup = [
    'check_', 'fix_', 'test_', 'debug_', 'migrate_', 'recreate_', 
    'tmp_', 'run_migration', 'import_db', 'db_import_helper',
    'generate_qr_list', 'list_eo', 'list_businesses', 'find_granpark',
    'fetch_eo', 'get_cid', 'create_icons', 'generate_icons', 'unzip_and_fix',
    'make_zip', 'make_payload', 'make_deployment', 'make_clean_zip', 
    'clean_remote', 'patch_', 'submission_new'
]

# Extensions to backup
exts_to_backup = ['.py', '.bat', '.zip']

files = os.listdir(source)

print(f"Cleanup started in {source}...")

for f in files:
    src_path = os.path.join(source, f)
    if os.path.isdir(src_path):
        if f in ['node_modules', '_eski_sql_dosyalari']:
            print(f"Moving directory: {f}")
            try:
                shutil.move(src_path, os.path.join(backup, f))
            except Exception as e:
                print(f"Error moving dir {f}: {e}")
        continue

    should_move = False
    
    if f in to_move:
        should_move = True
    
    for prefix in prefixes_to_backup:
        if f.startswith(prefix) and f.endswith('.php'):
            should_move = True
            break
            
    for ext in exts_to_backup:
        if f.endswith(ext):
            should_move = True
            break
            
    if should_move:
        print(f"Moving file: {f}")
        try:
            # If file already exists in backup, overwrite or rename
            dst_path = os.path.join(backup, f)
            if os.path.exists(dst_path):
                os.remove(dst_path)
            shutil.move(src_path, dst_path)
        except Exception as e:
            print(f"Error moving {f}: {e}")

print("Cleanup finished.")
