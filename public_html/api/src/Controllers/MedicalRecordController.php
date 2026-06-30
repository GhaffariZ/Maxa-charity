<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Core\Database;

final class MedicalRecordController
{
    public function store(Request $request): void
    {
        $userId = $request->userId();

        $data = (new Validator($request->body))
            ->string('full_name', max: 255)
            ->string('mobile', max: 20)
            ->string('province', max: 100)
            ->string('city', max: 100)
            ->validated();
            
        // Optional fields
        $age = !empty($request->body['age']) ? (int) $request->body['age'] : null;
        $gender = !empty($request->body['gender']) ? substr($request->body['gender'], 0, 50) : null;
        $cancerType = !empty($request->body['cancer_type']) ? substr($request->body['cancer_type'], 0, 100) : null;
        $diagnosisStatus = !empty($request->body['diagnosis_status']) ? substr($request->body['diagnosis_status'], 0, 100) : null;
        $description = !empty($request->body['description']) ? $request->body['description'] : null;

        $documents = [];
        
        $uploadDir = dirname(__DIR__, 3) . '/uploads/medical_records';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_FILES['documents']['name']) && is_array($_FILES['documents']['name'])) {
            $fileCount = count($_FILES['documents']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['documents']['tmp_name'][$i];
                    $name = basename($_FILES['documents']['name'][$i]);
                    
                    // Generate a safe unique name
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
                        continue;
                    }
                    
                    $safeName = $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $dest = $uploadDir . '/' . $safeName;
                    
                    if (move_uploaded_file($tmpName, $dest)) {
                        $documents[] = '/uploads/medical_records/' . $safeName;
                    }
                }
            }
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare('
            INSERT INTO medical_records 
            (user_id, full_name, mobile, age, gender, province, city, cancer_type, diagnosis_status, description, documents) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        $stmt->execute([
            $userId,
            $data['full_name'],
            $data['mobile'],
            $age,
            $gender,
            $data['province'],
            $data['city'],
            $cancerType,
            $diagnosisStatus,
            $description,
            json_encode($documents)
        ]);

        Response::success(['message' => 'پرونده پزشکی با موفقیت ثبت شد.']);
    }
}
