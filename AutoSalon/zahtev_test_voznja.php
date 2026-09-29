<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen i da je uloga 1 (korisnik)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['uloga']) || (int)$_SESSION['uloga'] !== 1) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';
$vozilo_id = isset($_GET['vozilo_id']) ? (int)$_GET['vozilo_id'] : null;

// Učitava detalje vozila ako je specificiran
$vozilo = null;
if ($vozilo_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM vozilo WHERE id = ?");
        $stmt->execute([$vozilo_id]);
        $vozilo = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Greška pri učitavanju vozila: ' . $e->getMessage();
    }
}

// Obrada forme
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vozilo_id = (int)$_POST['vozilo_id'];
    $datum = $_POST['datum'];
    $vreme_od = (int)$_POST['vreme_od'];
    
    if (empty($vozilo_id) || empty($datum) || empty($vreme_od)) {
        $error = 'Sva polja su obavezna!';
    } else {
        // Validacija datuma (ne može biti u prošlosti)
        $datum_obj = new DateTime($datum);
        $danas = new DateTime();
        $danas->setTime(0, 0, 0);
        
        if ($datum_obj < $danas) {
            $error = 'Datum ne može biti u prošlosti!';
        } else {
            // Konvertuje datum u timestamp
            $datum_timestamp = strtotime($datum);
            
            // Proverava da li je termin već zauzet
            try {
                $stmt = $pdo->prepare("SELECT id FROM zahtev_za_test_voznju WHERE datum = ? AND vreme_od = ? AND status IN ('Na čekanju', 'Odobren')");
                $stmt->execute([$datum_timestamp, $vreme_od]);
                
                if ($stmt->rowCount() > 0) {
                    $error = 'Ovaj termin je već zauzet! Molimo izaberite drugi termin.';
                } else {
                    // Čuva zahtev
                    $vreme_do = $vreme_od + 1; // Test vožnja traje 1 sat
                    $stmt = $pdo->prepare("INSERT INTO zahtev_za_test_voznju (nalog_fk, vozilo_fk, datum, vreme_od, vrme_do, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $vozilo_id, $datum_timestamp, $vreme_od, $vreme_do, 'Na čekanju']);
                    
                    $success = 'Vaš zahtev za test vožnju je uspešno poslat! Kontaktiraćemo vas uskoro.';
                }
            } catch (PDOException $e) {
                $error = 'Greška pri slanju zahteva: ' . $e->getMessage();
            }
        }
    }
}

// Učitava sva vozila za dropdown
try {
    $stmt = $pdo->prepare("SELECT id, marka, model, godiste, cena FROM vozilo ORDER BY marka, model");
    $stmt->execute();
    $vozila = $stmt->fetchAll();
} catch (PDOException $e) {
    $vozila = [];
}

