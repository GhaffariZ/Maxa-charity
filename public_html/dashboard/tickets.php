<?php
/* ============================================================================
 *  سیستم تیکتینگ — پنل مکسا  (با بک‌اندِ دیتابیس، نه فقط ظاهر)
 * ----------------------------------------------------------------------------
 *  هرمِ دسترسیِ درختی:
 *    1) کاربر شعبه  → فقط تیکت‌های خودش را می‌بیند؛ به مدیر شعبه‌ی خود تیکت می‌زند
 *                     و در نخِ تیکتِ خودش پیگیری می‌کند (به تیکتِ دیگران دسترسی ندارد).
 *    2) مدیر شعبه   → به تیکتِ کاربرانِ شعبه‌اش پاسخ می‌دهد، می‌تواند به ستاد تیکت بزند،
 *                     و تیکتِ کاربر را با «ارجاع به ستاد مرکزی» به‌صورت خودکار بالا بفرستد.
 *    3) ستاد مرکزی  → به تیکت‌هایی که از سمتِ شعب می‌آید پاسخ می‌دهد.
 *
 *  این صفحه داخلِ iframe داشبورد بارگذاری می‌شود (SPA) و پوسته‌ی سبکِ خود را دارد.
 * ========================================================================== */
require_once __DIR__ . '/_guard.php';
/* توجه: این صفحه عمداً dash_require(...) ندارد — سیستم تیکتینگ برای همه‌ی کاربرانِ
 * واردشده‌ی پنل در دسترس است و سطحِ دسترسی *درونِ* صفحه و کوئری‌ها اعمال می‌شود. */

$U             = dash_user();
$ME            = (int)$U['id'];
$IS_SUPER      = dash_is_super();
$IS_BRANCH_ADM = dash_is_branch_admin();
$MY_ROLE       = $IS_SUPER ? 'super' : ($IS_BRANCH_ADM ? 'branch_admin' : 'user');
$MY_BRANCH     = dash_active_branch_id();
$MY_NAME       = $U['full_name'] ?: $U['username'];

