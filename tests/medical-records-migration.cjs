// Generate a MariaDB/MySQL probe using the exact legacy ALTER from migration 027.
// The probe uses a temporary table and synthetic records only; no application rows are read or written.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const migration = fs.readFileSync(path.join(root, 'database/migrations/027_upgrade_legacy_medical_records.sql'), 'utf8');
const match = migration.match(/SET @medical_legacy_alter = '((?:[^']|'')*)';/);
assert.ok(match, 'Legacy ALTER clause must be available for the real-engine data-preservation test.');
const alter = match[1].replaceAll("''", "'");
assert.ok(!/\b(?:DROP|DELETE|TRUNCATE)\b/i.test(alter), 'The upgrade cannot remove legacy rows or documents.');
assert.ok(!/FOREIGN_KEY_CHECKS\s*=\s*0/i.test(migration), 'Foreign key validation must stay enabled.');
const database = process.argv[2];
assert.ok(database && /^[a-zA-Z0-9_]+$/.test(database), 'Pass the application database name for the temporary-table probe.');
const table = '`' + database + '`.`macsa_medical_027_probe`';
const sql = `CREATE TEMPORARY TABLE ${table} (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, user_id INT NULL,
  full_name VARCHAR(255) NOT NULL, mobile VARCHAR(20) NOT NULL,
  age INT NULL, gender VARCHAR(50) NULL, province VARCHAR(100) NOT NULL, city VARCHAR(100) NOT NULL,
  cancer_type VARCHAR(100) NULL, diagnosis_status VARCHAR(100) NULL, description TEXT NULL,
  documents LONGTEXT NULL, created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
INSERT INTO ${table} (id,user_id,full_name,mobile,province,city,documents) VALUES
  (1,NULL,'Synthetic intake','09000000000','Test province','Test city','["legacy-test.pdf"]'),
  (2,7,'Synthetic second intake','09111111111','Test province','Test city',NULL);
ALTER TABLE ${table} ${alter};
UPDATE ${table} SET branch_id=1 WHERE branch_id IS NULL;
ALTER TABLE ${table} MODIFY COLUMN branch_id INT UNSIGNED NOT NULL;
SELECT CASE WHEN COUNT(*)=2 AND SUM(
  (id=1 AND user_id IS NULL AND phone='09000000000' AND documents='["legacy-test.pdf"]') OR
  (id=2 AND user_id=7 AND phone='09111111111' AND documents IS NULL)
)=2 AND SUM(branch_id=1 AND status='new')=2 THEN 'PASS: original ids, phones, nullable owners and documents preserved' ELSE 'FAIL' END AS migration_027_check FROM ${table};
INSERT INTO ${table} (user_id,branch_id,full_name,phone,province,city) VALUES (8,1,'New API intake','09222222222','Test province','Test city');
SELECT CASE WHEN COUNT(*)=3 AND MAX(id)=3 THEN 'PASS: new API-shaped intake inserts after upgrade' ELSE 'FAIL' END AS new_intake_check FROM ${table};
SHOW COLUMNS FROM ${table};
`;
const output = path.join(root, '.local/medical-records-027-probe.sql');
fs.mkdirSync(path.dirname(output), {recursive:true});
fs.writeFileSync(output, sql, 'utf8');
console.log('PASS safe ALTER extraction; temporary-table probe generated at ' + output);
