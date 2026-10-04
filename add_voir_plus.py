#!/usr/bin/env python3
"""
Usage:
    python3 add_voir_plus.py <nom_vue>      # ex: python3 add_voir_plus.py ifpm
    python3 add_voir_plus.py <nom_vue> --restore
"""
import re, unicodedata, shutil, sys
from pathlib import Path

if len(sys.argv) < 2:
    print("Usage: python3 add_voir_plus.py <nom_vue> [--restore]")
    print("Exemple: python3 add_voir_plus.py ifpm")
    sys.exit(1)

view_name = sys.argv[1]
INDEX = Path(f"resources/views/{view_name}.blade.php")
BACKUP = Path(f"resources/views/{view_name}.blade.php.bak")

def slugify(text):
    text = unicodedata.normalize("NFKD", text).encode("ascii", "ignore").decode("ascii")
    text = text.lower()
    text = re.sub(r"[^a-z0-9]+", "-", text)
    return text.strip("-")

if "--restore" in sys.argv:
    if BACKUP.exists():
        shutil.move(BACKUP, INDEX)
        print(f"Restaure depuis {BACKUP}")
    else:
        print("Aucun backup a restaurer.")
    sys.exit(0)

if not INDEX.exists():
    print(f"ERREUR : {INDEX} introuvable.")
    sys.exit(1)

content = INDEX.read_text(encoding="utf-8")

if 'Voir plus<i class="fas fa-eye' in content:
    print("Les boutons 'Voir plus' sont deja presents.")
    sys.exit(0)

filieres = re.findall(r'data-filiere="([^"]+)"', content)
print(f"INFO : {len(filieres)} data-filiere detectes dans {INDEX}")
if not filieres:
    sys.exit(1)

pattern = re.compile(
    r'(data-filiere="([^"]+)"[\s\S]*?</a>)(?=\s*</div>\s*</div>\s*</div>)'
)
matches = pattern.findall(content)
print(f"INFO : {len(matches)} cartes correspondent au motif.")

if not matches:
    print("Motif HTML non reconnu. Envoie-moi le code d'une carte.")
    sys.exit(1)

shutil.copy(INDEX, BACKUP)
print(f"Backup cree : {BACKUP}")

count = 0
def replacer(match):
    global count
    full = match.group(1)
    slug = slugify(match.group(2))
    count += 1
    return full + (
        '\n                                    <a href="/formation/' + slug + '" class="th-btn mt-2"'
        '\n                                        style="width: 100%; background: #34c759; color: #fff; border-radius: 8px; padding: 10px; text-align: center; transition: all 0.3s ease;">Voir plus<i class="fas fa-eye ms-2"></i></a>'
    )

INDEX.write_text(pattern.sub(replacer, content), encoding="utf-8")
print(f"\nOK : {count} boutons 'Voir plus' ajoutes dans {INDEX}.")
print(f"Pour annuler : python3 add_voir_plus.py {view_name} --restore")
