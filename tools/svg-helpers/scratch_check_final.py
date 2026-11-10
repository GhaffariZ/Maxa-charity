import re
import sys
sys.stdout.reconfigure(encoding='utf-8')

with open('public_html/dashboard/components/branches/map-svg.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Check all paths
paths = re.findall(r'<path\b([^>]+)>', content)
print(f"Total <path> elements: {len(paths)}")
invalid_paths = []
for p in paths:
    id_m = re.search(r'id="([^"]+)"', p)
    d_m = re.search(r'd="([^"]+)"', p)
    pid = id_m.group(1) if id_m else 'no-id'
    d_val = d_m.group(1) if d_m else ''
    if len(d_val) < 15 or d_val.startswith('path'):
        invalid_paths.append((pid, d_val))

if invalid_paths:
    print("WARNING: Found invalid paths:", invalid_paths)
else:
    print("ALL paths have valid, complete SVG geometry coordinates!")

# 2. Check 6 active provinces
active_links = re.findall(r'<a\b[^>]*data-province="([^"]+)"[^>]*href="([^"]+)"', content)
print(f"\nActive links count ({len(active_links)}):")
for prov, href in active_links:
    print(f"  - {prov}: {href}")

# 3. Check 25 inactive provinces
inactive_items = re.findall(r'<g\b[^>]*class="[^"]*is-inactive[^"]*"[^>]*data-province="([^"]+)"', content)
print(f"\nInactive provinces count ({len(inactive_items)}):")
print(", ".join(inactive_items))

# 4. Check labels
labels = re.findall(r'<text\b[^>]*>([^<]+)</text>', content)
print(f"\nTotal labels ({len(labels)}):")
print(", ".join(labels))