// Generiše dostupne termine za odabrani datum
$dostupni_termini = [];
if (isset($_POST['datum']) || isset($_GET['datum'])) {
    $datum_za_proveru = isset($_POST['datum']) ? $_POST['datum'] : $_GET['datum'];
    
    try {
        // Konvertuje datum u timestamp
        $datum_timestamp = strtotime($datum_za_proveru);
        
        // Učitava zauzete termine za taj datum
        $stmt = $pdo->prepare("SELECT vreme_od FROM zahtev_za_test_voznju WHERE datum = ? AND status IN ('Na čekanju', 'Odobren')");
        $stmt->execute([$datum_timestamp]);
        $zauzeti_termini = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Generiše sve moguće termine (8-16)
        $svi_termini = range(8, 16);
        $dostupni_termini = array_diff($svi_termini, $zauzeti_termini);
    } catch (PDOException $e) {
        $error = 'Greška pri proveri dostupnosti: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zahtev za test vožnju - CarPoint</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <?php include 'navbar.php'; ?>
    <div class="page-content">
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-1">Zahtev za test vožnju</h2>
                <p class="text-muted">Zakažite test vožnju za vozilo koje vas zanima.</p>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Forma za zahtev -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Zakažite test vožnju</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($vozilo): ?>
                        <div class="alert alert-info">
                            <h5>Odabrano vozilo:</h5>
                            <p><strong><?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?></strong> 
                               (<?php echo $vozilo['godiste']; ?>) - 
                               <strong><?php echo number_format($vozilo['cena'], 0); ?>€</strong></p>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="testVoznjaForm">
                            <input type="hidden" name="vozilo_id" value="<?php echo $vozilo_id; ?>">
                            
                            <div class="mb-3">
                                <label for="vozilo_id_select" class="form-label">Vozilo</label>
                                <select class="form-select" id="vozilo_id_select" name="vozilo_id" required>
                                    <option value="">Izaberite vozilo</option>
                                    <?php foreach ($vozila as $v): ?>
                                        <option value="<?php echo $v['id']; ?>" 
                                                <?php echo ($vozilo_id == $v['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($v['marka'] . ' ' . $v['model'] . ' (' . $v['godiste'] . ') - ' . number_format($v['cena'], 0) . '€'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="datum" class="form-label">Datum</label>
                                <input type="date" class="form-control" id="datum" name="datum" 
                                       value="<?php echo isset($_POST['datum']) ? $_POST['datum'] : (isset($_GET['datum']) ? $_GET['datum'] : ''); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="vreme_od" class="form-label">Vreme</label>
                                <select class="form-select" id="vreme_od" name="vreme_od" required>
                                    <option value="">Izaberite vreme</option>
                                    <?php if (!empty($dostupni_termini)): ?>
                                        <?php foreach ($dostupni_termini as $termin): ?>
                                            <option value="<?php echo $termin; ?>" 
                                                    <?php echo (isset($_POST['vreme_od']) && $_POST['vreme_od'] == $termin) ? 'selected' : ''; ?>>
                                                <?php echo $termin; ?>:00 - <?php echo $termin + 1; ?>:00
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Prvo izaberite datum</option>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Radno vreme: 8:00 - 17:00. Test vožnja traje 1 sat.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Pošalji zahtev</button>
                                <small class="text-muted">Radno vreme: 8:00 - 17:00. Test vožnja traje 1 sat.</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Informacije -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Informacije o test vožnji</h5>
                    </div>
                    <div class="card-body">
                        <h6>Radno vreme:</h6>
                        <p>Ponedeljak - Petak: 8:00 - 17:00</p>
                        
                        <h6>Trajanje:</h6>
                        <p>Test vožnja traje 1 sat</p>
                        
                        <h6>Dostupni termini:</h6>
                        <p>8:00, 9:00, 10:00, 11:00, 12:00, 13:00, 14:00, 15:00, 16:00</p>
                        
                        <h6>Status zahteva:</h6>
                        <ul class="list-unstyled">
                            <li><span class="badge bg-warning">Na čekanju</span> - Vaš zahtev je poslat</li>
                            <li><span class="badge bg-success">Odobren</span> - Test vožnja je potvrđena</li>
                            <li><span class="badge bg-danger">Odbijen</span> - Zahtev je odbijen</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Moji zahtevi</h5>
                    </div>
                    <div class="card-body">
                        <a href="moji_zahtevi.php" class="btn btn-outline-primary">Pogledaj moje zahteve</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit forma kada se promeni datum
        document.getElementById('datum').addEventListener('change', function() {
            if (this.value) {
                window.location.href = 'zahtev_test_voznja.php?datum=' + this.value + '&vozilo_id=' + document.getElementById('vozilo_id_select').value;
            }
        });
        
        // Auto-submit forma kada se promeni vozilo
        document.getElementById('vozilo_id_select').addEventListener('change', function() {
            var datum = document.getElementById('datum').value;
            if (datum) {
                window.location.href = 'zahtev_test_voznja.php?datum=' + datum + '&vozilo_id=' + this.value;
            }
        });
    </script>
</body>
</html>