/* ---------- کمک‌توابع ---------- */
if (!function_exists('tk_fa')) {
  function tk_fa($s){ return strtr((string)$s, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']); }
}
if (!function_exists('tk_initials')) {
  function tk_initials($n){ $n=trim((string)$n); if($n==='') return '؟'; $p=preg_split('/\s+/',$n); $a=mb_substr($p[0]??'',0,1,'UTF-8'); $b=isset($p[1])?mb_substr($p[1],0,1,'UTF-8'):''; return $a.$b; }
}
if (!function_exists('tk_color')) {
  function tk_color($id){ $pal=['#007b7a','#f4a61e','#8b5cf6','#16a37a','#e0556b','#2f9e9c','#dd8d0c','#7c4ddb']; return $pal[((int)$id) % count($pal)]; }
}
$ROLE_LABEL = ['user'=>'کاربر شعبه','branch_admin'=>'مدیر شعبه','super'=>'ستاد مرکزی'];
$PRIO_LABEL = ['low'=>'کم','normal'=>'عادی','high'=>'فوری'];
$STAT_LABEL = ['open'=>'در انتظار پاسخ','answered'=>'پاسخ داده شده','closed'=>'بسته‌شده'];

/* ---------- آیا جداولِ تیکتینگ ساخته شده‌اند؟ ---------- */
$SETUP_NEEDED = false;
try { $pdo->query('SELECT 1 FROM tickets LIMIT 1'); }
catch (Throwable $e) { $SETUP_NEEDED = true; }

/* ---------- بررسیِ دسترسیِ یک تیکت برای کاربرِ فعلی ---------- */
function tk_load(PDO $pdo, int $id): ?array {
  $s = $pdo->prepare('SELECT * FROM tickets WHERE id = ? LIMIT 1');
  $s->execute([$id]);
  return $s->fetch() ?: null;
}
function tk_can_view(?array $t, string $role, int $me, int $branch): bool {
  if (!$t) return false;
  if ($role === 'super')        return $t['target'] === 'hq';
  if ($role === 'branch_admin') return ((int)$t['branch_id'] === $branch || (int)$t['created_by'] === $me);
  return (int)$t['created_by'] === $me && $t['target'] === 'branch'; // user
}

/* ============================================================================
 *  پردازشِ درخواست‌ها (POST) — الگوی PRG؛ پیام از طریقِ session-flash منتقل می‌شود
 * ========================================================================== */
if (!$SETUP_NEEDED && $_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $action = (string)($_POST['action'] ?? '');

  /* --- علامتِ «خوانده‌شد» (با fetch؛ بدون ریدایرکت) --- */
  if ($action === 'read') {
    $tid = (int)($_POST['ticket_id'] ?? 0);
    $t   = tk_load($pdo, $tid);
    if (tk_can_view($t, $MY_ROLE, $ME, $MY_BRANCH)) {
      $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                     ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$tid, $ME]);
    }
    http_response_code(204); exit;
  }

  try {
    /* --- ثبتِ تیکتِ جدید --- */
    if ($action === 'create') {
      if ($MY_ROLE === 'super') { throw new RuntimeException('ستاد مرکزی تیکتِ جدید ثبت نمی‌کند.'); }
      $subject  = trim((string)($_POST['subject'] ?? ''));
      $body     = trim((string)($_POST['body'] ?? ''));
      $priority = (string)($_POST['priority'] ?? 'normal');
      if (!in_array($priority, ['low','normal','high'], true)) $priority = 'normal';
      if ($subject === '' || $body === '') {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'موضوع و متنِ تیکت را وارد کنید.'];
      } elseif (mb_strlen($subject) > 200) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'موضوع نباید بیش از ۲۰۰ نویسه باشد.'];
      } else {
        $target = $MY_ROLE === 'user' ? 'branch' : 'hq';
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO tickets (branch_id,subject,target,priority,status,created_by,creator_role,last_reply_at)
                       VALUES (?,?,?,?,\'open\',?,?,NOW())')
            ->execute([$MY_BRANCH, $subject, $target, $priority, $ME, $MY_ROLE]);
        $tid = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO ticket_messages (ticket_id,user_id,author_role,body) VALUES (?,?,?,?)')
            ->execute([$tid, $ME, $MY_ROLE, $body]);
        $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                       ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$tid, $ME]);
        $pdo->commit();
        dash_audit('ticket_created', ['ticket_id'=>$tid,'target'=>$target,'branch_id'=>$MY_BRANCH]);
        $_SESSION['ticket_flash'] = ['type'=>'ok','text'=>'تیکتِ شما با موفقیت ثبت شد.'];
      }
    }

    /* --- پاسخ در نخِ گفتگو --- */
    elseif ($action === 'reply') {
      $tid  = (int)($_POST['ticket_id'] ?? 0);
      $body = trim((string)($_POST['body'] ?? ''));
      $t    = tk_load($pdo, $tid);
      if (!tk_can_view($t, $MY_ROLE, $ME, $MY_BRANCH)) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'به این تیکت دسترسی ندارید.'];
      } elseif ($t['target'] === 'hq' && $MY_ROLE === 'branch_admin' && $t['escalated_from'] !== null) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'امکان ارسال پاسخ برای تیکت‌های ارسالی به ستاد وجود ندارد.'];
      } elseif ($body === '') {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'متنِ پاسخ خالی است.'];
      } else {
        // آیا نویسنده «پاسخ‌دهنده»ی این تیکت است؟ (مدیرشعبه برای تیکتِ branch، ستاد برای hq)
        $isResponder = ($t['target']==='hq' && $MY_ROLE==='super')
                    || ($t['target']==='branch' && $MY_ROLE==='branch_admin');
        $newStatus = $isResponder ? 'answered' : 'open';
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO ticket_messages (ticket_id,user_id,author_role,body) VALUES (?,?,?,?)')
            ->execute([$tid, $ME, $MY_ROLE, $body]);
        $pdo->prepare('UPDATE tickets SET status = ?, last_reply_at = NOW() WHERE id = ?')->execute([$newStatus, $tid]);
        $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                       ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$tid, $ME]);

        // همگام‌سازی تیکت ارجاع شده و اصلی
        if ($t['escalated_from'] !== null) {
          // الف) این تیکت ارجاع شده است (target = hq)، همگام‌سازی با تیکت اصلی
          $orig_tid = (int)$t['escalated_from'];
          $orig_t = tk_load($pdo, $orig_tid);
          if ($orig_t) {
            $orig_newStatus = 'answered';
            $pdo->prepare('INSERT INTO ticket_messages (ticket_id,user_id,author_role,body) VALUES (?,?,?,?)')
                ->execute([$orig_tid, $ME, $MY_ROLE, $body]);
            $pdo->prepare('UPDATE tickets SET status = ?, last_reply_at = NOW() WHERE id = ?')->execute([$orig_newStatus, $orig_tid]);
            $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                           ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$orig_tid, $ME]);
          }
        } else {
          // ب) این تیکت اصلی است، بررسی وجود تیکت ارجاع شده برای آن
          $st_escal = $pdo->prepare('SELECT * FROM tickets WHERE escalated_from = ? LIMIT 1');
          $st_escal->execute([$tid]);
          $escal_t = $st_escal->fetch();
          if ($escal_t) {
            $escal_tid = (int)$escal_t['id'];
            $escal_newStatus = 'open';
            $pdo->prepare('INSERT INTO ticket_messages (ticket_id,user_id,author_role,body) VALUES (?,?,?,?)')
                ->execute([$escal_tid, $ME, $MY_ROLE, $body]);
            $pdo->prepare('UPDATE tickets SET status = ?, last_reply_at = NOW() WHERE id = ?')->execute([$escal_newStatus, $escal_tid]);
            $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                           ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$escal_tid, $ME]);
          }
        }

        $pdo->commit();
        dash_audit('ticket_reply', ['ticket_id'=>$tid]);
        $_SESSION['ticket_flash'] = ['type'=>'ok','text'=>'پاسخِ شما ارسال شد.'];
      }
    }

    /* --- تغییرِ وضعیت (بستن/بازگشایی) --- */
    elseif ($action === 'status') {
      $tid    = (int)($_POST['ticket_id'] ?? 0);
      $status = (string)($_POST['status'] ?? '');
      $t      = tk_load($pdo, $tid);
      if (!tk_can_view($t, $MY_ROLE, $ME, $MY_BRANCH)) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'به این تیکت دسترسی ندارید.'];
      } elseif ($t['target'] === 'hq' && $MY_ROLE === 'branch_admin' && $t['escalated_from'] !== null) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'امکان تغییر وضعیت برای تیکت‌های ارسالی به ستاد وجود ندارد.'];
      } elseif (!in_array($status, ['open','closed'], true)) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'وضعیتِ نامعتبر.'];
      } else {
        // بررسی دسترسی ویژه بستن و بازگشایی تیکت
        $allowStatusChange = false;

        if ($status === 'closed') {
          // کسی که تیکت را ایجاد کرده (چه کاربر، چه ادمین شعبه) نمی‌تواند آن را ببندد
          if ((int)$t['created_by'] !== $ME) {
            $allowStatusChange = true;
          }
        } elseif ($status === 'open') {
          // بازگشایی تیکت:
          if ($MY_ROLE === 'super') {
            // مدیر ستاد همیشه می‌تواند تیکت‌های ستاد را بازگشایی کند
            $allowStatusChange = ($t['target'] === 'hq');
          } elseif ($MY_ROLE === 'branch_admin') {
            // ادمین شعبه فقط در صورتی که تیکت مربوط به شعبه باشد و ارجاع داده نشده باشد می‌تواند بازگشایی کند
            if ($t['target'] === 'branch') {
              $chk = $pdo->prepare('SELECT id FROM tickets WHERE escalated_from = ? LIMIT 1');
              $chk->execute([$tid]);
              if (!$chk->fetch()) {
                $allowStatusChange = true;
              }
            }
          }
          // کاربران عادی هیچ‌گاه نمی‌توانند تیکت را بازگشایی کنند
        }

        if (!$allowStatusChange) {
          $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'شما اجازه تغییر وضعیت این تیکت را ندارید.'];
        } else {
          $pdo->beginTransaction();
          $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$status, $tid]);

          // همگام‌سازی وضعیت بین تیکت ارجاع شده و اصلی
          if ($t['escalated_from'] !== null) {
            // الف) این تیکت ارجاع شده است، همگام‌سازی با تیکت اصلی
            $orig_tid = (int)$t['escalated_from'];
            $orig_t = tk_load($pdo, $orig_tid);
            if ($orig_t) {
              $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$status, $orig_tid]);
            }
          } else {
            // ب) این تیکت اصلی است، همگام‌سازی با تیکت ارجاع شده (در صورت وجود)
            $st_escal = $pdo->prepare('SELECT id FROM tickets WHERE escalated_from = ? LIMIT 1');
            $st_escal->execute([$tid]);
            $escal_t = $st_escal->fetch();
            if ($escal_t) {
              $escal_tid = (int)$escal_t['id'];
              $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$status, $escal_tid]);
            }
          }

          $pdo->commit();
          dash_audit('ticket_status', ['ticket_id'=>$tid,'status'=>$status]);
          $_SESSION['ticket_flash'] = ['type'=>'ok','text'=> $status==='closed' ? 'تیکت بسته شد.' : 'تیکت بازگشایی شد.'];
        }
      }
    }

    /* --- ارجاع به ستاد مرکزی (فقط مدیر شعبه، روی تیکتِ کاربرانِ شعبه) --- */
    elseif ($action === 'escalate') {
      $tid = (int)($_POST['ticket_id'] ?? 0);
      $t   = tk_load($pdo, $tid);
      $okScope = $MY_ROLE === 'branch_admin' && $t && $t['target']==='branch' && (int)$t['branch_id']===$MY_BRANCH;
      if (!$okScope) {
        $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'امکانِ ارجاعِ این تیکت وجود ندارد.'];
      } else {
        $chk = $pdo->prepare('SELECT id FROM tickets WHERE escalated_from = ? LIMIT 1');
        $chk->execute([$tid]);
        if ($chk->fetch()) {
          $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'این تیکت پیش‌تر به ستاد ارجاع شده است.'];
        } else {
          // پیام‌های تیکتِ کاربر + نامِ سازنده
          $all_msgs_st = $pdo->prepare('SELECT m.user_id, m.author_role, m.body, m.created_at, du.full_name, du.username
                                         FROM ticket_messages m JOIN dashboard_users du ON du.id = m.user_id
                                        WHERE m.ticket_id = ? ORDER BY m.id ASC');
          $all_msgs_st->execute([$tid]);
          $all_msgs = $all_msgs_st->fetchAll();

          $pdo->beginTransaction();
          $pdo->prepare('INSERT INTO tickets (branch_id,subject,target,priority,status,created_by,creator_role,escalated_from,last_reply_at)
                         VALUES (?,?,\'hq\',?,\'open\',?,\'branch_admin\',?,NOW())')
              ->execute([$MY_BRANCH, $t['subject'], $t['priority'], $ME, $tid]);
          $nid = (int)$pdo->lastInsertId();

          $insert_msg_st = $pdo->prepare('INSERT INTO ticket_messages (ticket_id,user_id,author_role,body,created_at) VALUES (?,?,?,?,?)');
          foreach ($all_msgs as $index => $m) {
            $body = (string)$m['body'];
            if ($index === 0) {
              $who = trim((string)($m['full_name'] ?: $m['username'])) ?: 'کاربر شعبه';
              $body = '↪ ارجاع از تیکتِ «' . $who . '» (شماره ' . $tid . '):' . "\n\n" . $body;
            }
            $insert_msg_st->execute([$nid, $m['user_id'], $m['author_role'], $body, $m['created_at']]);
          }

          $pdo->prepare('INSERT INTO ticket_reads (ticket_id,user_id,last_read_at) VALUES (?,?,NOW())
                         ON DUPLICATE KEY UPDATE last_read_at = NOW()')->execute([$nid, $ME]);
          $pdo->commit();
          dash_audit('ticket_escalated', ['from'=>$tid,'to'=>$nid]);
          $_SESSION['ticket_flash'] = ['type'=>'ok','text'=>'تیکت به‌صورتِ خودکار به ستاد مرکزی ارجاع شد.'];
        }
      }
    }
  } catch (Throwable $ex) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    $_SESSION['ticket_flash'] = ['type'=>'err','text'=>'خطا در پردازشِ درخواست. دوباره تلاش کنید.'];
  }

  header('Location: tickets.php'); exit;
}

