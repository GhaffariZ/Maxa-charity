const assert = require('assert');
const fs = require('fs');

console.log('Testing Campaign Service & Financial Management 502 Fix...');

// 1. Check campaign-service.php
const campaignServiceCode = fs.readFileSync('public_html/core/campaign-service.php', 'utf8');
assert.ok(campaignServiceCode.includes('public function hasManagementCredentials(): bool'), 'hasManagementCredentials method must exist');
assert.ok(campaignServiceCode.includes('public function hasPublicCredentials(): bool'), 'hasPublicCredentials method must exist');
assert.ok(campaignServiceCode.includes('function campaign_service_has_management_credentials(string $slug): bool'), 'campaign_service_has_management_credentials helper must exist');
assert.ok(campaignServiceCode.includes('function campaign_service_has_public_credentials(string $slug): bool'), 'campaign_service_has_public_credentials helper must exist');

// 2. Check financial-management.php
const financeCode = fs.readFileSync('public_html/dashboard/financial-management.php', 'utf8');
assert.ok(financeCode.includes('!$client->hasManagementCredentials()'), 'Must check hasManagementCredentials in financial-management.php endpoint');
assert.ok(financeCode.includes("'configured' => false"), 'Must return configured => false when credentials missing');
assert.ok(!financeCode.includes("if (http_response_code() < 400) http_response_code(502);"), 'Must not force 502 status code');
assert.ok(financeCode.includes('campaign_service_has_management_credentials((string)$branch[\'slug\'])'), 'Must filter financeSources by has_management_credentials');
assert.ok(financeCode.includes('if (!sources.length) {\r\n      renderAmountsAndCampaigns();\r\n      renderPayments();\r\n      return true;\r\n    }'), 'Frontend load() must handle empty sources gracefully without toast error');

// 3. Check campaign-status.php
const campaignStatusCode = fs.readFileSync('public_html/dashboard/campaign-status.php', 'utf8');
assert.ok(campaignStatusCode.includes('!$client->hasManagementCredentials()'), 'Must check hasManagementCredentials in campaign-status.php ajax');
assert.ok(campaignStatusCode.includes('campaign_service_has_management_credentials((string)$branch[\'slug\'])'), 'Must filter campaignSources by has_management_credentials in campaign-status.php');

console.log('PASS: All assertions passed successfully!');
