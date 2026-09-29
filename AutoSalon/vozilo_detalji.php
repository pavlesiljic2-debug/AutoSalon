<?php
session_start();
require_once 'db.php';

$vozilo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($vozilo_id <= 0) {
    header('Location: vozila.php');
    exit();
}

// Učitava detalje vozila
try {
    $stmt = $pdo->prepare("SELECT * FROM vozilo WHERE id = ?");
    $stmt->execute([$vozilo_id]);
    $vozilo = $stmt->fetch();
    
    if (!$vozilo) {
        header('Location: vozila.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: vozila.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?> - Auto Salon</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <?php include 'navbar.php'; ?>
    <div class="page-content">
    <div class="container mt-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Početna</a></li>
                <li class="breadcrumb-item"><a href="vozila.php">Vozila</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <!-- Slika vozila -->
            <div class="col-md-6">
                <div class="card">
                    <?php if ($vozilo['naziv_slike']): ?>
                        <img src="images/<?php echo htmlspecialchars($vozilo['naziv_slike']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?>"
                             style="height: 400px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 400px;">
                            <div class="text-center text-muted">
                                <i class="bi bi-car-front" style="font-size: 5rem;"></i>
                                <p class="mt-3 fs-5">Nema slike</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Detalji vozila -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?></h2>
                        <h3 class="text-success"><?php echo number_format($vozilo['cena'], 0); ?>€</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Marka:</strong><br>
                                <?php echo htmlspecialchars($vozilo['marka']); ?>
                            </div>
                            <div class="col-6">
                                <strong>Model:</strong><br>
                                <?php echo htmlspecialchars($vozilo['model']); ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <strong>Godište:</strong><br>
                                <?php echo $vozilo['godiste']; ?>
                            </div>
                            <div class="col-6">
                                <strong>Tip motora:</strong><br>
                                <?php echo htmlspecialchars($vozilo['tip_motora'] ?: 'N/A'); ?>
                            </div>
                        </div>
                        
                        <?php if ($vozilo['kubikaza_motora'] || $vozilo['snaga_motora']): ?>
                        <div class="row mb-3">
                            <?php if ($vozilo['kubikaza_motora']): ?>
                            <div class="col-6">
                                <strong>Kubikaža:</strong><br>
                                <?php echo $vozilo['kubikaza_motora']; ?> cm³
                            </div>
                            <?php endif; ?>
                            <?php if ($vozilo['snaga_motora']): ?>
                            <div class="col-6">
                                <strong>Snaga:</strong><br>
                                <?php echo $vozilo['snaga_motora']; ?> KS
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($vozilo['opis']): ?>
                        <div class="mb-3">
                            <strong>Opis:</strong><br>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($vozilo['opis'])); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="kontakt.php?vozilo_id=<?php echo $vozilo['id']; ?>" 
                               class="btn btn-primary btn-lg">Kontaktiraj nas</a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="zahtev_test_voznja.php?vozilo_id=<?php echo $vozilo['id']; ?>" 
                               class="btn btn-outline-primary">Zatraži test vožnju</a>
                            <?php endif; ?>
                            <a href="vozila.php" class="btn btn-outline-secondary">Nazad na katalog</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recenzije -->
        <div class="row mt-5">
            <div class="col-12">
                <h4>Recenzije</h4>
                <hr>
            </div>
        </div>

        <?php
        // Ubacivanje recenzije (samo prijavljeni korisnici sa ulogom 1; jedna recenzija po vozilu)
        if (isset($_SESSION['user_id']) && isset($_SESSION['uloga']) && (int)$_SESSION['uloga'] === 1 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='add_review') {
            $ocena = isset($_POST['ocena']) ? (int)$_POST['ocena'] : 0;
            $komentar = trim($_POST['komentar'] ?? '');
            if ($ocena >= 1 && $ocena <= 5 && $komentar !== '') {
                try {
                    // Proveri da li već postoji recenzija za ovo vozilo od ovog korisnika
                    $stmt = $pdo->prepare("SELECT id FROM recenzija WHERE nalog_fk = ? AND vozilo_fk = ?");
                    $stmt->execute([$_SESSION['user_id'], $vozilo['id']]);
                    if ($stmt->rowCount() === 0) {
                        $stmt = $pdo->prepare("INSERT INTO recenzija (nalog_fk, vozilo_fk, komentar, ocena, dozvola) VALUES (?, ?, ?, ?, 0)");
                        $stmt->execute([$_SESSION['user_id'], $vozilo['id'], $komentar, $ocena]);
                        echo '<div class="alert alert-success">Recenzija je poslata i čeka odobrenje.</div>';
                    } else {
                        echo '<div class="alert alert-warning">Već ste ostavili recenziju za ovo vozilo.</div>';
                    }
                } catch (PDOException $e) {
                    echo '<div class="alert alert-danger">Greška pri slanju recenzije.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Unesite ocenu (1-5) i komentar.</div>';
            }
        }
        ?>

        <div class="row">
            <div class="col-md-6">
                <?php if (isset($_SESSION['user_id']) && isset($_SESSION['uloga']) && (int)$_SESSION['uloga'] === 1): ?>
                <div class="card mb-4">
                    <div class="card-header">Ostavite recenziju</div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_review">
                            <div class="mb-3">
                                <label class="form-label">Ocena (1-5)</label>
                                <select name="ocena" class="form-select" required>
                                    <option value="">Izaberite ocenu</option>
                                    <?php for ($i=1;$i<=5;$i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Komentar</label>
                                <textarea name="komentar" class="form-control" rows="3" maxlength="1000" required></textarea>
                            </div>
                            <button class="btn btn-primary">Pošalji recenziju</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">Samo korisnici sa korisničkom ulogom mogu ostaviti recenziju.</div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Ocene i komentari (odobreni)</div>
                    <div class="card-body">
                        <?php
                        try {
                            // Prosečna ocena i broj recenzija
                            $stmt = $pdo->prepare("SELECT AVG(ocena) AS avg_ocena, COUNT(*) AS br FROM recenzija WHERE vozilo_fk = ? AND dozvola = 1");
                            $stmt->execute([$vozilo['id']]);
                            $aggr = $stmt->fetch();
                            $avg = $aggr && $aggr['br']>0 ? number_format($aggr['avg_ocena'], 1) : 'N/A';
                            $count = $aggr ? (int)$aggr['br'] : 0;
                            echo '<p><strong>Prosečna ocena:</strong> ' . $avg . ' (' . $count . ' recenzija)</p>';

                            // Lista recenzija
                            $stmt = $pdo->prepare("SELECT r.komentar, r.ocena, n.ime, n.prezime FROM recenzija r JOIN nalog n ON r.nalog_fk = n.id WHERE r.vozilo_fk = ? AND r.dozvola = 1 ORDER BY r.id DESC");
                            $stmt->execute([$vozilo['id']]);
                            $recs = $stmt->fetchAll();
                            if (empty($recs)) {
                                echo '<p class="text-muted">Još uvek nema odobrenih recenzija.</p>';
                            } else {
                                foreach ($recs as $rec) {
                                    echo '<div class="mb-3">';
                                    echo '<div><strong>Ocena:</strong> ' . (int)$rec['ocena'] . '/5</div>';
                                    echo '<div class="text-muted">' . htmlspecialchars($rec['ime'] . ' ' . $rec['prezime']) . '</div>';
                                    echo '<p class="mb-0">' . nl2br(htmlspecialchars($rec['komentar'])) . '</p>';
                                    echo '</div>';
                                }
                            }
                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">Greška pri učitavanju recenzija.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slična vozila -->
        <div class="row mt-5">
            <div class="col-12">
                <h4>Slična vozila</h4>
                <hr>
            </div>
        </div>
        
        <div class="row">
            <?php
            // Učitava slična vozila (ista marka)
            try {
                $stmt = $pdo->prepare("SELECT * FROM vozilo WHERE marka = ? AND id != ? ORDER BY cena ASC LIMIT 3");
                $stmt->execute([$vozilo['marka'], $vozilo['id']]);
                $slicna_vozila = $stmt->fetchAll();
                
                foreach ($slicna_vozila as $slicno_vozilo):
            ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <?php if ($slicno_vozilo['naziv_slike']): ?>
                        <img src="images/<?php echo htmlspecialchars($slicno_vozilo['naziv_slike']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($slicno_vozilo['marka'] . ' ' . $slicno_vozilo['model']); ?>"
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <div class="text-center text-muted">
                                <i class="bi bi-car-front" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($slicno_vozilo['marka'] . ' ' . $slicno_vozilo['model']); ?></h6>
                        <p class="card-text">
                            <strong><?php echo number_format($slicno_vozilo['cena'], 0); ?>€</strong><br>
                            <small class="text-muted"><?php echo $slicno_vozilo['godiste']; ?> | <?php echo htmlspecialchars($slicno_vozilo['tip_motora'] ?: 'N/A'); ?></small>
                        </p>
                        <a href="vozilo_detalji.php?id=<?php echo $slicno_vozilo['id']; ?>" class="btn btn-sm btn-outline-primary">Pogledaj</a>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            } catch (PDOException $e) {
                // Greška pri učitavanju sličnih vozila - ne prikazuje se ništa
            }
            ?>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
