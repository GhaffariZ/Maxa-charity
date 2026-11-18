<?php
declare(strict_types=1);


/** The receipt, rather than the order's creation or editable catalog price, is the revenue source. */
function stand_finance_rows(PDO $db, ?int $branchId, ?string $start = null, ?string $end = null, string $status = 'success'): array
{
    $where = ["sp.source = 'stands'"];
    $params = [];
    if ($branchId !== null) { $where[] = 'sp.branch_id = ?'; $params[] = $branchId; }
    if ($status !== 'all') { $where[] = 'sp.status = ?'; $params[] = $status; }
    if ($start) { $where[] = 'COALESCE(sp.paid_at, sp.created_at) >= ?'; $params[] = $start . ' 00:00:00'; }
    if ($end) { $where[] = 'COALESCE(sp.paid_at, sp.created_at) <= ?'; $params[] = $end . ' 23:59:59'; }
    $st = $db->prepare("SELECT sp.*, o.tracking_code, o.from_user, o.to_user, s.title AS stand_title,
        b.name AS branch_name, COALESCE(sp.paid_at, sp.created_at) AS tx_date
        FROM stand_payments sp JOIN orders o ON o.id = sp.order_id
        LEFT JOIN stands s ON s.id = o.stand_id LEFT JOIN branches b ON b.id = sp.branch_id
        WHERE " . implode(' AND ', $where) . ' ORDER BY tx_date DESC, sp.id DESC');
    $st->execute($params);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

