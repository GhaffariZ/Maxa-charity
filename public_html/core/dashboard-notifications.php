<?php
declare(strict_types=1);

/** SQL sources contain identifiers and dates only; patient/message contents never enter the feed. */
function dash_notification_sources(array $context): array
{
    $branch = (int)$context['branch'];
    $user = (int)$context['user'];
    $sources = [];
    if (!empty($context['tickets'])) {
        if ($context['super']) {
            $scope = "t.target = 'hq'";
            $params = [];
        } elseif ($context['branch_admin']) {
            $scope = "((t.branch_id = ? AND t.target = 'branch') OR (t.branch_id = ? AND t.target = 'hq' AND (t.status <> 'closed' OR t.escalated_from IS NULL)))";
            $params = [$branch, $branch];
        } else {
            $scope = 't.created_by = ?';
            $params = [$user];
        }
        $sources['tickets'] = [
            'sql' => "SELECT CONCAT('ticket:', m.id) AS event_key, 'tickets' AS kind, t.id AS record_id,
                       m.created_at, (t.status <> 'closed') AS pending,
                       (tr.last_read_at IS NOT NULL AND tr.last_read_at >= m.created_at) AS source_read
                      FROM tickets t
                      JOIN ticket_messages m ON m.id = (SELECT MAX(tm.id) FROM ticket_messages tm WHERE tm.ticket_id = t.id)
                      LEFT JOIN ticket_reads tr ON tr.ticket_id = t.id AND tr.user_id = ?
                     WHERE ($scope) AND t.branch_id = ? AND m.user_id <> ?
                       AND (m.created_at >= NOW() - INTERVAL 30 DAY OR t.status <> 'closed')",
            'params' => array_merge([$user], $params, [$branch, $user]),
        ];
    }
    if (!empty($context['news'])) {
        $sources['news'] = [
            // shortcut: reuse audit metadata; add an indexed review timestamp if editorial log lookups become slow.
            'sql' => "SELECT CONCAT('news:', n.id, ':', COALESCE(a.id, 0)) AS event_key, 'news' AS kind,
                       n.id AS record_id, a.created_at, (n.status = 'review') AS pending, 0 AS source_read
                      FROM news n
                      LEFT JOIN audit_log a ON a.id = (
                        SELECT MAX(al.id) FROM audit_log al WHERE al.action = 'news_status_changed'
                          AND JSON_UNQUOTE(JSON_EXTRACT(al.metadata, '$.news_id')) = n.id
                          AND JSON_UNQUOTE(JSON_EXTRACT(al.metadata, '$.status')) = 'review')
                     WHERE n.branch_id = ? AND (n.status = 'review' OR a.created_at >= NOW() - INTERVAL 30 DAY)",
            'params' => [$branch],
        ];
    }
    if (!empty($context['medical'])) {
        $sources['medical'] = [
            'sql' => "SELECT CONCAT('medical:', mr.id) AS event_key, 'medical' AS kind, mr.id AS record_id,
                       mr.created_at, (mr.status IN ('new','reviewing')) AS pending, 0 AS source_read
                      FROM medical_records mr WHERE mr.branch_id = ?
                       AND (mr.created_at >= NOW() - INTERVAL 30 DAY OR mr.status IN ('new','reviewing'))",
            'params' => [$branch],
        ];
    }
    return $sources;
}

function dash_notification_context(): array
{
    $finance = dash_is_finance_only() || dash_is_finance_user();
    return [
        'user' => (int)dash_user()['id'], 'branch' => dash_active_branch_id(),
        'super' => dash_is_super(), 'branch_admin' => dash_is_branch_admin(),
        'tickets' => !$finance && dash_can('tickets'),
        'news' => !$finance && dash_can('news') && dash_is_news_editor(),
        'medical' => !$finance && dash_can('medical'),
    ];
}

