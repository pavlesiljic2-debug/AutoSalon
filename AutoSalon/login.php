<?php
session_start();
require_once 'db.php';

$error = '';

// Ako je korisnik već prijavljen, preusmeri ga na početnu
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $lozinka = $_POST['lozinka'];
    
    if (empty($email) || empty($lozinka)) {
        $error = 'Unesite email i lozinku!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, ime, prezime, email, lozinka, uloga FROM nalog WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($lozinka, $user['lozinka'])) {
                // Uspešna prijava
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ime'] = $user['ime'];
                $_SESSION['prezime'] = $user['prezime'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['uloga'] = $user['uloga'];
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Neispravni podaci za prijavu!';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri prijavi: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava - Auto Salon</title>
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
                        <h3 class="text-center">Prijava</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="lozinka" class="form-label">Lozinka</label>
                                <input type="password" class="form-control" id="lozinka" name="lozinka" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Prijavi se</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p>Nemaš nalog? <a href="register.php">Registruj se</a></p>
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
    </div>
</body>
</html>
