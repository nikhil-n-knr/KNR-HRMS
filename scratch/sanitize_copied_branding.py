import os
import re

replacements = {
    r'KNR Office': 'OneHub Connect',
    r'KNR Portals': 'OneHub Connect Portals',
    r'KNR PROPERTY': 'PiSparrow Property',
    r'KNR STORAGE': 'PiSparrow Storage',
    r'KNR Hub': 'PiSparrow Hub',
    r'KNR CONNECT': 'ONEHUB CONNECT',
    r'KNR OPSCORE': 'ONEHUB CONNECT',
    r'@knrint\.com': '@pisparrow.com',
    r'@knr\.com': '@pisparrow.com',
}

def sanitize_file(filepath):
    with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
    
    original = content
    for pattern, repl in replacements.items():
        content = re.sub(pattern, repl, content)
        # Also handle lowercase variations for text references if needed
        # but let's stick to the specific definitions to avoid breaking variables
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Sanitized: {filepath}")

# Walk resources/js/Pages
for root, dirs, files in os.walk('/Volumes/Nikhil Drive/Nikhil/Codes/KNR-HRMS/resources/js/Pages'):
    # Skip Auth/Login.vue since it's already reverted/branded
    for file in files:
        if file.endswith('.vue'):
            path = os.path.join(root, file)
            if 'Auth/Login.vue' in path:
                continue
            sanitize_file(path)

# Walk app/Http/Controllers/Admin
for root, dirs, files in os.walk('/Volumes/Nikhil Drive/Nikhil/Codes/KNR-HRMS/app/Http/Controllers/Admin'):
    for file in files:
        if file.endswith('.php'):
            path = os.path.join(root, file)
            sanitize_file(path)
