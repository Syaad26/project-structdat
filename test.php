<?php
require_once __DIR__ . '/index.php';

initInventarisSession();
$feedback = prosesAksiInventaris();
$state = getInventarisState();

$inventaris = &$state['inventaris'];
$jumlah = &$state['jumlah'];

$notif = $feedback['notif'];
$error = $feedback['error'];
$hasilCari = $feedback['hasilCari'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Nusantara</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="bg-shape shape-1" aria-hidden="true"></div>
    <div class="bg-shape shape-2" aria-hidden="true"></div>

    <main class="container">
        <header class="hero">
            <p class="kicker">Sistem Inventaris Digital</p>
            <h1>Perpustakaan Nusantara</h1>
            <p class="subtitle">Kelola data buku seperti aplikasi perpustakaan: tambah, cari, update stok, dan hapus data dari inventaris.</p>
            <div class="stats">
                <article>
                    <h2><?= $jumlah ?></h2>
                    <p>Total Buku</p>
                </article>
                <article>
                    <h2><?= count($inventaris) ?></h2>
                    <p>Node Aktif</p>
                </article>
                <article>
                    <h2><?= h(getAddr(0)) ?></h2>
                    <p>Base Address</p>
                </article>
            </div>
        </header>

        <?php if ($notif !== null): ?>
            <section class="alert success"><?= h($notif) ?></section>
        <?php endif; ?>

        <?php if ($error !== null): ?>
            <section class="alert error"><?= h($error) ?></section>
        <?php endif; ?>

        <section class="grid-forms">
            <form class="card" method="post">
                <h3>Tambah Buku</h3>
                <input type="hidden" name="aksi" value="tambah">
                <label>Judul Buku</label>
                <input type="text" name="judul" placeholder="Contoh: Algoritma Dasar" required>
                <label>Pengarang</label>
                <input type="text" name="pengarang" placeholder="Nama pengarang" required>
                <label>Stok</label>
                <input type="number" name="stok" min="0" value="1" required>
                <button type="submit">Tambah ke Inventaris</button>
            </form>

            <form class="card" method="post">
                <h3>Cari Buku (ID)</h3>
                <input type="hidden" name="aksi" value="cari">
                <label>ID Buku</label>
                <input type="number" name="id_cari" min="1" required>
                <button type="submit">Cari Buku</button>

                <?php if ($hasilCari !== null): ?>
                    <div class="result-box">
                        <p><strong>Ditemukan:</strong> <?= h($hasilCari['data']['judul']) ?></p>
                        <p><strong>Pengarang:</strong> <?= h($hasilCari['data']['pengarang']) ?></p>
                        <p><strong>Stok:</strong> <?= h((string)$hasilCari['data']['stok']) ?></p>
                        <p><strong>Pointer:</strong> <?= h($hasilCari['addr']) ?></p>
                    </div>
                <?php endif; ?>
            </form>

            <form class="card" method="post">
                <h3>Update Stok</h3>
                <input type="hidden" name="aksi" value="update">
                <label>ID Buku</label>
                <input type="number" name="id_update" min="1" required>
                <label>Stok Baru</label>
                <input type="number" name="stok_baru" min="0" required>
                <button type="submit">Simpan Perubahan</button>
            </form>

            <form class="card" method="post">
                <h3>Hapus Buku</h3>
                <input type="hidden" name="aksi" value="hapus">
                <label>ID Buku</label>
                <input type="number" name="id_hapus" min="1" required>
                <button class="danger" type="submit">Hapus dari Inventaris</button>
            </form>
        </section>

        <section class="table-card">
            <div class="table-title">
                <h3>Daftar Inventaris Buku</h3>
                <p>Setiap buku memiliki simulasi alamat memori sebagai representasi pointer.</p>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pointer</th>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inventaris)): ?>
                            <tr>
                                <td colspan="5" class="empty">Inventaris kosong.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventaris as $i => $buku): ?>
                                <tr>
                                    <td><code><?= h(getAddr($i)) ?></code></td>
                                    <td>#<?= h((string)$buku['id']) ?></td>
                                    <td><?= h($buku['judul']) ?></td>
                                    <td><?= h($buku['pengarang']) ?></td>
                                    <td><?= h((string)$buku['stok']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>