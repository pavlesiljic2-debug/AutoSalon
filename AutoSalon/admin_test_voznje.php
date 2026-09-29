<?php
session_start();
require_once 'db.php';

// Moderatori (2) i Admini (3) imaju pristup
if (!isset($_SESSION['user_id']) || ($_SESSION['uloga'] != 2 && $_SESSION['uloga'] != 3)) {
    header('Location: index.php');
    exit();
}

$error = '';

// Promena statusa (moderator/admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = (int)($_POST['id'] ?? 0);
    $novi_status = trim($_POST['status'] ?? '');
    if ($id > 0 && $novi_status !== '') {
        try {
            $stmt = $pdo->prepare("UPDATE zahtev_za_test_voznju SET status = ? WHERE id = ?");
            $stmt->execute([$novi_status, $id]);
            // Refresh to GET to avoid resubmission, preserve filters
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            header('Location: admin_test_voznje.php' . ($qs ? ('?' . $qs) : ''));
            exit();
        } catch (PDOException $e) {
            $error = 'Greška pri promeni statusa: ' . $e->getMessage();
        }
    }
}

// Filter parametri
$datum = isset($_GET['datum']) ? trim($_GET['datum']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$vreme = isset($_GET['vreme']) ? trim($_GET['vreme']) : '';
$korisnik = isset($_GET['korisnik']) ? trim($_GET['korisnik']) : '';

// SQL where uslovi
$where = [];
$params = [];

if ($datum !== '') {
    $where[] = 'z.datum = ?';
    $params[] = strtotime($datum);
}
if ($status !== '') {
    $where[] = 'z.status = ?';
    $params[] = $status;
}
if ($vreme !== '') {
    $where[] = 'z.vreme_od = ?';
    $params[] = (int)$vreme;
}
if ($korisnik !== '') {
    $where[] = '(n.ime LIKE ? OR n.prezime LIKE ? OR n.email LIKE ?)';
    $params[] = "%$korisnik%";
    $params[] = "%$korisnik%";
    $params[] = "%$korisnik%";
}

$sql = "SELECT z.*, n.ime, n.prezime, n.email, v.marka, v.model, v.godiste, v.cena
        FROM zahtev_za_test_voznju z
        JOIN nalog n ON z.nalog_fk = n.id
        JOIN vozilo v ON z.vozilo_fk = v.id";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY z.datum DESC, z.vreme_od ASC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $zahtevi = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju: ' . $e->getMessage();
    $zahtevi = [];
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test vožnje - pregled</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
<div class="page-container">
<?php include 'navbar.php'; ?>
<div class="page-content">

<div class="container mt-5">
    <h2>Zakazane test vožnje</h2>
    <p class="lead">Pregled svih termina sa filterima.</p>
    <hr>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label class="form-label">Datum</label>
            <input type="date" name="datum" class="form-control" value="<?php echo htmlspecialchars($datum); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">Svi</option>
                <option value="Na čekanju" <?php echo $status==='Na čekanju'?'selected':''; ?>>Na čekanju</option>
                <option value="Odobren" <?php echo $status==='Odobren'?'selected':''; ?>>Odobren</option>
                <option value="Odbijen" <?php echo $status==='Odbijen'?'selected':''; ?>>Odbijen</option>
                <option value="Otkazan" <?php echo $status==='Otkazan'?'selected':''; ?>>Otkazan</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Vreme</label>
            <select name="vreme" class="form-select">
                <option value="">Sva</option>
                <?php for ($i=8; $i<=16; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $vreme!=='' && (int)$vreme===$i?'selected':''; ?>><?php echo $i; ?>:00</option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Korisnik (ime, prezime ili email)</label>
            <input type="text" name="korisnik" class="form-control" value="<?php echo htmlspecialchars($korisnik); ?>" placeholder="npr. Marko ili marko@mail.com">
        </div>
        <div class="col-md-1 d-grid">
            <button class="btn btn-primary">Filtriraj</button>
        </div>
        <div class="col-md-2 d-grid">
            <a href="admin_test_voznje.php" class="btn btn-outline-secondary">Resetuj filtere</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Datum</th>
                    <th>Vreme</th>
                    <th>Korisnik</th>
                    <th>Vozilo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($zahtevi)): ?>
                <tr><td colspan="6" class="text-center text-muted">Nema rezultata za zadate filtere.</td></tr>
                <?php else: foreach ($zahtevi as $z): ?>
                <tr>
                    <td><?php echo $z['id']; ?></td>
                    <td><?php echo date('d.m.Y', (int)$z['datum']); ?></td>
                    <td><?php echo $z['vreme_od']; ?>:00 - <?php echo $z['vrme_do']; ?>:00</td>
                    <td>
                        <?php echo htmlspecialchars($z['ime'] . ' ' . $z['prezime']); ?><br>
                        <small class="text-muted"><?php echo htmlspecialchars($z['email']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($z['marka'] . ' ' . $z['model'] . ' (' . $z['godiste'] . ')'); ?> - <strong><?php echo number_format($z['cena'], 0); ?>€</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php
                            $badge = 'bg-secondary';
                            if ($z['status']==='Na čekanju') $badge='bg-warning';
                            elseif ($z['status']==='Odobren') $badge='bg-success';
                            elseif ($z['status']==='Odbijen') $badge='bg-danger';
                            elseif ($z['status']==='Otkazan') $badge='bg-secondary';
                            echo '<span class="badge ' . $badge . '">' . htmlspecialchars($z['status']) . '</span>';
                            ?>
                            <form method="POST" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="id" value="<?php echo $z['id']; ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="Na čekanju" <?php echo $z['status']==='Na čekanju'?'selected':''; ?>>Na čekanju</option>
                                    <option value="Odobren" <?php echo $z['status']==='Odobren'?'selected':''; ?>>Odobren</option>
                                    <option value="Odbijen" <?php echo $z['status']==='Odbijen'?'selected':''; ?>>Odbijen</option>
                                    <option value="Otkazan" <?php echo $z['status']==='Otkazan'?'selected':''; ?>>Otkazan</option>
                                </select>
                                <button class="btn btn-outline-primary btn-sm">Sačuvaj</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

 </div>
 </div>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


