import os
import traceback

def check_files(root_dir):
    print(f"Checking readability for: {root_dir}")
    bad_files = []
    count = 0
    for root, dirs, files in os.walk(root_dir):
        for name in files:
            path = os.path.join(root, name)
            count += 1
            try:
                # Sadece okuma yetkisi var mı kontrol et
                with open(path, 'rb') as f:
                    pass
            except Exception as e:
                print(f"ERROR: {path}")
                print(f"REASON: {str(e)}")
                bad_files.append((path, str(e)))
            
            if count % 500 == 0:
                print(f"Processed {count} files...")
                
    print("\n--- RESULTS ---")
    if not bad_files:
        print("Success: All files are readable.")
    else:
        print(f"Failed: Found {len(bad_files)} problematic files.")
        for p, err in bad_files:
            print(f"- {p} ({err})")

if __name__ == "__main__":
    # Windows path format
    check_files(r"C:\AppServ\www\REHBER")
