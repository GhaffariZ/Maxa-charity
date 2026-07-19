<?php

declare(strict_types=1);

namespace Maksa\Controllers;

use Maksa\Auth\RefreshTokenService;
use Maksa\Core\Exceptions\ApiException;
use Maksa\Core\Request;
use Maksa\Core\Response;
use Maksa\Core\Validator;
use Maksa\Repositories\DonationRepository;
use Maksa\Repositories\NotificationRepository;
use Maksa\Repositories\ProfileRepository;
use Maksa\Repositories\UserRepository;
use Maksa\Support\Audit;
use Maksa\Support\AvatarStorage;
use Maksa\Support\Cookie;

final class UserController
{
    private ProfileRepository $profiles;

    public function __construct()
    {
        $this->profiles = new ProfileRepository();
    }

    // ---- GET /user/me -------------------------------------------------------
    public function me(Request $request): void
    {
        $profile = $this->profiles->fullProfile($request->userId());
        if ($profile === null) {
            throw ApiException::notFound('کاربر یافت نشد.');
        }
        Response::success(['user' => $this->shape($profile)]);
    }

    // ---- PATCH /user/me -----------------------------------------------------
    public function updateMe(Request $request): void
    {
        // Whitelist — mass-assignment defense. Email/status/tier are NOT editable here.
        $data = (new Validator($request->body))
            ->string('first_name', max: 100, required: false)
            ->string('last_name', max: 100, required: false)
            ->iranMobile('phone', required: false)
            ->string('postal_address', max: 1000, required: false)
            ->validated();

        $this->profiles->updateProfile($request->userId(), $data);
        Audit::log($request->userId(), 'profile_updated', $request->ip(), $request->userAgent(), ['fields' => array_keys($data)]);

        $profile = $this->profiles->fullProfile($request->userId());
        Response::success(['user' => $this->shape($profile ?? [])]);
    }

    // ---- DELETE /user/me ----------------------------------------------------
    public function deleteMe(Request $request): void
    {
        // Require the user to type a confirmation phrase (defense against accidents/CSRF-style).
        $data = (new Validator($request->body))->string('confirm', max: 50)->validated();
        if ($data['confirm'] !== 'DELETE') {
            throw ApiException::badRequest('برای حذف حساب، عبارت تأیید را ارسال کنید.', 'confirmation_required');
        }

        $userId = $request->userId();
        (new RefreshTokenService())->revokeAllForUser($userId);
        (new UserRepository())->delete($userId);
        Cookie::clearRefreshToken();
        Audit::log(null, 'account_deleted', $request->ip(), $request->userAgent(), ['user_id' => $userId]);

        Response::success(['message' => 'حساب کاربری شما حذف شد.']);
    }

    // ---- POST /user/me/avatar (multipart) ----------------------------------
    public function uploadAvatar(Request $request): void
    {
        $file = $_FILES['avatar'] ?? null;
        if (!is_array($file)) {
            throw ApiException::badRequest('فیلد تصویر (avatar) ارسال نشده است.', 'no_file');
        }
        $url = AvatarStorage::store($file, $request->userId());
        $this->profiles->setAvatarUrl($request->userId(), $url);
        Audit::log($request->userId(), 'avatar_updated', $request->ip(), $request->userAgent());

        Response::success(['avatar_url' => $url]);
    }

