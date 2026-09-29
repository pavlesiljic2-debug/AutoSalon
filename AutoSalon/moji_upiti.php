<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen i da je uloga 1 (korisnik)
if (!isset($_SESSION['user_id']) || !isset($_SESSION['uloga']) || (int)$_SESSION['uloga'] !== 1) {
    header('Location: login.php');
    exit();
}

// Učitava upite korisnika
try {
    $stmt = $pdo->prepare("
        SELECT u.*, v.marka, v.model, v.godiste, v.cena 
        FROM upit u 
        LEFT JOIN vozilo v ON u.vozilo_fk = v.id 
        WHERE u.email = ? 
        ORDER BY u.id DESC
    ");
    $stmt->execute([$_SESSION['email']]);
    $upiti = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju upita: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moji upiti - Auto Salon</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h2>Moji upiti</h2>
                <p class="lead">Ovde možete videti sve upite koje ste poslali.</p>
                <hr>
            </div>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($upiti)): ?>
            <div class="alert alert-info text-center">
                <h5>Nemate poslatih upita</h5>
                <p>Pošaljite nam upit ako imate pitanja o vozilima ili našim uslugama.</p>
                <a href="kontakt.php" class="btn btn-primary">Pošalji upit</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($upiti as $upit): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><?php echo htmlspecialchars($upit['naziv']); ?></h5>
                            <small class="text-muted">#<?php echo $upit['id']; ?></small>
                        </div>
                        <div class="card-body">
                            <?php if ($upit['vozilo_fk'] && $upit['marka']): ?>
                            <div class="mb-3">
                                <strong>Vezano za vozilo:</strong><br>
                                <span class="text-primary">
                                    <?php echo htmlspecialchars($upit['marka'] . ' ' . $upit['model']); ?>
                                    (<?php echo $upit['godiste']; ?>) - 
                                    <?php echo number_format($upit['cena'], 0); ?>€
                                </span>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <strong>Tip upita:</strong><br>
                                <span class="text-info">Opšti upit</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Vaše pitanje:</strong><br>
                                <p class="mt-2"><?php echo nl2br(htmlspecialchars($upit['pitanje'])); ?></p>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($upit['email']); ?>
                                </div>
                                <div class="col-6">
                                    <strong>Status:</strong><br>
                                    <?php 
                                        $status = $upit['status'] ?: 'Na čekanju';
                                        if ($status === 'Na čekanju') echo '<span class="badge bg-warning">Na čekanju</span>';
                                        elseif ($status === 'U obradi') echo '<span class="badge bg-info">U obradi</span>';
                                        elseif ($status === 'Odgovoreno') echo '<span class="badge bg-success">Odgovoreno</span>';
                                        else echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                    ?>
                                </div>
                            </div>
                            
                            <?php if ($upit['odgovor']): ?>
                            <div class="mb-3">
                                <strong>Odgovor:</strong><br>
                                <div class="alert alert-success">
                                    <?php echo nl2br(htmlspecialchars($upit['odgovor'])); ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <strong>Status:</strong> Vaš upit je na čekanju. Odgovorićemo vam uskoro.
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <?php if ($upit['vozilo_fk']): ?>
                                <a href="vozilo_detalji.php?id=<?php echo $upit['vozilo_fk']; ?>" 
                                   class="btn btn-outline-primary btn-sm">Pogledaj vozilo</a>
                                <?php endif; ?>
                                <a href="kontakt.php" class="btn btn-outline-secondary btn-sm">Pošalji novi upit</a>
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
                        <h5>Želite da pošaljete novi upit?</h5>
                        <a href="kontakt.php" class="btn btn-primary">Pošalji upit</a>
                        <a href="vozila.php" class="btn btn-outline-primary">Pogledaj katalog vozila</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informacije o upitima -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Informacije o upitima</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6>Vreme odgovora</h6>
                                <p>Obično odgovaramo u roku od 24 sata tokom radnih dana.</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Kontakt</h6>
                                <p>Za hitne upite pozovite nas na <strong>+381 11 123 4567</strong></p>
                            </div>
                            <div class="col-md-4">
                                <h6>Email</h6>
                                <p>Možete nas kontaktirati i na <strong>info@autosalon.rs</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
