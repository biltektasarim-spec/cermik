import os
import shutil

def copy_readable(src, dst):
    print(f"Kaynak: {src}")
    print(f"Hedef:  {dst}")
    
    if not os.path.exists(dst):
        os.makedirs(dst)
        
    copied_count = 0
    failed_count = 0
    
    for root, dirs, files in os.walk(src):
        # Hedef dizin yapısını oluştur
        rel_path = os.path.relpath(root, src)
        dest_dir = os.path.join(dst, rel_path)
        
        if not os.path.exists(dest_dir):
            try:
                os.makedirs(dest_dir)
            except:
                pass
                
        for name in files:
            src_file = os.path.join(root, name)
            dst_file = os.path.join(dest_dir, name)
            
            try:
                shutil.copy2(src_file, dst_file)
                copied_count += 1
            except Exception as e:
                # print(f"HATA: {src_file} kopyalanamadi: {e}")
                failed_count += 1
                
            if copied_count % 500 == 0:
                print(f"{copied_count} dosya kopyalandi...")

    print("\n--- TAMAMLANDI ---")
    print(f"Kopyalanan: {copied_count}")
    print(f"Hata/Kilitli: {failed_count}")
    print(f"\nArtik '{dst}' klasorunu sorunsuzca ziplebilirsiniz.")

if __name__ == "__main__":
    src_dir = r"C:\AppServ\www\REHBER"
    dst_dir = r"C:\AppServ\www\REHBER_KOPYA"
    copy_readable(src_dir, dst_dir)
