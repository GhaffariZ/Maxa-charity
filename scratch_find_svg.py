import subprocess
import re
import sys
sys.stdout.reconfigure(encoding='utf-8')

# Find all commits that modified map files
log = subprocess.check_output(['git', 'log', '--oneline', '--all'], encoding='utf-8')
print("All commits:")
print(log)

# Search for any commit with real path data (e.g. searching for '130.198,282.335')
grep_res = subprocess.check_output(['git', 'log', '-S130.198', '--oneline'], encoding='utf-8')
print("\nCommits containing '130.198':")
print(grep_res)
