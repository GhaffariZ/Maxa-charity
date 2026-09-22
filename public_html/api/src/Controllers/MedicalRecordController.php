<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Auth\Jwt;
use Maksa\Auth\RefreshTokenService;
use Maksa\Core\Database;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\UserRepository;
use Maksa\Services\OtpService;
use Maksa\Support\Audit;
use Maksa\Support\Cookie;
use PDO;

final class MedicalRecordController
{
    public function create(Request $request): void
    {
        $data = (new Validator($request->body))
            ->string('full_name', min: 2, max: 200)
            ->string('phone', min: 10, max: 20)
            ->int('age', min: 1, max: 120, required: false)
            ->in('gender', ['male', 'female', 'unspecified'], required: false)
            ->string('province', min: 2, max: 100)
            ->string('city', min: 2, max: 100)
            ->string('cancer_type', max: 100, required: false)
            ->string('diagnosis_status', max: 100, required: false)
            ->string('description', max: 5000, required: false)
            ->bool('consent', required: true)
            ->validated();

        if (empty($data['consent'])) {
            throw ApiException::badRequest('ثبت پرونده نیازمند موافقت با ذخیره اطلاعات پزشکی است.', 'consent_required');
        }

        $phone = OtpService::normalizePhone($data['phone']);
        if (!OtpService::isValidPhone($phone)) {
            throw ApiException::badRequest('شماره تلفن همراه معتبر نیست.', 'invalid_phone');
        }
        $data['phone'] = $phone;
        $users = new UserRepository();
        $userId = $this->authenticatedUser($request, $users);
        $newUser = false;
        $accessToken = null;

        if ($userId === null) {
            [$firstName, $lastName] = $this->splitName($data['full_name']);
            $account = $users->createOrGetDonorByPhone($phone, $firstName, $lastName, null, false);
            $userId = $account['id'];
            $newUser = $account['is_new'];
        }

        $branchId = $this->hqBranchId();

        try {
            $recordId = $this->storeRecord($userId, $branchId, $data);
        } catch (\Throwable $e) {
            if ($newUser) {
                $users->delete($userId);
            }
            throw $e;
        }

        if ($request->authUserId === null && $newUser) {
            $accessToken = Jwt::issueAccessToken($userId);
            $refreshToken = (new RefreshTokenService())->issueNewFamily($userId, $request->ip(), $request->userAgent());
            Cookie::setRefreshToken($refreshToken);
        }

        Audit::log($userId, 'medical_record_created', $request->ip(), $request->userAgent(), [
            'record_id' => $recordId,
            'branch_id' => $branchId,
            'account_created' => $newUser,
        ]);

        Response::success([
            'record_id' => $recordId,
            'access_token' => $accessToken,
            'account_created' => $newUser,
            'user' => [
                'id' => $userId,
                'phone' => $phone,
                'full_name' => $data['full_name'],
            ],
            'message' => 'پرونده شما با موفقیت ثبت شد.',
        ], 201);
    }

    private function authenticatedUser(Request $request, UserRepository $users): ?int
    {
        $token = $request->bearerToken();
        if ($token === null) {
            return null;
        }

        try {
            $payload = Jwt::verify($token);
            $userId = (int) ($payload['sub'] ?? 0);
            $user = $userId > 0 ? $users->findById($userId) : null;
            if ($user === null || $user['status'] === 'suspended') {
                return null;
            }
            $request->authUserId = $userId;
            return $userId;
        } catch (ApiException) {
            return null;
        }
    }

    /** @param array<string,mixed> $data */
    private function storeRecord(int $userId, int $branchId, array $data): int
    {
        $savedFiles = [];

        try {
            return (int) Database::transaction(function (PDO $db) use ($userId, $branchId, $data, &$savedFiles): int {
                $stmt = $db->prepare(
                    'INSERT INTO medical_records
                    (user_id, branch_id, full_name, phone, age, gender, province, city, cancer_type, diagnosis_status, description)
                    VALUES (:user_id, :branch_id, :full_name, :phone, :age, :gender, :province, :city, :cancer_type, :diagnosis_status, :description)'
                );
                $stmt->execute([
                    ':user_id' => $userId,
                    ':branch_id' => $branchId,
                    ':full_name' => $data['full_name'],
                    ':phone' => $data['phone'],
                    ':age' => $data['age'] ?? null,
                    ':gender' => $data['gender'] ?? 'unspecified',
                    ':province' => $data['province'],
                    ':city' => $data['city'],
                    ':cancer_type' => $data['cancer_type'] ?? null,
                    ':diagnosis_status' => $data['diagnosis_status'] ?? null,
                    ':description' => $data['description'] ?? null,
                ]);

                $recordId = (int) $db->lastInsertId();
                $this->storeFiles($db, $recordId, $savedFiles);
                return $recordId;
            });
        } catch (\Throwable $e) {
            foreach ($savedFiles as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            throw $e;
        }
    }

    /** @param list<string> $savedFiles */
    private function storeFiles(PDO $db, int $recordId, array &$savedFiles): void
    {
        $files = $_FILES['documents'] ?? null;
        if (!is_array($files) || !isset($files['name'])) {
            return;
        }

        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        if (count($names) > 5) {
            throw ApiException::badRequest('حداکثر ۵ مدرک قابل بارگذاری است.', 'too_many_files');
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];
        $dir = __DIR__ . '/../../../../maksa-private/medical-records/' . $recordId;
        $insert = $db->prepare(
            'INSERT INTO medical_record_files (record_id, path, original_name, mime_type, size)
             VALUES (:record_id, :path, :original_name, :mime_type, :size)'
        );

        foreach ($names as $i => $originalName) {
            $error = (int) (is_array($files['error']) ? ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) : $files['error']);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                throw ApiException::badRequest('بارگذاری یکی از مدارک ناموفق بود.', 'upload_failed');
            }

            $tmp = (string) (is_array($files['tmp_name']) ? ($files['tmp_name'][$i] ?? '') : $files['tmp_name']);
            $size = (int) (is_array($files['size']) ? ($files['size'][$i] ?? 0) : $files['size']);
            if ($size <= 0 || $size > 5 * 1024 * 1024 || !is_uploaded_file($tmp)) {
                throw ApiException::badRequest('هر مدرک باید حداکثر ۵ مگابایت باشد.', 'invalid_upload');
            }

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
            if (!isset($allowed[$mime])) {
                throw ApiException::badRequest('فقط تصویر یا فایل PDF قابل بارگذاری است.', 'invalid_file_type');
            }

            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException('Medical upload directory could not be created.');
            }

            $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
            $diskPath = $dir . '/' . $fileName;
            if (!move_uploaded_file($tmp, $diskPath)) {
                throw new \RuntimeException('Medical document could not be stored.');
            }
            $savedFiles[] = $diskPath;

            $insert->execute([
                ':record_id' => $recordId,
                ':path' => $recordId . '/' . $fileName,
                ':original_name' => mb_substr(basename((string) $originalName), 0, 255),
                ':mime_type' => $mime,
                ':size' => $size,
            ]);
        }
    }

    private function hqBranchId(): int
    {
        $id = Database::connection()
            ->query("SELECT id FROM branches WHERE is_hq = 1 AND status = 'active' ORDER BY id LIMIT 1")
            ->fetchColumn();
        if ($id === false) {
            throw new \RuntimeException('HQ branch is not configured.');
        }
        return (int) $id;
    }

    /** @return array{0:string,1:string|null} */
    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), 2) ?: [];
        return [$parts[0] ?? $fullName, $parts[1] ?? null];
    }
}
