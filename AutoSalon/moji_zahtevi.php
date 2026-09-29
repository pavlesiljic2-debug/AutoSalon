<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen i da je uloga 1 (korisnik)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['uloga']) || (int)$_SESSION['uloga'] !== 1) {
    header('Location: login.php');
    exit();
}

// Učitava zahteve korisnika
try {
    $stmt = $pdo->prepare("
        SELECT z.*, v.marka, v.model, v.godiste, v.cena 
        FROM zahtev_za_test_voznju z 
        JOIN vozilo v ON z.vozilo_fk = v.id 
        WHERE z.nalog_fk = ? 
        ORDER BY z.datum DESC, z.vreme_od DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $zahtevi = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju zahteva: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moji zahtevi za test vožnju - Auto Salon</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <?php include 'navbar.php'; ?>
    <div class="page-content">
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h2>Moji zahtevi za test vožnju</h2>
                <p class="lead">Ovde možete videti sve vaše zahteve za test vožnju.</p>
                <hr>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($zahtevi)): ?>
            <div class="alert alert-info text-center">
                <h5>Nemate poslatih zahteva</h5>
                <p>Zakažite test vožnju za vozilo koje vas zanima.</p>
                <a href="zahtev_test_voznja.php" class="btn btn-primary">Zakaži test vožnju</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($zahtevi as $zahtev): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><?php echo htmlspecialchars($zahtev['marka'] . ' ' . $zahtev['model']); ?></h5>
                            <?php
                            switch($zahtev['status']) {
                                case 'Na čekanju':
                                    echo '<span class="badge bg-warning">Na čekanju</span>';
                                    break;
                                case 'Odobren':
                                    echo '<span class="badge bg-success">Odobren</span>';
                                    break;
                                case 'Odbijen':
                                    echo '<span class="badge bg-danger">Odbijen</span>';
                                    break;
                                case 'Otkazan':
                                    echo '<span class="badge bg-secondary">Otkazan</span>';
                                    break;
                                default:
                                    echo '<span class="badge bg-info">' . htmlspecialchars($zahtev['status']) . '</span>';
                            }
                            ?>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Godište:</strong><br>
                                    <?php echo $zahtev['godiste']; ?>
                                </div>
                                <div class="col-6">
                                    <strong>Cena:</strong><br>
                                    <?php echo number_format($zahtev['cena'], 0); ?>€
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-6">
                                    <strong>Datum:</strong><br>
                                    <?php echo date('d.m.Y', $zahtev['datum']); ?>
                                </div>
                                <div class="col-6">
                                    <strong>Vreme:</strong><br>
                                    <?php echo $zahtev['vreme_od']; ?>:00 - <?php echo $zahtev['vrme_do']; ?>:00
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="vozilo_detalji.php?id=<?php echo $zahtev['vozilo_fk']; ?>" 
                                   class="btn btn-outline-primary btn-sm">Pogledaj vozilo</a>
                                <?php if ($zahtev['status'] == 'Na čekanju'): ?>
                                <small class="text-muted">Vaš zahtev je na čekanju. Kontaktiraćemo vas uskoro.</small>
                                <?php elseif ($zahtev['status'] == 'Odobren'): ?>
                                <small class="text-success">Vaš zahtev je odobren! Očekujte poziv za potvrdu.</small>
                                <?php elseif ($zahtev['status'] == 'Odbijen'): ?>
                                <small class="text-danger">Vaš zahtev je odbijen. Kontaktirajte nas za više informacija.</small>
                                <?php elseif ($zahtev['status'] == 'Otkazan'): ?>
                                <small class="text-secondary">Vaš zahtev je otkazan.</small>
                                <?php else: ?>
                                <small class="text-info">Status: <?php echo htmlspecialchars($zahtev['status']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5>Želite da zakažete novi test vožnju?</h5>
                        <a href="zahtev_test_voznja.php" class="btn btn-primary">Zakaži test vožnju</a>
                        <a href="vozila.php" class="btn btn-outline-primary">Pogledaj katalog vozila</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    </div>
    </div>
    <?php include 'footer.php'; ?>
    
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
