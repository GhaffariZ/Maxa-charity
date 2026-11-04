from scratch_find_kashan import poly_pts, dist_to_poly_boundary, point_in_poly

for target_y in [405, 410, 415, 418, 420]:
    row = [(dist_to_poly_boundary(x, target_y, poly_pts), x) for x in range(460, 505) if point_in_poly(x, target_y, poly_pts)]
    row.sort(reverse=True)
    if row:
        best_d, best_x = row[0]
        print(f"y={target_y}: best x={best_x} with distance to border = {best_d:.1f}px")
