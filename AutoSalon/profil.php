<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Ažuriranje ličnih podataka
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    
    if (empty($ime) || empty($prezime) || empty($email)) {
        $error = 'Sva polja su obavezna!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Neispravna email adresa!';
    } else {
        try {
            // Proverava da li email već postoji kod drugog korisnika
            $stmt = $pdo->prepare("SELECT id FROM nalog WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email adresa već postoji!';
            } else {
                // Ažurira podatke
                $stmt = $pdo->prepare("UPDATE nalog SET ime = ?, prezime = ?, email = ? WHERE id = ?");
                $stmt->execute([$ime, $prezime, $email, $_SESSION['user_id']]);
                
                // Ažurira sesiju
                $_SESSION['ime'] = $ime;
                $_SESSION['prezime'] = $prezime;
                $_SESSION['email'] = $email;
                
                $success = 'Podaci su uspešno ažurirani!';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri ažuriranju: ' . $e->getMessage();
        }
    }
}

// Promena lozinke
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    $trenutna_lozinka = $_POST['trenutna_lozinka'];
    $nova_lozinka = $_POST['nova_lozinka'];
    $potvrda_lozinke = $_POST['potvrda_lozinke'];
    
    if (empty($trenutna_lozinka) || empty($nova_lozinka) || empty($potvrda_lozinke)) {
        $error = 'Sva polja za lozinku su obavezna!';
    } elseif ($nova_lozinka !== $potvrda_lozinke) {
        $error = 'Nove lozinke se ne poklapaju!';
    } elseif (strlen($nova_lozinka) < 6) {
        $error = 'Nova lozinka mora imati najmanje 6 karaktera!';
    } else {
        try {
            // Proverava trenutnu lozinku
            $stmt = $pdo->prepare("SELECT lozinka FROM nalog WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (password_verify($trenutna_lozinka, $user['lozinka'])) {
                // Ažurira lozinku
                $hashed_password = password_hash($nova_lozinka, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE nalog SET lozinka = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success = 'Lozinka je uspešno promenjena!';
            } else {
                $error = 'Trenutna lozinka nije ispravna!';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri promeni lozinke: ' . $e->getMessage();
        }
    }
}

// Učitava trenutne podatke korisnika
try {
    $stmt = $pdo->prepare("SELECT ime, prezime, email, uloga, datum_registracije FROM nalog WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju podataka: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moj profil - Auto Salon</title>
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
                <h2>Moj profil</h2>
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
            <!-- Lični podaci -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Lični podaci</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label for="ime" class="form-label">Ime</label>
                                <input type="text" class="form-control" id="ime" name="ime" 
                                       value="<?php echo htmlspecialchars($user_data['ime']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prezime" class="form-label">Prezime</label>
                                <input type="text" class="form-control" id="prezime" name="prezime" 
                                       value="<?php echo htmlspecialchars($user_data['prezime']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Ažuriraj podatke</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Promena lozinke -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Promena lozinke</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="mb-3">
                                <label for="trenutna_lozinka" class="form-label">Trenutna lozinka</label>
                                <input type="password" class="form-control" id="trenutna_lozinka" name="trenutna_lozinka" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nova_lozinka" class="form-label">Nova lozinka</label>
                                <input type="password" class="form-control" id="nova_lozinka" name="nova_lozinka" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="potvrda_lozinke" class="form-label">Potvrdi novu lozinku</label>
                                <input type="password" class="form-control" id="potvrda_lozinke" name="potvrda_lozinke" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-warning">Promeni lozinku</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Informacije o nalogu</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>ID korisnika:</strong> <?php echo $_SESSION['user_id']; ?></p>
                        <p><strong>Uloga:</strong> 
                            <?php 
                            if ($user_data['uloga'] == 1) {
                                echo 'Običan korisnik';
                            } elseif ($user_data['uloga'] == 2) {
                                echo 'Moderator';
                            } elseif ($user_data['uloga'] == 3) {
                                echo 'Administrator';
                            } else {
                                echo 'Nepoznata uloga';
                            }
                            ?>
                        </p>
                        <p><strong>Registrovano:</strong> 
                            <?php 
                            if (isset($user_data['datum_registracije'])) {
                                echo date('d.m.Y H:i', strtotime($user_data['datum_registracije']));
                            } else {
                                echo 'Nepoznato';
                            }
                            ?>
                        </p>
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
