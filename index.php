<?php
require 'vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

$client = new MongoDB\Client("mongodb://localhost:27017");
$collection = $client->book_inventary->inventaris;
$addrBase = 0x8A00;

function respond(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function getAddr(int $idx, int $base): string
{
    return '0x' . strtoupper(dechex($base + $idx * 0x40));
}

function findIndexById(array $inventaris, int $id): ?int
{
    foreach ($inventaris as $i => $buku) {
        if ((int)$buku['id'] === $id) {
            return $i;
        }
    }
    return null;
}

function buildState(array $inventaris, int $base): array
{
    $totalStok = 0;
    $stokKritis = 0;
    $withAddr = [];

    foreach ($inventaris as $i => $buku) {
        $stok = (int)$buku['stok'];
        $totalStok += $stok;

        if ($stok <= 3) {
            $stokKritis++;
        }

        $withAddr[] = [
            'id' => $buku['_id'], // ambil dari MongoDB _id
            'judul' => $buku['judul'],
            'pengarang' => $buku['pengarang'],
            'stok' => $stok,
            'addr' => getAddr($i, $base)
        ];
    }

    return [
        'books' => $withAddr,
        'stats' => [
            'total_buku' => count($withAddr),
            'total_stok' => $totalStok,
            'stok_kritis' => $stokKritis,
            'base_addr' => getAddr(0, $base),
        ],
    ];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = [];

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $json = json_decode($raw, true);
        if (is_array($json)) {
            $input = $json;
        }
    }

    if (empty($input)) {
        $input = $_POST;
    }
}

$action = $_GET['action'] ?? ($input['action'] ?? 'list');

if ($action === 'list') {
    $books = $collection->find([], ['sort' => ['_id' => 1]])->toArray();
    respond(['ok' => true] + buildState($books, $addrBase));
}

if ($action === 'add') {
    $judul = trim($input['judul']);
    $pengarang = trim($input['pengarang']);
    $stok = (int)$input['stok'];

    $last = $collection->findOne([], ['sort' => ['_id' => -1]]);
    $newId = $last ? $last['_id'] + 1 : 1;

    $collection->insertOne([
        '_id' => $newId,
        'judul' => $judul,
        'pengarang' => $pengarang,
        'stok' => $stok
    ]);

    $books = $collection->find([], ['sort' => ['_id' => 1]])->toArray();
    respond(['ok' => true, 'message' => 'Buku ditambahkan'] + buildState($books, $addrBase));
}

if ($action === 'update') {
    $collection->updateOne(
        ['_id' => (int)$input['id']],
        ['$set' => ['stok' => (int)$input['stok']]]
    );

    $books = $collection->find([], ['sort' => ['_id' => 1]])->toArray();
    respond(['ok' => true, 'message' => 'Stok diperbarui'] + buildState($books, $addrBase));
}

if ($action === 'delete') {
    $collection->deleteOne(['_id' => (int)$input['id']]);

    $books = $collection->find([], ['sort' => ['_id' => 1]])->toArray();
    respond(['ok' => true, 'message' => 'Buku dihapus'] + buildState($books, $addrBase));
}

respond(['ok' => false, 'message' => 'Action tidak dikenali.'], 400);
