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

print("Points inside northern Isfahan (Kashan region):")
for y in range(395, 430, 5):
    inside_xs = [x for x in range(450, 520, 5) if point_in_poly(x, y, poly_pts)]
    if inside_xs:
        print(f"y={y}: xs from {min(inside_xs)} to {max(inside_xs)}")

# Let's check clearance from neighboring labels:
# Qom is at (430, 343) -> text at (430, 358)
# Markazi is at (370, 368)
# Isfahan is at (510, 468) -> text at (510, 483)
# Semnan is at (645, 275)