/* ---------- پیامِ flash ---------- */
$FLASH = $_SESSION['ticket_flash'] ?? null;
unset($_SESSION['ticket_flash']);

/* ============================================================================
 *  واکشیِ تیکت‌ها بر اساسِ نقش
 * ========================================================================== */
$tickets = []; $msgsByTicket = []; $readsByTicket = [];
if (!$SETUP_NEEDED) {
  if ($MY_ROLE === 'super') {
    $where = "t.target = 'hq'"; $params = [];
  } elseif ($MY_ROLE === 'branch_admin') {
    $where = "((t.branch_id = ? AND t.target = 'branch') OR (t.branch_id = ? AND t.target = 'hq' AND (t.status <> 'closed' OR t.escalated_from IS NULL)))";
    $params = [$MY_BRANCH, $MY_BRANCH];
  } else { // user
    $where = "t.created_by = ? AND t.target = 'branch'"; $params = [$ME];
  }
  $sql = "SELECT t.*, du.full_name, du.username, b.name AS branch_name,
                 (SELECT COUNT(*) FROM tickets c WHERE c.escalated_from = t.id) AS child_count
            FROM tickets t
            JOIN dashboard_users du ON du.id = t.created_by
            JOIN branches b        ON b.id  = t.branch_id
           WHERE $where
           ORDER BY (t.status = 'closed') ASC, t.last_reply_at DESC, t.id DESC";
  $st = $pdo->prepare($sql);
  $st->execute($params);
  $tickets = $st->fetchAll();

  if ($tickets) {
    $ids = array_map(static fn($r)=>(int)$r['id'], $tickets);
    $ph  = implode(',', array_fill(0, count($ids), '?'));

    $ms = $pdo->prepare("SELECT m.*, du.full_name, du.username
                           FROM ticket_messages m JOIN dashboard_users du ON du.id = m.user_id
                          WHERE m.ticket_id IN ($ph) ORDER BY m.id ASC");
    $ms->execute($ids);
    foreach ($ms->fetchAll() as $m) { $msgsByTicket[(int)$m['ticket_id']][] = $m; }

    $rs = $pdo->prepare("SELECT ticket_id, last_read_at FROM ticket_reads WHERE user_id = ? AND ticket_id IN ($ph)");
    $rs->execute(array_merge([$ME], $ids));
    foreach ($rs->fetchAll() as $r) { $readsByTicket[(int)$r['ticket_id']] = $r['last_read_at']; }
  }
}

/* ---------- آماده‌سازیِ آیتم‌ها برای نمایش ---------- */
$VIEW = [];
foreach ($tickets as $t) {
  if ($MY_ROLE === 'branch_admin' && $t['target'] === 'branch' && $t['child_count'] > 0) {
    continue;
  }
  $tid   = (int)$t['id'];
  $msgs  = $msgsByTicket[$tid] ?? [];
  $last  = $msgs ? end($msgs) : null;
  $myRead = $readsByTicket[$tid] ?? null;
  $unread = false;
  if ($last && (int)$last['user_id'] !== $ME) {
    if ($myRead === null || strtotime((string)$last['created_at']) > strtotime((string)$myRead)) $unread = true;
  }
  $box = $MY_ROLE === 'branch_admin' ? ($t['target']==='branch' ? 'inbox' : ($t['escalated_from'] !== null ? 'escalated' : 'sent'))
       : ($MY_ROLE === 'super' ? 'inbox' : 'mine');
  $VIEW[] = ['t'=>$t,'msgs'=>$msgs,'unread'=>$unread,'box'=>$box,'creator'=>($t['full_name'] ?: $t['username'])];
}

/* عنوان و زیرعنوانِ صفحه بر اساسِ نقش */
if ($MY_ROLE === 'super') {
  $H_TITLE = 'تیکت‌های شعب'; $H_SUB = 'به تیکت‌هایی که از سمتِ شعب به ستاد مرکزی ارسال می‌شود پاسخ دهید.';
} elseif ($MY_ROLE === 'branch_admin') {
  $H_TITLE = 'تیکت‌ها'; $H_SUB = 'به تیکتِ کاربرانِ شعبه‌ی خود پاسخ دهید و با ستاد مرکزی در ارتباط باشید.';
} else {
  $H_TITLE = 'تیکت‌های من'; $H_SUB = 'درخواست‌های خود را برای مدیرِ شعبه ثبت و پیگیری کنید.';
}
$CAN_CREATE = $MY_ROLE !== 'super';
$CREATE_TO  = $MY_ROLE === 'user' ? 'مدیرِ شعبه' : 'ستاد مرکزی';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تیکت‌ها | پنل مکسا</title>
<script>try{if(localStorage.getItem('maxa-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
:root{
  --color-primary:#007b7a; --color-primary-dark:#006665; --color-primary-light:#4fb2b0;
  --color-secondary:#f4a61e; --color-secondary-dark:#b9760a; --color-text:#2f3437; --color-muted:#9d9d9d;
  --color-border:#e6e8ea; --color-bg:#f8f9fa; --color-surface:#ffffff;
  --success:#16a37a; --danger:#e0556b; --violet:#7c4ddb;
  --primary-08:rgba(0,123,122,.08); --primary-12:rgba(0,123,122,.12);
  --secondary-12:rgba(244,166,30,.16); --danger-12:rgba(224,85,107,.12);
  --success-12:rgba(22,163,122,.14); --violet-12:rgba(124,77,219,.14);
  --radius-sm:12px; --radius:18px; --radius-lg:24px;
  --shadow-sm:0 1px 2px rgba(16,40,40,.04),0 2px 5px rgba(16,40,40,.05);
  --shadow-md:0 4px 14px rgba(16,40,40,.06),0 2px 6px rgba(16,40,40,.04);
  --shadow-lg:0 20px 44px -14px rgba(0,102,101,.20),0 8px 20px -10px rgba(16,40,40,.12);
  --ease:cubic-bezier(.4,0,.2,1);
}
:root[data-theme="dark"]{
  --color-text:#e7ecee; --color-muted:#8e989d; --color-border:#2a343a;
  --color-bg:#0f1518; --color-surface:#19232a; --color-secondary-dark:#e0a528;
  --primary-08:rgba(79,178,176,.10); --primary-12:rgba(79,178,176,.16);
  --secondary-12:rgba(244,166,30,.18); --danger-12:rgba(224,85,107,.16);
  --success-12:rgba(22,163,122,.18); --violet-12:rgba(124,77,219,.20);
  --shadow-sm:0 1px 2px rgba(0,0,0,.4),0 2px 6px rgba(0,0,0,.3);
  --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.35);
  --shadow-lg:0 24px 48px -16px rgba(0,0,0,.62),0 10px 24px -12px rgba(0,0,0,.5);
  color-scheme:dark; background-color:var(--color-bg);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Vazirmatn',sans-serif;background:var(--color-bg);color:var(--color-text);font-size:14px;line-height:1.7;-webkit-font-smoothing:antialiased;min-height:100vh;padding:28px 22px;transition:background .3s,color .3s}
*::-webkit-scrollbar{width:9px;height:9px}
*::-webkit-scrollbar-thumb{background:rgba(0,123,122,.20);border-radius:99px;border:2px solid transparent;background-clip:padding-box}
[data-theme="dark"] *::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);background-clip:padding-box}

.tk-wrap{max-width:920px;margin:0 auto}

.tk-head{display:flex;align-items:center;gap:16px;margin-bottom:20px}
.tk-head-ic{width:54px;height:54px;border-radius:16px;flex-shrink:0;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--color-primary-light),var(--color-primary));box-shadow:0 12px 24px -10px rgba(0,123,122,.6)}
.tk-head-ic svg{width:27px;height:27px}
.tk-head h1{font-size:22px;font-weight:800;letter-spacing:-.01em}
.tk-head p{font-size:13px;color:var(--color-muted);margin-top:3px}
.tk-head .tk-spacer{flex:1}

