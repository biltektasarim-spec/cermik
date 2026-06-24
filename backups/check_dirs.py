import os

root = "c:\\AppServ\\www\\REHBER"
for item in os.listdir(root):
    if os.path.isdir(os.path.join(root, item)):
        print(f"Dir: {item} (len: {len(item)})")
        # Print hex for special chars
        hex_name = ":".join("{:02x}".format(ord(c)) for c in item)
        print(f"  Hex: {hex_name}")
