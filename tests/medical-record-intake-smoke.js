const fs = require('fs');

const controller = fs.readFileSync('public_html/api/src/Controllers/MedicalRecordController.php', 'utf8');
const form = fs.readFileSync('public_html/dashboard/components/patient_intake/component.php', 'utf8');

if (/medical_intake|otp_required|auth\/otp/.test(controller + form)) {
  throw new Error('Medical intake must not use OTP.');
}
if (!controller.includes('&& $newUser')) {
  throw new Error('Guests must not receive a session for an existing account.');
}

console.log('medical record intake smoke check: OK');
