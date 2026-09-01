import subprocess
import re
import sys
sys.stdout.reconfigure(encoding='utf-8')

# Get original SVG from earliest commit that added it
raw_svg = subprocess.check_output(['git', 'show', '9ae1a3e:public_html/dashboard/components/branches/map-svg.php'], encoding='utf-8')

print("Original raw SVG size:", len(raw_svg))

# Extract all paths from raw_svg
# Regex to match <path ...>
path_tags = re.findall(r'<path\b([^>]+)>', raw_svg)
path_dict = {}
for pstr in path_tags:
    id_m = re.search(r'id="([^"]+)"', pstr)
    d_m = re.search(r'd="([^"]+)"', pstr)
    if id_m and d_m:
        pid = id_m.group(1)
        d_val = d_m.group(1)
        path_dict[pid] = d_val

print(f"Extracted {len(path_dict)} real paths.")

# Extract all polygons
poly_tags = re.findall(r'<polygon\b([^>]+)>', raw_svg)
poly_dict = {}
for pstr in poly_tags:
    id_m = re.search(r'id="([^"]+)"', pstr)
    pts_m = re.search(r'points="([^"]+)"', pstr)
    if id_m and pts_m:
        pid = id_m.group(1)
        if pid == 'polygon17':  # Skip duplicate whole country overlay
            continue
        poly_dict[pid] = pts_m.group(1)

print(f"Extracted {len(poly_dict)} real island polygons.")

# Active provinces with exact requested URLs
active_meta = {
    'path42': {'name': 'تهران', 'url': '/tehran-branch', 'x': 475.0, 'y': 295.0, 'fs': 16},
    'path36': {'name': 'اصفهان', 'url': '/esfahan-branch', 'x': 510.0, 'y': 480.0, 'fs': 17},
    'path32': {'name': 'خوزستان', 'url': '/ahvaz-branch', 'x': 315.0, 'y': 585.0, 'fs': 16},
    'path49': {'name': 'آذربایجان شرقی', 'url': '/tabriz-branch', 'x': 185.0, 'y': 110.0, 'fs': 14},
    'path43': {'name': 'قم', 'url': '/qom-branch', 'x': 430.0, 'y': 355.0, 'fs': 15},
    'path35': {'name': 'خراسان رضوی', 'url': '/mashhad-branch', 'x': 905.0, 'y': 275.0, 'fs': 17},
}

# 25 Inactive provinces
inactive_meta = {
    'path38': {'name': 'آذربایجان غربی', 'x': 72.0, 'y': 160.0, 'fs': 13.5},
    'path45': {'name': 'اردبیل', 'x': 264.0, 'y': 92.0, 'fs': 13.5},
    'path27': {'name': 'البرز', 'x': 420.0, 'y': 260.0, 'fs': 12.5},
    'path41': {'name': 'ایلام', 'x': 195.0, 'y': 465.0, 'fs': 13.5},
    'path33': {'name': 'بوشهر', 'x': 460.0, 'y': 760.0, 'fs': 14},
    'path37': {'name': 'چهارمحال و بختیاری', 'x': 400.0, 'y': 535.0, 'fs': 12.5},
    'path31': {'name': 'خراسان جنوبی', 'x': 860.0, 'y': 475.0, 'fs': 15},
    'path21': {'name': 'خراسان شمالی', 'x': 795.0, 'y': 160.0, 'fs': 13.5},
    'path28': {'name': 'زنجان', 'x': 278.0, 'y': 230.0, 'fs': 14},
    'path47': {'name': 'سمنان', 'x': 645.0, 'y': 275.0, 'fs': 16},
    'path24': {'name': 'سیستان و بلوچستان', 'x': 1020.0, 'y': 800.0, 'fs': 16},
    'path48': {'name': 'فارس', 'x': 560.0, 'y': 720.0, 'fs': 16},
    'path26': {'name': 'قزوین', 'x': 360.0, 'y': 252.0, 'fs': 13.5},
    'path39': {'name': 'کردستان', 'x': 190.0, 'y': 285.0, 'fs': 14},
    'path46': {'name': 'کرمان', 'x': 785.0, 'y': 720.0, 'fs': 17},
    'path40': {'name': 'کرمانشاه', 'x': 180.0, 'y': 365.0, 'fs': 14},
    'path51': {'name': 'کهگیلویه و بویراحمد', 'x': 418.0, 'y': 625.0, 'fs': 12.5},
    'path23': {'name': 'گلستان', 'x': 675.0, 'y': 168.0, 'fs': 14},
    'path44': {'name': 'گیلان', 'x': 348.0, 'y': 155.0, 'fs': 14},
    'path29': {'name': 'لرستان', 'x': 282.0, 'y': 430.0, 'fs': 14},
    'path25': {'name': 'مازندران', 'x': 510.0, 'y': 235.0, 'fs': 14.5},
    'path34': {'name': 'مرکزی', 'x': 370.0, 'y': 368.0, 'fs': 14},
    'path30': {'name': 'هرمزگان', 'x': 700.0, 'y': 925.0, 'fs': 15},
    'path22': {'name': 'همدان', 'x': 292.0, 'y': 335.0, 'fs': 14},
    'path50': {'name': 'یزد', 'x': 645.0, 'y': 550.0, 'fs': 16}
}

