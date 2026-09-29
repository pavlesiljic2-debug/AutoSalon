<?php
session_start();
require_once 'db.php';

// Moderator (2) i Admin (3)
if (!isset($_SESSION['user_id']) || ($_SESSION['uloga'] != 2 && $_SESSION['uloga'] != 3)) {
    header('Location: index.php');
    exit();
}

$error = '';

// Akcija: promena dozvole ili brisanje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            if ($_POST['action'] === 'toggle') {
                $new = (int)($_POST['dozvola'] ?? 0);
                $stmt = $pdo->prepare("UPDATE recenzija SET dozvola = ? WHERE id = ?");
                $stmt->execute([$new, $id]);
            } elseif ($_POST['action'] === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM recenzija WHERE id = ?");
                $stmt->execute([$id]);
            }
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            header('Location: admin_recenzije.php' . ($qs ? ('?' . $qs) : ''));
            exit();
        } catch (PDOException $e) {
            $error = 'Greška pri ažuriranju: ' . $e->getMessage();
        }
    }
}

// Filteri
$vozilo = isset($_GET['vozilo']) ? trim($_GET['vozilo']) : '';
$dozvola = isset($_GET['dozvola']) ? trim($_GET['dozvola']) : '';

$where = [];
$params = [];

if ($vozilo !== '') {
    $where[] = '(v.marka LIKE ? OR v.model LIKE ?)';
    $params[] = "%$vozilo%"; $params[] = "%$vozilo%";
}
if ($dozvola !== '') {
    $where[] = 'r.dozvola = ?';
    $params[] = (int)$dozvola;
}

$sql = "SELECT r.*, n.ime, n.prezime, v.marka, v.model
        FROM recenzija r
        JOIN nalog n ON r.nalog_fk = n.id
        JOIN vozilo v ON r.vozilo_fk = v.id";
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.id DESC';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recenzije = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju: ' . $e->getMessage();
    $recenzije = [];
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje recenzijama</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
<div class="page-container">
<?php include 'navbar.php'; ?>
<div class="page-content">

<div class="container mt-5">
    <h2>Recenzije - upravljanje</h2>
    <p class="lead">Pretraga po vozilu i dozvoli, odobravanje/uklanjanje, brisanje.</p>
    <hr>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-4">
            <label class="form-label">Vozilo (marka ili model)</label>
            <input type="text" name="vozilo" class="form-control" value="<?php echo htmlspecialchars($vozilo); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Dozvola</label>
            <select name="dozvola" class="form-select">
                <option value="">Sve</option>
                <option value="1" <?php echo $dozvola==='1'?'selected':''; ?>>Odobreno</option>
                <option value="0" <?php echo $dozvola==='0'?'selected':''; ?>>Na čekanju</option>
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-primary">Filtriraj</button>
        </div>
        <div class="col-md-2 d-grid">
            <a href="admin_recenzije.php" class="btn btn-outline-secondary">Resetuj filtere</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Korisnik</th>
                    <th>Vozilo</th>
                    <th>Ocena</th>
                    <th>Komentar</th>
                    <th>Dozvola</th>
                    <th>Akcije</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recenzije)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Nema rezultata.</td></tr>
                <?php else: foreach ($recenzije as $r): ?>
                    <tr>
                        <td><?php echo $r['id']; ?></td>
                        <td><?php echo htmlspecialchars($r['ime'] . ' ' . $r['prezime']); ?></td>
                        <td><?php echo htmlspecialchars($r['marka'] . ' ' . $r['model']); ?></td>
                        <td><?php echo (int)$r['ocena']; ?>/5</td>
                        <td style="max-width: 420px; white-space: pre-wrap;"><?php echo htmlspecialchars($r['komentar']); ?></td>
                        <td>
                            <?php echo $r['dozvola'] ? '<span class="badge bg-success">Odobreno</span>' : '<span class="badge bg-warning">Na čekanju</span>'; ?>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <input type="hidden" name="dozvola" value="<?php echo $r['dozvola'] ? 0 : 1; ?>">
                                <button class="btn btn-sm btn-outline-primary">
                                    <?php echo $r['dozvola'] ? 'Poništi dozvolu' : 'Odobri'; ?>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Obrisati ovu recenziju?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                <button class="btn btn-sm btn-outline-danger">Obriši</button>
                            </form>
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


