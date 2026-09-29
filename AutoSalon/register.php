<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $lozinka = $_POST['lozinka'];
    $potvrda_lozinke = $_POST['potvrda_lozinke'];
    
    // Validacija
    if (empty($ime) || empty($prezime) || empty($email) || empty($lozinka)) {
        $error = 'Sva polja su obavezna!';
    } elseif ($lozinka !== $potvrda_lozinke) {
        $error = 'Lozinke se ne poklapaju!';
    } elseif (strlen($lozinka) < 6) {
        $error = 'Lozinka mora imati najmanje 6 karaktera!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Neispravna email adresa!';
    } else {
        try {
            // Proverava da li email već postoji
            $stmt = $pdo->prepare("SELECT id FROM nalog WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email adresa već postoji!';
            } else {
                // Hash lozinke
                $hashed_password = password_hash($lozinka, PASSWORD_DEFAULT);
                
                // Ubacuje novog korisnika
                $stmt = $pdo->prepare("INSERT INTO nalog (ime, prezime, email, lozinka, uloga, datum_registracije) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$ime, $prezime, $email, $hashed_password, 1]); // 1 = običan korisnik
                
                $success = 'Uspešno ste se registrovali! Možete se prijaviti.';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri registraciji: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija - Auto Salon</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <?php include 'navbar.php'; ?>
    <div class="page-content">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Registracija</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime</label>
                                <input type="text" class="form-control" id="ime" name="ime" 
                                       value="<?php echo isset($_POST['ime']) ? htmlspecialchars($_POST['ime']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prezime" class="form-label">Prezime</label>
                                <input type="text" class="form-control" id="prezime" name="prezime" 
                                       value="<?php echo isset($_POST['prezime']) ? htmlspecialchars($_POST['prezime']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lozinka" class="form-label">Lozinka</label>
                                <input type="password" class="form-control" id="lozinka" name="lozinka" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="potvrda_lozinke" class="form-label">Potvrdi lozinku</label>
                                <input type="password" class="form-control" id="potvrda_lozinke" name="potvrda_lozinke" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registruj se</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Već imaš nalog? <a href="login.php">Prijavi se</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>
</html>
