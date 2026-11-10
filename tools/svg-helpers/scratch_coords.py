import re

with open('public_html/dashboard/components/branches/map-svg.php', 'r', encoding='utf-8') as f:
    content = f.read()

m = re.search(r'id="path36"[^>]*d="([^"]+)"', content)
if not m:
    print("path36 not found")
    exit()

d = m.group(1)

# Parse SVG path
# 'm 446.898,421.735 c 1.854,1.391 ...'
tokens = re.findall(r'([a-zA-Z]|[-0-9.]+)', d)

cur_x = 0.0
cur_y = 0.0
all_points = []
cmd = None
i = 0
while i < len(tokens):
    t = tokens[i]
    if t.isalpha():
        cmd = t
        i += 1
        continue
    
    if cmd == 'm':
        cur_x += float(tokens[i])
        cur_y += float(tokens[i+1])
        all_points.append((cur_x, cur_y))
        i += 2
        cmd = 'l' # subsequent numbers are lineto
    elif cmd == 'M':
        cur_x = float(tokens[i])
        cur_y = float(tokens[i+1])
        all_points.append((cur_x, cur_y))
        i += 2
        cmd = 'L'
    elif cmd == 'c':
        dx1, dy1 = float(tokens[i]), float(tokens[i+1])
        dx2, dy2 = float(tokens[i+2]), float(tokens[i+3])
        dx, dy = float(tokens[i+4]), float(tokens[i+5])
        cur_x += dx
        cur_y += dy
        all_points.append((cur_x, cur_y))
        i += 6
    elif cmd == 'C':
        cur_x = float(tokens[i+4])
        cur_y = float(tokens[i+5])
        all_points.append((cur_x, cur_y))
        i += 6
    elif cmd == 'l':
        cur_x += float(tokens[i])
        cur_y += float(tokens[i+1])
        all_points.append((cur_x, cur_y))
        i += 2
    elif cmd == 'L':
        cur_x = float(tokens[i])
        cur_y = float(tokens[i+1])
        all_points.append((cur_x, cur_y))
        i += 2
    elif cmd == 'h':
        cur_x += float(tokens[i])
        all_points.append((cur_x, cur_y))
        i += 1
    elif cmd == 'H':
        cur_x = float(tokens[i])
        all_points.append((cur_x, cur_y))
        i += 1
    elif cmd == 'v':
        cur_y += float(tokens[i])
        all_points.append((cur_x, cur_y))
        i += 1
    elif cmd == 'V':
        cur_y = float(tokens[i])
        all_points.append((cur_x, cur_y))
        i += 1
    elif cmd in ('z', 'Z'):
        i += 1
    else:
        i += 1

xs = [p[0] for p in all_points]
ys = [p[1] for p in all_points]
print(f"Isfahan polygon points count: {len(all_points)}")
print(f"Bounding Box: X=[{min(xs):.1f}, {max(xs):.1f}], Y=[{min(ys):.1f}, {max(ys):.1f}]")

# Let's inspect northern points (where Kashan is)
northern_pts = [p for p in all_points if p[1] < 450]
print(f"Northern points count (Y < 450): {len(northern_pts)}")
if northern_pts:
    n_xs = [p[0] for p in northern_pts]
    n_ys = [p[1] for p in northern_pts]
    print(f"Northern region (Kashan): X=[{min(n_xs):.1f}, {max(n_xs):.1f}], Y=[{min(n_ys):.1f}, {max(n_ys):.1f}]")

# Qom label is at cx=430, cy=343
# Central Isfahan label is currently at cx=510, cy=468
