<?php
require_once __DIR__ . '/db.php';

function searchTransport(string $type, string $from, string $to, string $date): array
{
    if (TICKET_API_MOCK || empty(TICKET_API_KEY)) {
        return searchMockTransport($type, $from, $to, $date);
    }

    $url = TICKET_API_URL . '/transport/search?' . http_build_query([
        'type' => $type,
        'from' => $from,
        'to' => $to,
        'date' => $date,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . TICKET_API_KEY],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            return $data;
        }
    }

    return searchMockTransport($type, $from, $to, $date);
}

function searchMockTransport(string $type, string $from, string $to, string $date): array
{
    $stmt = db()->prepare(
        'SELECT * FROM transport_inventory WHERE type = ? AND (from_city LIKE ? OR from_city = ?) AND (to_city LIKE ? OR to_city = ?) ORDER BY price ASC'
    );
    $likeFrom = '%' . $from . '%';
    $likeTo = '%' . $to . '%';
    $stmt->execute([$type, $likeFrom, $from, $likeTo, $to]);
    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        $stmt = db()->prepare('SELECT * FROM transport_inventory WHERE type = ? ORDER BY price ASC LIMIT 10');
        $stmt->execute([$type]);
        $rows = $stmt->fetchAll();
    }

    return array_map(function ($r) use ($date) {
        return [
            'id' => $r['id'],
            'operator' => $r['operator'],
            'from_city' => $r['from_city'],
            'to_city' => $r['to_city'],
            'departure_time' => $r['departure_time'],
            'arrival_time' => $r['arrival_time'],
            'price' => (float) $r['price'],
            'rating' => (float) $r['rating'],
            'bus_type' => $r['vehicle_class'],
            'date' => $date,
        ];
    }, $rows);
}

function bookTransport(int $userId, int $inventoryId, string $date, int $passengers = 1): array
{
    $stmt = db()->prepare('SELECT * FROM transport_inventory WHERE id = ?');
    $stmt->execute([$inventoryId]);
    $inv = $stmt->fetch();
    if (!$inv) {
        return ['success' => false, 'message' => 'Transport not found'];
    }

    $pnr = 'PNR' . strtoupper(substr(md5(uniqid()), 0, 8));
    $total = $inv['price'] * $passengers;

    $stmt = db()->prepare(
        'INSERT INTO transport_bookings (user_id, trip_plan_id, type, operator, from_city, to_city, departure_time, arrival_time, travel_date, price, pnr, passengers, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,"confirmed")'
    );
    $stmt->execute([
        $userId, null, $inv['type'], $inv['operator'], $inv['from_city'], $inv['to_city'],
        $inv['departure_time'], $inv['arrival_time'], $date, $total, $pnr, $passengers,
    ]);

    return ['success' => true, 'pnr' => $pnr, 'booking_id' => (int) db()->lastInsertId(), 'total' => $total];
}