/* دکمه‌ها */
.btn{font-family:inherit;font-weight:700;border:none;cursor:pointer;border-radius:12px;display:inline-flex;align-items:center;gap:7px;
  transition:filter .2s,transform .14s,box-shadow .22s,border-color .2s}
.btn svg{width:16px;height:16px}
.btn:active{transform:scale(.97)}
.btn-primary{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;padding:11px 18px;font-size:13px;box-shadow:0 12px 22px -12px rgba(0,123,122,.8)}
.btn-primary:hover{transform:translateY(-1px)}
.btn-ghost{background:var(--color-surface);color:var(--color-text);border:1px solid var(--color-border);padding:10px 15px;font-size:12.5px}
.btn-ghost:hover{border-color:var(--color-primary-light)}
.btn-sm{padding:7px 13px;font-size:12px;border-radius:10px}

/* پیام flash */
.tk-flash{border-radius:13px;padding:12px 16px;font-size:13px;font-weight:700;margin-bottom:18px;line-height:1.8;display:flex;align-items:center;gap:9px}
.tk-flash svg{width:18px;height:18px;flex-shrink:0}
.tk-flash.ok{background:var(--success-12);color:var(--success);border:1px solid rgba(22,163,122,.25)}
.tk-flash.err{background:var(--danger-12);color:var(--danger);border:1px solid rgba(224,85,107,.25)}

/* کادرِ ثبتِ تیکتِ جدید */
.tk-new{margin-bottom:20px}
.tk-new-panel{background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);
  padding:20px;margin-top:14px;animation:tkIn .25s var(--ease)}
.tk-new-panel[hidden]{display:none}
@keyframes tkIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
.tk-field{margin-bottom:14px}
.tk-field label{display:block;font-size:12.5px;font-weight:700;margin-bottom:7px;color:#5b6469}
[data-theme="dark"] .tk-field label{color:var(--color-muted)}
.tk-field input[type=text],.tk-field select,.tk-field textarea{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);
  background:var(--color-bg);border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;line-height:1.9;
  transition:border-color .2s,box-shadow .2s,background .2s}
.tk-field input:focus,.tk-field select:focus,.tk-field textarea:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.tk-field textarea{resize:vertical;min-height:96px}
.tk-grid2{display:grid;grid-template-columns:1fr 200px;gap:14px}
.tk-form-foot{display:flex;align-items:center;gap:12px;justify-content:space-between;flex-wrap:wrap;margin-top:6px}
.tk-form-foot .to{font-size:12px;color:var(--color-muted);font-weight:700}
.tk-form-foot .to b{color:var(--color-primary-dark)}