/** A missing optional module must not suppress notifications from healthy modules. */
function dash_notification_query(PDO $pdo, array $context): array
{
    $parts = []; $params = []; $unavailable = [];
    foreach (dash_notification_sources($context) as $kind => $source) {
        try {
            $check = $pdo->prepare('SELECT * FROM (' . $source['sql'] . ') source_check LIMIT 0');
            $check->execute($source['params']);
            $parts[] = $source['sql'];
            array_push($params, ...$source['params']);
        } catch (PDOException $e) {
            error_log('Dashboard notification source ' . $kind . ': ' . $e->getMessage());
            $unavailable[] = $kind;
        }
    }
    return [
        'sql' => $parts ? implode(' UNION ALL ', $parts) : "SELECT '' AS event_key, '' AS kind, 0 AS record_id, NULL AS created_at, 0 AS pending, 0 AS source_read WHERE 1=0",
        'params' => $params, 'unavailable' => $unavailable,
    ];
}

function dash_notification_mark(PDO $pdo, array $query, array $context, ?string $key): void
{
    if ($key !== null && (strlen($key) > 100 || !preg_match('/\A(?:ticket:\d+|medical:\d+|news:\d+:\d+)\z/', $key))) {
        throw new InvalidArgumentException('شناسهٔ اعلان نامعتبر است.');
    }
    $sql = 'INSERT INTO dashboard_notification_reads (user_id, branch_id, event_key, read_at)
            SELECT ?, ?, e.event_key, NOW() FROM (' . $query['sql'] . ') e';
    $params = array_merge([$context['user'], $context['branch']], $query['params']);
    if ($key !== null) {
        // The INSERT SELECT also rechecks role/branch ownership for forged notification keys.
        $sql .= ' WHERE e.event_key = ?';
        $params[] = $key;
    }
    if ($key === null) $sql .= ' WHERE 1=1';
    $sql .= ' ON DUPLICATE KEY UPDATE read_at = VALUES(read_at)';
    $pdo->prepare($sql)->execute($params);
}

function dash_notification_feed(PDO $pdo, array $query, array $context, string $filter, int $page): array
{
    $from = ' FROM (' . $query['sql'] . ') e LEFT JOIN dashboard_notification_reads r
               ON r.event_key = e.event_key AND r.user_id = ? AND r.branch_id = ?';
    $params = array_merge($query['params'], [$context['user'], $context['branch']]);
    $unread = '(r.event_key IS NULL AND e.source_read = 0)';
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, COALESCE(SUM($unread),0) AS unread" . $from);
    $stmt->execute($params);
    $counts = $stmt->fetch();
    $where = $filter === 'unread' ? " WHERE $unread" : '';
    $total = (int)$counts[$filter === 'unread' ? 'unread' : 'total'];
    $page = min($page, max(1, (int)ceil($total / 20)));
    $stmt = $pdo->prepare("SELECT e.*, $unread AS unread" . $from . $where .
        ' ORDER BY e.created_at DESC, e.event_key DESC LIMIT 20 OFFSET ' . (($page - 1) * 20));
    $stmt->execute($params);
    $labels = ['tickets' => 'پیام تازه در تیکت', 'news' => 'خبر ارسال‌شده برای بررسی', 'medical' => 'درخواست پذیرش بیمار'];
    $links = ['tickets' => 'tickets.php?id=', 'news' => 'news-list.php?id=', 'medical' => 'medical-records.php?id='];
    $items = [];
    foreach ($stmt->fetchAll() as $row) {
        $items[] = [
            'key' => $row['event_key'], 'kind' => $row['kind'],
            'title' => $labels[$row['kind']] . ' #' . $row['record_id'],
            'href' => $links[$row['kind']] . (int)$row['record_id'],
            'created_at' => $row['created_at'] ? date(DATE_ATOM, strtotime($row['created_at'])) : null,
            'unread' => (bool)$row['unread'],
        ];
    }
    $stmt = $pdo->prepare('SELECT kind, COUNT(*) AS count FROM (' . $query['sql'] . ') e WHERE pending = 1 GROUP BY kind');
    $stmt->execute($query['params']);
    $attention = [];
    foreach ($stmt->fetchAll() as $row) $attention[$row['kind']] = (int)$row['count'];
    return [
        'items' => $items, 'unread' => (int)$counts['unread'], 'total' => $total, 'page' => $page,
        'has_more' => $page * 20 < $total, 'attention' => $attention,
        'available' => array_keys(dash_notification_sources($context)),
        'unavailable' => $query['unavailable'], 'updated_at' => date(DATE_ATOM),
    ];
}
