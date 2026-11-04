# SVG Helpers & Coordinate Tools

This directory contains standalone Python utility scripts used for inspecting, validating, and calculating SVG paths and coordinates for the interactive Iran branch map (`public_html/dashboard/components/branches/map-svg.php`).

### Scripts:
- `scratch_check_final.py`: Validates all SVG `<path>` elements, active/inactive province links, and text labels.
- `scratch_coords.py`: Parses and analyzes SVG coordinate bounding boxes.
- `scratch_find_kashan.py`: Polygon boundary and point-in-polygon geometry algorithms for Kashan branch mapping.
- `scratch_find_kashan2.py`: Supplementary coordinate tests for Kashan point detection.
- `scratch_find_svg.py`: Utility to search for specific SVG elements.
- `scratch_restore_real_paths.py`: Restores original geometric SVG path coordinates for provinces.
- `scratch_test_kashan_pts.py`: Point-in-polygon unit tests for Kashan region coordinates.
