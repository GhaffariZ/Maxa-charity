const fs = require('fs');

const source = fs.readFileSync('public_html/dashboard/components/branch-home/component.php', 'utf8');
for (const city of ['tehran', 'esfahan', 'kashan', 'mashhad', 'ahvaz', 'tabriz', 'qom']) {
  if (!source.includes(`key.indexOf('${city}')`)) throw new Error(`Missing icon for ${city}`);
}
if (!source.includes('badge.innerHTML=branchCityIcon')) throw new Error('Branch badge does not render the city icon');

console.log('branch city icons smoke check: OK');