/* جعبه‌ها (دریافتی/ارسالی) — برای مدیر شعبه */
.tk-boxes{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.tk-box-btn{font-family:inherit;cursor:pointer;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:13px;
  padding:10px 16px;font-size:13px;font-weight:700;color:var(--color-muted);display:inline-flex;align-items:center;gap:8px;transition:border-color .2s,color .2s}
.tk-box-btn b{font-variant-numeric:tabular-nums}
.tk-box-btn:hover{border-color:var(--color-primary-light)}
.tk-box-btn.active{border-color:var(--color-primary);color:var(--color-primary-dark);background:var(--primary-08)}

/* تب‌های فیلتر وضعیت */
.tk-tabs{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px}
.tk-tab{font-family:inherit;text-align:start;cursor:pointer;background:var(--color-surface);border:1.5px solid var(--color-border);border-radius:16px;
  padding:13px 15px;box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:11px;transition:border-color .2s,box-shadow .25s,transform .15s var(--ease)}
.tk-tab:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
.tk-tab.active{border-color:var(--color-primary)}
.tk-tab-ic{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;flex-shrink:0}
.tk-tab-ic svg{width:20px;height:20px}
.tk-tab b{font-size:20px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;display:block}
.tk-tab span{font-size:11px;color:var(--color-muted);font-weight:600;margin-top:4px;display:block;line-height:1.5}
.ic-all{background:var(--primary-08);color:var(--color-primary)}
.ic-open{background:var(--secondary-12);color:var(--color-secondary-dark)}
.ic-answered{background:var(--violet-12);color:var(--violet)}
.ic-closed{background:var(--success-12);color:var(--success)}

/* کارت‌های تیکت */
.tk-list{display:flex;flex-direction:column;gap:14px}
.tk-card{background:var(--color-surface);border:1px solid var(--color-border);border-radius:var(--radius);box-shadow:var(--shadow-sm);
  padding:18px 20px;transition:box-shadow .25s var(--ease),border-color .2s;position:relative;display:flex;gap:15px}
.tk-card:hover{box-shadow:var(--shadow-md)}
.tk-card.unread{border-inline-start:4px solid var(--color-secondary)}
.tk-card.closed{opacity:.82}
.tk-av{width:46px;height:46px;border-radius:14px;flex-shrink:0;display:grid;place-items:center;color:#fff;font-weight:700;font-size:15px;box-shadow:var(--shadow-sm)}
.tk-body{flex:1;min-width:0}
.tk-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
.tk-subject{font-weight:800;font-size:15px;line-height:1.6;word-break:break-word}
.tk-num{font-size:11px;color:var(--color-muted);font-weight:700;white-space:nowrap}
.tk-date{font-size:11.5px;color:var(--color-muted);font-weight:600;white-space:nowrap;flex-shrink:0}
.tk-tags{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:9px}
.tk-badge{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px;white-space:nowrap}
.tk-badge .dot{width:6px;height:6px;border-radius:50%}
.prio-low{background:var(--primary-08);color:var(--color-primary-dark)} .prio-low .dot{background:var(--color-primary)}
.prio-normal{background:var(--violet-12);color:var(--violet)} .prio-normal .dot{background:var(--violet)}
.prio-high{background:var(--danger-12);color:var(--danger)} .prio-high .dot{background:var(--danger)}
.st-open{background:var(--secondary-12);color:var(--color-secondary-dark)} .st-open .dot{background:var(--color-secondary)}
.st-answered{background:var(--success-12);color:var(--success)} .st-answered .dot{background:var(--success)}
.st-closed{background:rgba(157,157,157,.16);color:var(--color-muted)} .st-closed .dot{background:var(--color-muted)}
.tk-chip{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px;background:var(--color-bg);color:var(--color-muted);border:1px solid var(--color-border)}
.tk-chip svg{width:12px;height:12px}
.tk-chip.esc{background:var(--secondary-12);color:var(--color-secondary-dark);border-color:transparent}
.tk-chip.escin{background:var(--violet-12);color:var(--violet);border-color:transparent}
.tk-chip.new{background:var(--danger-12);color:var(--danger);border-color:transparent}
.tk-preview{font-size:13px;color:var(--color-text);line-height:1.85;margin-top:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.tk-foot{display:flex;align-items:center;gap:9px;margin-top:13px;flex-wrap:wrap}
.tk-count{font-size:11.5px;color:var(--color-muted);font-weight:700;margin-inline-start:auto}

/* نخِ گفتگو (بازشونده) */
.tk-thread{margin-top:14px;padding-top:14px;border-top:1px dashed var(--color-border)}
.tk-thread[hidden]{display:none}
.tk-msgs{display:flex;flex-direction:column;gap:10px}
.tk-msg{max-width:85%;border:1px solid var(--color-border);border-radius:14px;padding:11px 14px;background:var(--color-bg)}
.tk-msg.mine{align-self:flex-start;border-bottom-right-radius:2px}
.tk-msg.other{align-self:flex-end;border-bottom-left-radius:2px}
.tk-msg.role-user{background:var(--success-12);border-color:rgba(22,163,122,.25)}
.tk-msg.role-branch_admin{background:var(--violet-12);border-color:rgba(124,77,219,.25)}
.tk-msg.role-super{background:var(--secondary-12);border-color:rgba(244,166,30,.35)}
.tk-msg .mh{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:5px}
.tk-msg .mwho{font-size:12px;font-weight:800}
.tk-msg .mrole{font-size:10.5px;font-weight:700;color:var(--color-muted)}
.tk-msg .mdate{font-size:10.5px;color:var(--color-muted);font-weight:600}
.tk-msg p{font-size:13px;line-height:1.9;word-break:break-word;white-space:pre-wrap}
.tk-actions{display:flex;align-items:center;gap:9px;margin-top:14px;flex-wrap:wrap;justify-content:flex-end}
.tk-reply-form{margin-top:14px}
.tk-reply-form textarea{width:100%;font-family:inherit;font-size:13.5px;color:var(--color-text);background:var(--color-bg);
  border:1.5px solid var(--color-border);border-radius:var(--radius-sm);padding:11px 13px;line-height:1.9;resize:vertical;min-height:80px;
  transition:border-color .2s,box-shadow .2s,background .2s}
.tk-reply-form textarea:focus{outline:none;border-color:var(--color-primary-light);box-shadow:0 0 0 4px var(--primary-08);background:var(--color-surface)}
.tk-reply-actions{display:flex;gap:9px;margin-top:10px;justify-content:flex-start;flex-wrap:wrap}
.btn-danger{background:var(--danger-12);color:var(--danger);padding:7px 13px;font-size:12px;border-radius:10px}
.btn-warn{background:var(--secondary-12);color:var(--color-secondary-dark);padding:7px 13px;font-size:12px;border-radius:10px}
.btn-send{background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));color:#fff;padding:9px 16px;font-size:12.5px}
.tk-locked{font-size:12px;color:var(--color-muted);font-weight:700;margin-top:12px;text-align:center;padding:10px;background:var(--color-bg);border-radius:12px}

.tk-empty{text-align:center;padding:50px 20px;color:var(--color-muted);font-weight:600;background:var(--color-surface);
  border:1.5px dashed var(--color-border);border-radius:var(--radius)}

.tk-setup{background:var(--color-surface);border:1.5px dashed var(--color-secondary);border-radius:var(--radius);padding:26px;line-height:2}
.tk-setup h2{font-size:16px;font-weight:800;margin-bottom:8px}
.tk-setup code{background:var(--color-bg);border:1px solid var(--color-border);border-radius:8px;padding:3px 8px;font-size:12.5px;direction:ltr;display:inline-block}

.tk-notification-dot{
  display:none;
  position:absolute;
  top:-4px;
  right:-4px;
  width:9px;
  height:9px;
  border-radius:50%;
  background-color:var(--danger);
  box-shadow:0 0 0 2px #ffffff, 0 0 6px rgba(224, 85, 107, 0.6);
  animation:tk-pulse-glow 2s infinite ease-in-out;
  z-index:10;
}
@keyframes tk-pulse-glow{
  0%{
    transform:scale(1);
    box-shadow:0 0 0 2px #ffffff, 0 0 4px rgba(224, 85, 107, 0.5);
  }
  50%{
    transform:scale(1.1);
    box-shadow:0 0 0 2px #ffffff, 0 0 10px rgba(224, 85, 107, 0.8), 0 0 0 4px rgba(224, 85, 107, 0.25);
  }
  100%{
    transform:scale(1);
    box-shadow:0 0 0 2px #ffffff, 0 0 4px rgba(224, 85, 107, 0.5);
  }
}

@media (max-width:680px){
  body{padding:18px 14px}
  .tk-tabs{grid-template-columns:repeat(2,1fr)}
  .tk-grid2{grid-template-columns:1fr}
  .tk-head h1{font-size:19px}
}
</style>
</head>
<body>

<main class="tk-wrap">
  <header class="tk-head">
    <div class="tk-head-ic">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>
    </div>
    <div>
      <h1><?= e($H_TITLE) ?></h1>
      <p><?= e($H_SUB) ?></p>
    </div>
    <span class="tk-spacer"></span>
  </header>

  <?php if ($FLASH): ?>
    <div class="tk-flash <?= $FLASH['type']==='ok' ? 'ok' : 'err' ?>">
      <?php if ($FLASH['type']==='ok'): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php endif; ?>
      <span><?= e($FLASH['text']) ?></span>
    </div>
  <?php endif; ?>

  <?php if ($SETUP_NEEDED): ?>
    <div class="tk-setup">
      <h2>سیستم تیکتینگ هنوز راه‌اندازی نشده است</h2>
      <p>برای فعال‌سازی، مهاجرتِ دیتابیس را اجرا کنید:</p>
      <p><code>mysql -u USER -p DBNAME &lt; database/migrations/002_ticketing.sql</code></p>
      <p>پس از اجرا، این صفحه به‌صورتِ خودکار آماده‌ی استفاده خواهد بود.</p>
    </div>
  <?php else: ?>

    <?php if ($CAN_CREATE): ?>
    <div class="tk-new">
      <button type="button" class="btn btn-primary" id="newBtn" onclick="tkNewToggle()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        ثبتِ تیکتِ جدید
      </button>
      <div class="tk-new-panel" id="newPanel" hidden>
        <form method="post" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="action" value="create">
          <div class="tk-grid2">
            <div class="tk-field">
              <label>موضوع</label>
              <input type="text" name="subject" maxlength="200" placeholder="موضوعِ تیکت را کوتاه بنویسید" required>
            </div>
            <div class="tk-field">
              <label>اولویت</label>
              <select name="priority">
                <option value="normal">عادی</option>
                <option value="low">کم</option>
                <option value="high">فوری</option>
              </select>
            </div>
          </div>
          <div class="tk-field">
            <label>متنِ تیکت</label>
            <textarea name="body" placeholder="شرحِ درخواست یا مشکلِ خود را بنویسید..." required></textarea>
          </div>
          <div class="tk-form-foot">
            <span class="to">گیرنده: <b><?= e($CREATE_TO) ?></b></span>
            <div style="display:flex;gap:9px">
              <button type="button" class="btn btn-ghost" onclick="tkNewToggle()">انصراف</button>
              <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                ارسال تیکت
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($MY_ROLE === 'branch_admin'): ?>
    <div class="tk-boxes">
      <button type="button" class="tk-box-btn active" data-box="inbox" onclick="tkBox('inbox')" style="position: relative;">
        تیکت‌های دریافتی <b>(<span data-boxcount="inbox">۰</span>)</b>
        <span class="tk-notification-dot tk-dot-inbox"></span>
      </button>
      <button type="button" class="tk-box-btn" data-box="sent" onclick="tkBox('sent')" style="position: relative;">
        ارسالی به ستاد <b>(<span data-boxcount="sent">۰</span>)</b>
        <span class="tk-notification-dot tk-dot-sent"></span>
      </button>
      <button type="button" class="tk-box-btn" data-box="escalated" onclick="tkBox('escalated')">
        ارجاعی به ستاد <b>(<span data-boxcount="escalated">۰</span>)</b>
      </button>
    </div>
    <?php endif; ?>

    <section class="tk-tabs">
      <button type="button" class="tk-tab active" data-cat="all" onclick="tkTab('all')">
        <div class="tk-tab-ic ic-all"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></div>
        <div><b data-tabcount="all">۰</b><span>همه</span></div>
      </button>
      <button type="button" class="tk-tab" data-cat="open" onclick="tkTab('open')" style="position: relative;">
        <div class="tk-tab-ic ic-open"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg></div>
        <div><b data-tabcount="open">۰</b><span>در انتظار پاسخ</span></div>
        <span class="tk-notification-dot tk-dot-tab-open"></span>
      </button>
      <button type="button" class="tk-tab" data-cat="answered" onclick="tkTab('answered')" style="position: relative;">
        <div class="tk-tab-ic ic-answered"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg></div>
        <div><b data-tabcount="answered">۰</b><span>پاسخ‌داده‌شده</span></div>
        <span class="tk-notification-dot tk-dot-tab-answered"></span>
      </button>
      <button type="button" class="tk-tab" data-cat="closed" onclick="tkTab('closed')">
        <div class="tk-tab-ic ic-closed"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div><b data-tabcount="closed">۰</b><span>بسته‌شده</span></div>
      </button>
    </section>

    <div class="tk-list" id="tkList">
      <?php foreach ($VIEW as $V):
        $t = $V['t']; $tid = (int)$t['id']; $msgs = $V['msgs'];
        $status = $t['status']; $prio = $t['priority']; $box = $V['box'];
        $isResponderView = ($t['target']==='branch' && $MY_ROLE==='branch_admin') || ($t['target']==='hq' && $MY_ROLE==='super');
        $showCreator = $MY_ROLE !== 'user';
        $showBranch  = $MY_ROLE === 'super';
        $canEscalate = $MY_ROLE==='branch_admin' && $box==='inbox' && (int)$t['child_count']===0;
        $isEscalated = (int)$t['child_count'] > 0;
        $isEscalIn   = $t['escalated_from'] !== null;
        $readOnly    = ($t['target'] === 'hq' && $MY_ROLE === 'branch_admin' && $t['escalated_from'] !== null);

        $canClose = ($status !== 'closed') && ((int)$t['created_by'] !== $ME);
        $canReopen = false;
        if ($status === 'closed') {
          if ($MY_ROLE === 'super') {
            $canReopen = ($t['target'] === 'hq');
          } elseif ($MY_ROLE === 'branch_admin') {
            $canReopen = ($t['target'] === 'branch' && (int)$t['child_count'] === 0);
          }
        }
      ?>
      <article class="tk-card <?= $V['unread']?'unread':'' ?> <?= $status==='closed'?'closed':'' ?>"
               data-id="<?= $tid ?>" data-box="<?= e($box) ?>" data-status="<?= e($status) ?>">
        <div class="tk-av" style="background:linear-gradient(135deg,<?= tk_color($t['created_by']) ?>,<?= tk_color($t['created_by']) ?>cc)"><?= e(tk_initials($V['creator'])) ?></div>
        <div class="tk-body">
          <div class="tk-top">
            <div>
              <span class="tk-subject"><?= e($t['subject']) ?></span>
              <span class="tk-num"> — شماره <?= tk_fa($tid) ?></span>
            </div>
            <span class="tk-date" data-ts="<?= e((string)($t['last_reply_at'] ?: $t['created_at'])) ?>"></span>
          </div>

          <div class="tk-tags">
            <span class="tk-badge prio-<?= e($prio) ?>"><span class="dot"></span><?= e($PRIO_LABEL[$prio] ?? $prio) ?></span>
            <span class="tk-badge st-<?= e($status) ?>" data-role="status-badge"><span class="dot"></span><?= e($STAT_LABEL[$status] ?? $status) ?></span>
            <?php if ($showCreator): ?>
              <span class="tk-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V21"/><circle cx="9.5" cy="7" r="3.5"/></svg>از: <?= e($V['creator']) ?> (<?= e($ROLE_LABEL[$t['creator_role']] ?? '') ?>)</span>
            <?php endif; ?>
            <?php if ($showBranch): ?>
              <span class="tk-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="2.4"/><circle cx="18" cy="6" r="2.4"/><circle cx="12" cy="18" r="2.4"/><path d="M7.6 7.8 11 15.6"/><path d="M16.4 7.8 13 15.6"/></svg>شعبه: <?= e($t['branch_name']) ?></span>
            <?php endif; ?>
            <?php if ($isEscalIn): ?>
              <span class="tk-chip escin">ارجاع‌شده از شعبه</span>
            <?php endif; ?>
            <?php if ($isEscalated): ?>
              <span class="tk-chip esc">به ستاد ارجاع شد</span>
            <?php endif; ?>
            <?php if ($V['unread']): ?>
              <span class="tk-chip new" data-role="new-chip">جدید</span>
            <?php endif; ?>
          </div>

          <?php $lastMsg = $msgs ? end($msgs) : null; reset($msgs); ?>
          <p class="tk-preview"><?= e($lastMsg ? (string)$lastMsg['body'] : '') ?></p>

          <div class="tk-foot">
            <button type="button" class="btn btn-ghost btn-sm" data-role="toggle" onclick="tkToggle(this)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/></svg>
              مشاهدهٔ گفتگو
            </button>
            <span class="tk-count"><?= tk_fa(count($msgs)) ?> پیام</span>
          </div>

          <div class="tk-thread" hidden data-role="thread">
            <div class="tk-msgs">
              <?php foreach ($msgs as $m):
                $mine = (int)$m['user_id'] === $ME;
                $clsSide = $mine ? 'mine' : 'other';
                $clsRole = 'role-' . $m['author_role'];
                $cls = $clsSide . ' ' . $clsRole;
                $mWho = trim((string)($m['full_name'] ?: $m['username']));
              ?>
              <div class="tk-msg <?= $cls ?>">
                <div class="mh">
                  <span class="mwho"><?= e($mWho) ?> <span class="mrole">(<?= e($ROLE_LABEL[$m['author_role']] ?? '') ?>)</span></span>
                  <span class="mdate" data-ts="<?= e((string)$m['created_at']) ?>"></span>
                </div>
                <p><?= e((string)$m['body']) ?></p>
              </div>
              <?php endforeach; ?>
            </div>

            <?php if ($readOnly): ?>
              <div class="tk-actions">
                <div class="tk-locked" style="flex:1">این تیکت به ستاد مرکزی ارجاع شده است و فقط توسط ستاد مرکزی مدیریت می‌شود.</div>
              </div>
            <?php elseif ($status !== 'closed'): ?>
              <form method="post" class="tk-reply-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="ticket_id" value="<?= $tid ?>">
                <textarea name="body" placeholder="<?= $isResponderView ? 'پاسخِ خود را بنویسید...' : 'پیامِ خود را بنویسید...' ?>" required></textarea>
                <div class="tk-reply-actions">
                  <button type="submit" class="btn btn-send">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <?= $isResponderView ? 'ارسال پاسخ' : 'ارسال پیام' ?>
                  </button>
                </div>
              </form>
              <div class="tk-actions">
                <?php if ($canEscalate): ?>
                  <form method="post" onsubmit="return confirm('این تیکت با همان محتوا به‌صورتِ خودکار به ستاد مرکزی ارجاع می‌شود. ادامه می‌دهید؟');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="escalate">
                    <input type="hidden" name="ticket_id" value="<?= $tid ?>">
                    <button type="submit" class="btn btn-warn">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                      ارجاع به ستاد مرکزی
                    </button>
                  </form>
                <?php endif; ?>
                <?php if ($canClose): ?>
                  <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="status" value="closed">
                    <input type="hidden" name="ticket_id" value="<?= $tid ?>">
                    <button type="submit" class="btn btn-danger">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                      بستنِ تیکت
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="tk-actions">
                <div class="tk-locked" style="flex:1">این تیکت بسته شده است.</div>
                <?php if ($canReopen): ?>
                  <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="status">
                    <input type="hidden" name="ticket_id" value="<?= $tid ?>">
                    <input type="hidden" name="status" value="open">
                    <button type="submit" class="btn btn-ghost btn-sm">بازگشایی</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>

      <div id="tkEmpty" class="tk-empty" style="<?= count($VIEW) ? 'display:none' : '' ?>">تیکتی برای نمایش وجود ندارد.</div>
    </div>

  <?php endif; ?>
</main>

<script>
(function(){
  "use strict";
  var CSRF = <?= json_encode(csrf_token()) ?>;
  var ROLE = <?= json_encode($MY_ROLE) ?>;
  var list = document.getElementById('tkList');

  function faNum(n){ return String(n).replace(/\d/g,function(d){ return '۰۱۲۳۴۵۶۷۸۹'[d]; }); }

  /* ---- تاریخِ شمسی برای همه‌ی [data-ts] ---- */
  function fmtDate(ts){
    if(!ts) return '';
    var d = new Date(String(ts).replace(' ','T'));
    if(isNaN(d.getTime())) return ts;
    try{
      var date = new Intl.DateTimeFormat('fa-IR-u-ca-persian',{year:'numeric',month:'long',day:'numeric'}).format(d);
      var time = new Intl.DateTimeFormat('fa-IR',{hour:'2-digit',minute:'2-digit'}).format(d);
      return date+' – '+time;
    }catch(e){ return ts; }
  }
  document.querySelectorAll('[data-ts]').forEach(function(el){ el.textContent = fmtDate(el.getAttribute('data-ts')); });

  if(!list) return;

  /* ---- وضعیتِ فیلترها ---- */
  var activeBox = (ROLE==='branch_admin') ? 'inbox' : null;
  var activeCat = 'all';

  function cardVisibleByBox(c){ return activeBox===null || c.getAttribute('data-box')===activeBox; }
  function applyFilters(){
    var any=false;
    list.querySelectorAll('.tk-card').forEach(function(c){
      var show = cardVisibleByBox(c) && (activeBox === 'escalated' || activeCat==='all' || c.getAttribute('data-status')===activeCat);
      c.style.display = show ? '' : 'none';
      if(show) any=true;
    });
    var empty=document.getElementById('tkEmpty'); if(empty) empty.style.display = any ? 'none' : '';
    updateCounts();
  }
  function updateCounts(){
    var c={all:0,open:0,answered:0,closed:0};
    var hasInboxOpen = false;
    var hasSentAnswered = false;

    list.querySelectorAll('.tk-card').forEach(function(card){
      var cardBox = card.getAttribute('data-box');
      var cardStatus = card.getAttribute('data-status');

      if (cardBox === 'inbox' && cardStatus === 'open') {
        hasInboxOpen = true;
      }
      if (cardBox === 'sent' && cardStatus === 'answered') {
        hasSentAnswered = true;
      }

      if(!cardVisibleByBox(card)) return;          // شمارشِ تب‌ها فقط برای جعبه‌ی فعال
      c.all++;
      if(c[cardStatus]!==undefined) c[cardStatus]++;
    });

    // به‌روزرسانی نمایش نقطه‌های قرمز نوتیفیکیشن
    var dotInbox = document.querySelector('.tk-dot-inbox');
    if (dotInbox) dotInbox.style.display = hasInboxOpen ? 'block' : 'none';

    var dotSent = document.querySelector('.tk-dot-sent');
    if (dotSent) dotSent.style.display = hasSentAnswered ? 'block' : 'none';

    // نمایش/مخفی کردن نقطه‌های نوتیفیکیشن در سطح تب‌ها بر اساس نقش و جعبه‌ی فعال
    var showTabOpen = false;
    var showTabAnswered = false;

    if (ROLE === 'super') {
      if (c.open > 0) showTabOpen = true;
    } else if (ROLE === 'branch_admin') {
      if (activeBox === 'inbox' && c.open > 0) showTabOpen = true;
      if (activeBox === 'sent' && c.answered > 0) showTabAnswered = true;
    } else if (ROLE === 'user') {
      if (c.answered > 0) showTabAnswered = true;
    }

    var dotTabOpen = document.querySelector('.tk-dot-tab-open');
    if (dotTabOpen) dotTabOpen.style.display = showTabOpen ? 'block' : 'none';

    var dotTabAnswered = document.querySelector('.tk-dot-tab-answered');
    if (dotTabAnswered) dotTabAnswered.style.display = showTabAnswered ? 'block' : 'none';

    Object.keys(c).forEach(function(k){
      document.querySelectorAll('[data-tabcount="'+k+'"]').forEach(function(b){ b.textContent=faNum(c[k]); });
    });
    // شمارشِ جعبه‌ها (مدیر شعبه)
    var box={inbox:0,sent:0,escalated:0};
    list.querySelectorAll('.tk-card').forEach(function(card){ var b=card.getAttribute('data-box'); if(box[b]!==undefined) box[b]++; });
    Object.keys(box).forEach(function(k){
      document.querySelectorAll('[data-boxcount="'+k+'"]').forEach(function(s){ s.textContent=faNum(box[k]); });
    });
  }

  window.tkTab = function(cat){
    activeCat=cat;
    document.querySelectorAll('.tk-tab').forEach(function(t){ t.classList.toggle('active', t.getAttribute('data-cat')===cat); });
    applyFilters();
  };
  window.tkBox = function(box){
    activeBox=box;
    document.querySelectorAll('.tk-box-btn').forEach(function(b){ b.classList.toggle('active', b.getAttribute('data-box')===box); });
    
    // مخفی کردن یا نمایش تب‌ها بر اساس اینکه جعبه ارجاعی فعال است یا خیر
    var tabs = document.querySelector('.tk-tabs');
    if (tabs) {
      if (box === 'escalated') {
        tabs.style.display = 'none';
      } else {
        tabs.style.display = '';
      }
    }
    
    applyFilters();
  };

  /* ---- باز/بستهٔ کادرِ تیکتِ جدید ---- */
  window.tkNewToggle = function(){
    var p=document.getElementById('newPanel'); if(!p) return;
    if(p.hasAttribute('hidden')){ p.removeAttribute('hidden'); var i=p.querySelector('input[name=subject]'); if(i) i.focus(); }
    else p.setAttribute('hidden','');
  };

  /* ---- باز/بستهٔ نخِ گفتگو + علامتِ خوانده‌شد ---- */
  window.tkToggle = function(btn){
    var card=btn.closest('.tk-card');
    var thread=card.querySelector('[data-role="thread"]');
    if(!thread) return;
    if(thread.hasAttribute('hidden')){
      thread.removeAttribute('hidden');
      // علامتِ خوانده‌شد (در صورتِ وجودِ پیامِ جدید)
      if(card.classList.contains('unread')){
        markRead(card.getAttribute('data-id'));
        card.classList.remove('unread');
        var chip=card.querySelector('[data-role="new-chip"]'); if(chip) chip.remove();
      }
    } else {
      thread.setAttribute('hidden','');
    }
  };

  function markRead(id){
    try{
      var body=new URLSearchParams(); body.set('action','read'); body.set('ticket_id',id); body.set('csrf_token',CSRF);
      fetch('tickets.php',{method:'POST',headers:{'X-CSRF-TOKEN':CSRF},body:body,credentials:'same-origin'});
    }catch(e){}
  }

  /* ---- تمِ روشن/تاریک هم‌گام با داشبورد ---- */
  function applyTheme(){
    var d=false; try{ d=localStorage.getItem('maxa-theme')==='dark'; }catch(e){}
    if(d) document.documentElement.setAttribute('data-theme','dark'); else document.documentElement.removeAttribute('data-theme');
  }
  applyTheme();
  window.addEventListener('storage',function(e){ if(!e||e.key==='maxa-theme'||e.key===null) applyTheme(); });

  applyFilters();
})();
</script>
</body>
</html>