svg_parts = []
svg_parts.append("""<svg
   version="1.1"
   id="Iran"
   x="0px"
   y="0px"
   width="1155.9022"
   height="1015.9907"
   viewBox="0 0 1155.9022 1015.9907"
   xml:space="preserve"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns="http://www.w3.org/2000/svg">
<style type="text/css">
/* Map Base & Clear Refined Separations */
.province-shape {
  stroke-linejoin: round !important;
  stroke-linecap: round !important;
  transition: fill 0.22s ease, stroke 0.22s ease, stroke-width 0.22s ease, filter 0.22s ease;
}

/* Inactive Provinces: Authentic Maxa Amber Yellow with Clear Distinct Border */
.province-shape.is-inactive,
g.is-inactive,
g.is-inactive path,
g.is-inactive polygon {
  fill: #f4a61e !important;
  stroke: #b86a00 !important;
  stroke-width: 1.05 !important;
  cursor: default !important;
  pointer-events: none !important;
  user-select: none !important;
  filter: none !important;
}

/* Active Provinces: Authentic Maxa Turquoise/Teal with Clear Border */
a.province-link {
  cursor: pointer !important;
  pointer-events: auto !important;
  outline: none;
  display: block;
}
a.province-link .province-shape.is-active {
  fill: #007b7a !important;
  stroke: #004544 !important;
  stroke-width: 1.25 !important;
  cursor: pointer !important;
  filter: drop-shadow(0 2px 6px rgba(0, 123, 122, 0.3));
}
a.province-link:hover .province-shape.is-active,
a.province-link:focus-visible .province-shape.is-active {
  fill: #10aeb8 !important;
  stroke: #002d2c !important;
  stroke-width: 1.6 !important;
  filter: drop-shadow(0 8px 18px rgba(16, 174, 184, 0.5)) !important;
}
a.province-link:active .province-shape.is-active {
  fill: #005958 !important;
}

/* Typography on Map */
.map-labels text {
  font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  text-anchor: middle;
  dominant-baseline: central;
  pointer-events: none;
  user-select: none;
}

/* Inactive Province Labels: Dark readable contrast on yellow */
.map-labels .lbl-inactive {
  fill: #451a03;
  font-weight: 700;
  opacity: 0.95;
}

/* Active Province Labels: Crisp White with shadow on blue */
.map-labels .lbl-active {
  fill: #ffffff;
  font-weight: 900;
  filter: drop-shadow(0 1.5px 3px rgba(0, 0, 0, 0.9));
}
.map-labels .lbl-active-dot {
  fill: #facc15;
  stroke: #ffffff;
  stroke-width: 1.2;
  filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.5));
}
</style>""")

# Map shapes layer
svg_parts.append('<g id="layercountry" transform="translate(-14.397255,-27.439554)">')

# 1. Output islands (paths 4392..4422)
island_path_ids = ['path4392', 'path4406', 'path4408', 'path4410', 'path4412', 'path4414', 'path4416', 'path4418', 'path4420', 'path4422']
for ipid in island_path_ids:
    if ipid in path_dict:
        svg_parts.append(f'  <g class="province-item is-inactive is-island"><path id="{ipid}" class="province-shape is-inactive" d="{path_dict[ipid]}" /></g>')

# 2. Output island polygons (polygon11..21)
for pid, pts in poly_dict.items():
    svg_parts.append(f'  <g class="province-item is-inactive is-island"><polygon id="{pid}" class="province-shape is-inactive" points="{pts}" /></g>')

# 3. Output 25 Inactive Provinces (paths 21..51)
for pid, meta in inactive_meta.items():
    if pid in path_dict:
        name = meta['name']
        svg_parts.append(f'  <g class="province-item is-inactive" data-province="{name}"><path id="{pid}" class="province-shape is-inactive" data-province="{name}" d="{path_dict[pid]}" /></g>')

# 4. Output 6 Active Provinces (wrapped in <a> with exact URL)
for pid, meta in active_meta.items():
    if pid in path_dict:
        name = meta['name']
        url = meta['url']
        svg_parts.append(f'  <a xlink:href="{url}" href="{url}" class="province-link is-active" data-province="{name}" title="شعبه {name} مکسا"><path id="{pid}" class="province-shape is-active" data-province="{name}" d="{path_dict[pid]}" /></a>')

svg_parts.append('</g>')

# 5. Output Labels layer (31 unique labels)
svg_parts.append('<g id="layerlabels" class="map-labels">')

# Inactive labels
for pid, meta in inactive_meta.items():
    name, x, y, fs = meta['name'], meta['x'], meta['y'], meta['fs']
    svg_parts.append(f'  <text x="{x:.1f}" y="{y:.1f}" font-size="{fs}px" class="lbl-inactive">{name}</text>')

# Active labels
for pid, meta in active_meta.items():
    name, x, y, fs = meta['name'], meta['x'], meta['y'], meta['fs']
    dot_y = y - 12 if fs >= 15 else y - 10
    svg_parts.append(f'  <g class="active-label-group" data-province="{name}">')
    svg_parts.append(f'    <circle cx="{x:.1f}" cy="{dot_y:.1f}" r="3.2" class="lbl-active-dot" />')
    svg_parts.append(f'    <text x="{x:.1f}" y="{y + 3:.1f}" font-size="{fs}px" class="lbl-active">{name}</text>')
    svg_parts.append(f'  </g>')

svg_parts.append('</g>')
svg_parts.append('</svg>')

final_svg = '\n'.join(svg_parts)

with open('public_html/dashboard/components/branches/map-svg.php', 'w', encoding='utf-8') as f:
    f.write(final_svg)

print("Properly restored full real coordinates for all 31 provinces and generated map-svg.php successfully!")
