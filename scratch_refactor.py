import os
import re

# Directory containing the controllers and models
app_dir = r"c:\Users\isaquelessa\Desktop\TCC\tcc v3\Site\app"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original = content
    
    # We will do some safe heuristics or just manual replacements for known queries.
    # Since writing a full SQL parser in a short script is hard, we can replace known patterns.
    # Actually, it's safer to just do manual edits if we can't guarantee safety.
    # But let's try some basic regex for $db->query("SELECT * FROM table ...")
    
    # 1. $db->query("SELECT * FROM table ORDER BY ...") -> $db->prepare("SELECT * FROM table WHERE id_empresa = ? ORDER BY ...")->execute([$_SESSION['empresa_id']])
    content = re.sub(
        r'\$db->query\("SELECT \w+ FROM (\w+)(.*?)"\);',
        r'$db->query("SELECT \1 FROM \2 WHERE id_empresa = " . intval($_SESSION[\'empresa_id\']) . " \3");',
        content
    )
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk(app_dir):
    for file in files:
        if file.endswith('.php'):
            process_file(os.path.join(root, file))
