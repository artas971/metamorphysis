#!/usr/bin/env python3
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SVG_FILE = ROOT / 'templates' / 'partials' / 'logo.html.twig'

def main():
    if not SVG_FILE.exists():
        print(f"Fichier introuvable: {SVG_FILE}")
        return 1
    text = SVG_FILE.read_text(encoding='utf-8')

    # Trouve les balises <path .../> ou <path ...></path>
    pattern = re.compile(r'(<path\b[^>]*?(?:/>|></path>))', re.DOTALL | re.IGNORECASE)

    seen = set()
    to_remove = []

    for m in pattern.finditer(text):
        tag = m.group(1)
        d_match = re.search(r'\bd\s*=\s*"([^"]+)"', tag)
        class_match = re.search(r'\bclass\s*=\s*"([^"]+)"', tag)
        d_val = d_match.group(1).strip() if d_match else None
        class_val = class_match.group(1).strip() if class_match else None
        key = (d_val, class_val)
        if key in seen:
            to_remove.append(m.span(1))
        else:
            seen.add(key)

    if not to_remove:
        print('Aucun doublon trouvé.')
        return 0

    # Backup
    backup = SVG_FILE.with_suffix('.twig.bak')
    backup.write_text(text, encoding='utf-8')

    # Supprime les spans (de la fin vers le début)
    new_parts = []
    last = 0
    for start, end in sorted(to_remove):
        new_parts.append(text[last:start])
        last = end
    new_parts.append(text[last:])
    new_text = ''.join(new_parts)

    SVG_FILE.write_text(new_text, encoding='utf-8')
    print(f"Doublons supprimés: {len(to_remove)} (backup: {backup})")
    return 0

if __name__ == '__main__':
    raise SystemExit(main())
