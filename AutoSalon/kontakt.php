<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

// Uzima vozilo_id iz URL parametra
$vozilo_id = isset($_GET['vozilo_id']) ? (int)$_GET['vozilo_id'] : null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $naziv = trim($_POST['naziv']);
    $pitanje = trim($_POST['pitanje']);
    $vozilo_id = isset($_POST['vozilo_id']) ? (int)$_POST['vozilo_id'] : null;
    
    // Validacija
    if (empty($email) || empty($naziv) || empty($pitanje)) {
        $error = 'Sva polja su obavezna!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Neispravna email adresa!';
    } else {
        try {
            // Čuva upit u bazu (status: Na čekanju)
            $stmt = $pdo->prepare("INSERT INTO upit (vozilo_fk, email, naziv, pitanje, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$vozilo_id, $email, $naziv, $pitanje, 'Na čekanju']);
            
            $success = 'Vaš upit je uspešno poslat! Odgovorićemo vam uskoro.';
            
            // Resetuje formu nakon uspešnog slanja
            $email = '';
            $naziv = '';
            $pitanje = '';
        } catch (PDOException $e) {
            $error = 'Greška pri slanju upita: ' . $e->getMessage();
        }
    }
}

// Ako je korisnik prijavljen, uzmi njegov email
$user_email = '';
if (isset($_SESSION['user_id'])) {
    $user_email = $_SESSION['email'];
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontakt - Auto Salon</title>
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
                <h2>Kontakt</h2>
                <p class="lead">Imate pitanje? Pošaljite nam upit i odgovorićemo vam uskoro.</p>
                <hr>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Pošaljite nam upit</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($vozilo_id): ?>
                                <input type="hidden" name="vozilo_id" value="<?php echo $vozilo_id; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email adresa</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_email); ?>" 
                                       <?php echo isset($_SESSION['user_id']) ? 'readonly' : ''; ?> required>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="form-text">Vaš email je automatski popunjen jer ste prijavljeni.</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="naziv" class="form-label">Naslov upita</label>
                                <input type="text" class="form-control" id="naziv" name="naziv" 
                                       value="<?php echo isset($_POST['naziv']) ? htmlspecialchars($_POST['naziv']) : ''; ?>" 
                                       placeholder="npr. Pitanje o BMW X5" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pitanje" class="form-label">Vaše pitanje</label>
                                <textarea class="form-control" id="pitanje" name="pitanje" rows="5" 
                                          placeholder="Opišite vaše pitanje ili zahtev..." required><?php echo isset($_POST['pitanje']) ? htmlspecialchars($_POST['pitanje']) : ''; ?></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Pošalji upit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Kontakt informacije</h4>
                    </div>
                    <div class="card-body">
                        <h6>Auto Salon</h6>
                        <p>
                            <strong>Adresa:</strong><br>
                            Bulevar Kralja Aleksandra 123<br>
                            11000 Beograd, Srbija
                        </p>
                        
                        <p>
                            <strong>Telefon:</strong><br>
                            +381 11 123 4567
                        </p>
                        
                        <p>
                            <strong>Email:</strong><br>
                            info@autosalon.rs
                        </p>
                        
                        <p>
                            <strong>Radno vreme:</strong><br>
                            Ponedeljak - Petak: 08:00 - 20:00<br>
                            Subota: 09:00 - 16:00<br>
                            Nedelja: Zatvoreno
                        </p>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Često postavljana pitanja</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Da li nudite finansiranje?</strong><br>
                        Da, nudimo različite opcije finansiranja.</p>
                        
                        <p><strong>Da li vozila imaju garanciju?</strong><br>
                        Sva vozila imaju garanciju od 12 meseci.</p>
                        
                        <p><strong>Da li možete da organizujete test vožnju?</strong><br>
                        Da, možete zatražiti test vožnju za bilo koje vozilo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
