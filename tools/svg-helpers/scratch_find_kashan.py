import re

with open('public_html/dashboard/components/branches/map-svg.php', 'r', encoding='utf-8') as f:
    content = f.read()

m = re.search(r'id="path36"[^>]*d="([^"]+)"', content)
d = m.group(1)
tokens = re.findall(r'([a-zA-Z]|[-0-9.]+)', d)

cur_x, cur_y = 0.0, 0.0
poly_pts = []
cmd = None
i = 0
while i < len(tokens):
    t = tokens[i]
    if t.isalpha():
        cmd = t
        i += 1
        continue
    if cmd in ('m', 'l'):
        cur_x += float(tokens[i])
        cur_y += float(tokens[i+1])
        poly_pts.append((cur_x, cur_y))
        i += 2
    elif cmd in ('M', 'L'):
        cur_x = float(tokens[i])
        cur_y = float(tokens[i+1])
        poly_pts.append((cur_x, cur_y))
        i += 2
    elif cmd == 'c':
        cur_x += float(tokens[i+4])
        cur_y += float(tokens[i+5])
        poly_pts.append((cur_x, cur_y))
        i += 6
    elif cmd == 'C':
        cur_x = float(tokens[i+4])
        cur_y = float(tokens[i+5])
        poly_pts.append((cur_x, cur_y))
        i += 6
    elif cmd in ('z', 'Z'):
        i += 1
    else:
        i += 1

def point_in_poly(x, y, poly):
    n = len(poly)
    inside = False
    p1x, p1y = poly[0]
    for i in range(n + 1):
        p2x, p2y = poly[i % n]
        if y > min(p1y, p2y):
            if y <= max(p1y, p2y):
                if x <= max(p1x, p2x):
                    if p1y != p2y:
                        xinters = (y - p1y) * (p2x - p1x) / (p2y - p1y) + p1x
                    if p1x == p2x or x <= xinters:
                        inside = not inside
        p1x, p1y = p2x, p2y
    return inside

# Let's find the point with maximum distance to border in northern Isfahan (around y 395 to 420)
import math

def dist_to_segment(px, py, x1, y1, x2, y2):
    dx, dy = x2 - x1, y2 - y1
    if dx == 0 and dy == 0:
        return math.hypot(px - x1, py - y1)
    t = ((px - x1) * dx + (py - y1) * dy) / (dx*dx + dy*dy)
    t = max(0, min(1, t))
    nearest_x = x1 + t * dx
    nearest_y = y1 + t * dy
    return math.hypot(px - nearest_x, py - nearest_y)

def dist_to_poly_boundary(px, py, poly):
    min_d = float('inf')
    n = len(poly)
    for i in range(n):
        x1, y1 = poly[i]
        x2, y2 = poly[(i+1)%n]
        d = dist_to_segment(px, py, x1, y1, x2, y2)
        if d < min_d:
            min_d = d
    return min_d

candidates = []
for y in range(398, 425):
    for x in range(460, 505):
        if point_in_poly(x, y, poly_pts):
            d = dist_to_poly_boundary(x, y, poly_pts)
            candidates.append((d, x, y))

candidates.sort(reverse=True)
print("Top 10 deepest points in Northern Isfahan (furthest from borders):")
for d, x, y in candidates[:10]:
    print(f"dist={d:.2f}, x={x}, y={y}")