    // ---- GET /user/avatar/{file}  (public, guarded path) --------------------
    public function serveAvatar(Request $request): void
    {
        $resolved = AvatarStorage::resolve($request->params['file'] ?? '');
        if ($resolved === null) {
            throw ApiException::notFound('تصویر یافت نشد.');
        }
        [$path, $mime] = $resolved;

        if (!headers_sent()) {
            http_response_code(200);
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . (string) filesize($path));
            header('Cache-Control: private, max-age=86400');
            // This route returns an image, override the JSON-oriented CSP.
            header("Content-Security-Policy: default-src 'none'");
        }
        readfile($path);
        exit;
    }

    // ---- GET /user/notification-prefs --------------------------------------
    public function getPrefs(Request $request): void
    {
        Response::success(['preferences' => $this->profiles->notificationPrefs($request->userId())]);
    }

    // ---- PUT /user/notification-prefs --------------------------------------
    public function updatePrefs(Request $request): void
    {
        $data = (new Validator($request->body))
            ->bool('news')
            ->bool('impact_reports')
            ->bool('new_campaigns')
            ->validated();

        $this->profiles->updateNotificationPrefs($request->userId(), $data);
        Response::success(['preferences' => $this->profiles->notificationPrefs($request->userId())]);
    }

    // ---- GET /user/dashboard -----------------------------------------------
    public function dashboard(Request $request): void
    {
        $userId = $request->userId();
        $donations = new DonationRepository();
        $profile = $this->profiles->fullProfile($userId);

        Response::success([
            'stats'           => $donations->dashboardStats($userId),
            'recent_activity' => $donations->recentActivity($userId),
            'kindness_points' => (int) ($profile['kindness_points'] ?? 0),
            'tier'            => [
                'slug' => $profile['tier_slug'] ?? null,
                'name' => $profile['tier_name'] ?? null,
            ],
            'unread_notifications' => (new NotificationRepository())->unreadCount($userId),
        ]);
    }

    /** @param array<string,mixed> $p */
    // ---- GET /user/impacts --------------------------------------------------
    public function impacts(Request $request): void
    {
        $db = \Maksa\Core\Database::connection();
        
        $stmt = $db->prepare("SELECT user_id, amount FROM panel_donations WHERE status = 'success' AND campaign_id IS NULL ORDER BY paid_at ASC");
        $stmt->execute();
        $donations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $db->prepare("SELECT * FROM donation_impacts WHERE unit_price IS NOT NULL AND unit_price > 0 ORDER BY id ASC");
        $stmt->execute();
        $impacts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $userAllocations = [];
        $targetUserId = $request->userId();
        
        $impactIndex = 0;
        $currentImpactRemaining = 0;
        if (isset($impacts[0])) {
            $currentImpactRemaining = (int)$impacts[0]['quantity'] * (int)$impacts[0]['unit_price'];
        }

        foreach ($donations as $donation) {
            $amount = (int)$donation['amount'];
            $uid = (int)$donation['user_id'];
            
            while ($amount > 0 && $impactIndex < count($impacts)) {
                $allocation = min($amount, $currentImpactRemaining);
                
                if ($uid === $targetUserId) {
                    $impactId = $impacts[$impactIndex]['id'];
                    if (!isset($userAllocations[$impactId])) {
                        $userAllocations[$impactId] = 0;
                    }
                    $userAllocations[$impactId] += $allocation;
                }
                
                $amount -= $allocation;
                $currentImpactRemaining -= $allocation;
                
                if ($currentImpactRemaining <= 0) {
                    $impactIndex++;
                    if ($impactIndex < count($impacts)) {
                        $currentImpactRemaining = (int)$impacts[$impactIndex]['quantity'] * (int)$impacts[$impactIndex]['unit_price'];
                    }
                }
            }
        }

        $result = [];
        foreach ($impacts as $impact) {
            $impactId = $impact['id'];
            if (!isset($userAllocations[$impactId]) || $userAllocations[$impactId] <= 0) {
                continue;
            }
            
            $contribution = $userAllocations[$impactId];
            $unitPrice = (int)$impact['unit_price'];
            $units = floor($contribution / $unitPrice);
            $remainder = $contribution % $unitPrice;
            $percentage = round(($remainder / $unitPrice) * 100);
            
            $statText = "";
            if ($units > 0 && $percentage > 0) {
                $statText = $units . " عدد " . $impact['quantity_unit'] . " و مقداری (" . $percentage . " درصد) از یک " . $impact['quantity_unit'];
            } elseif ($units > 0) {
                $statText = $units . " عدد " . $impact['quantity_unit'];
            } elseif ($percentage > 0) {
                $statText = "مقداری از یک عدد " . $impact['quantity_unit'] . " (" . $percentage . " درصد)";
            }
            
            $statText = strtr($statText, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']);
            
            $result[] = [
                'id' => $impact['id'],
                'title' => $impact['title'],
                'description' => $impact['description'],
                'image' => $impact['image'],
                'stat_text' => $statText
            ];
        }
        
        Response::success(['impacts' => $result]);
    }

    private function shape(array $p): array
    {
        return [
            'id'              => (int) ($p['id'] ?? 0),
            'email'           => $p['email'] ?? null,
            'email_verified'  => ($p['email_verified_at'] ?? null) !== null,
            'status'          => $p['status'] ?? null,
            'first_name'      => $p['first_name'] ?? null,
            'last_name'       => $p['last_name'] ?? null,
            'phone'           => $p['phone'] ?? null,
            'avatar_url'      => $p['avatar_url'] ?? null,
            'postal_address'  => $p['postal_address'] ?? null,
            'kindness_points' => (int) ($p['kindness_points'] ?? 0),
            'tier'            => [
                'slug' => $p['tier_slug'] ?? null,
                'name' => $p['tier_name'] ?? null,
            ],
            'member_since'    => $p['member_since'] ?? null,
            'last_login_at'   => $p['last_login_at'] ?? null,
        ];
    }
}
