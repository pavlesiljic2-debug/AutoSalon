<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen i da li je admin (uloga 3)
if (!isset($_SESSION['user_id']) || $_SESSION['uloga'] != 3) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Brisanje korisnika
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    // Ne dozvoljava brisanje samog sebe
    if ($user_id == $_SESSION['user_id']) {
        $error = 'Ne možete obrisati svoj nalog!';
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM nalog WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = 'Korisnik je uspešno obrisan!';
        } catch (PDOException $e) {
            $error = 'Greška pri brisanju: ' . $e->getMessage();
        }
    }
}

// Ažuriranje korisnika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $user_id = (int)$_POST['user_id'];
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $uloga = (int)$_POST['uloga'];
    
    if (empty($ime) || empty($prezime) || empty($email)) {
        $error = 'Sva polja su obavezna!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Neispravna email adresa!';
    } else {
        try {
            // Proverava da li email već postoji kod drugog korisnika
            $stmt = $pdo->prepare("SELECT id FROM nalog WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email adresa već postoji!';
            } else {
                $stmt = $pdo->prepare("UPDATE nalog SET ime = ?, prezime = ?, email = ?, uloga = ? WHERE id = ?");
                $stmt->execute([$ime, $prezime, $email, $uloga, $user_id]);
                $success = 'Korisnik je uspešno ažuriran!';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri ažuriranju: ' . $e->getMessage();
        }
    }
}

// Dodavanje novog korisnika
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $lozinka = $_POST['lozinka'];
    $uloga = (int)$_POST['uloga'];
    
    if (empty($ime) || empty($prezime) || empty($email) || empty($lozinka)) {
        $error = 'Sva polja su obavezna!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Neispravna email adresa!';
    } elseif (strlen($lozinka) < 6) {
        $error = 'Lozinka mora imati najmanje 6 karaktera!';
    } else {
        try {
            // Proverava da li email već postoji
            $stmt = $pdo->prepare("SELECT id FROM nalog WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $error = 'Email adresa već postoji!';
            } else {
                $hashed_password = password_hash($lozinka, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO nalog (ime, prezime, email, lozinka, uloga, datum_registracije) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$ime, $prezime, $email, $hashed_password, $uloga]);
                $success = 'Korisnik je uspešno dodat!';
            }
        } catch (PDOException $e) {
            $error = 'Greška pri dodavanju: ' . $e->getMessage();
        }
    }
}

// Učitava sve korisnike
try {
    $stmt = $pdo->prepare("SELECT id, ime, prezime, email, uloga FROM nalog ORDER BY id DESC");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju korisnika: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Auto Salon</title>
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
                <h2>Admin Panel - Upravljanje nalozima</h2>
                <p class="lead">Ovde možete upravljati svim korisničkim nalozima u sistemu.</p>
                <hr>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Dodavanje novog korisnika -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Dodaj novog korisnika</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="ime" class="form-label">Ime</label>
                                        <input type="text" class="form-control" id="ime" name="ime" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="prezime" class="form-label">Prezime</label>
                                        <input type="text" class="form-control" id="prezime" name="prezime" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="uloga" class="form-label">Uloga</label>
                                        <select class="form-select" id="uloga" name="uloga" required>
                                            <option value="1">Korisnik</option>
                                            <option value="2">Moderator</option>
                                            <option value="3">Admin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="mb-3">
                                        <label for="lozinka" class="form-label">Lozinka</label>
                                        <input type="password" class="form-control" id="lozinka" name="lozinka" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Dodaj korisnika</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista korisnika -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Svi korisnici (<?php echo count($users); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ime</th>
                                        <th>Prezime</th>
                                        <th>Email</th>
                                        <th>Uloga</th>
                                        <th>Akcije</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['ime']); ?></td>
                                        <td><?php echo htmlspecialchars($user['prezime']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            switch($user['uloga']) {
                                                case 1: echo '<span class="badge bg-primary">Korisnik</span>'; break;
                                                case 2: echo '<span class="badge bg-warning">Moderator</span>'; break;
                                                case 3: echo '<span class="badge bg-danger">Admin</span>'; break;
                                                default: echo '<span class="badge bg-secondary">Nepoznato</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['ime']); ?>', '<?php echo htmlspecialchars($user['prezime']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', <?php echo $user['uloga']; ?>)">
                                                Izmeni
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="admin.php?action=delete&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Da li ste sigurni da želite da obrišete ovog korisnika?')">
                                                Obriši
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal za izmenu korisnika -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Izmeni korisnika</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label for="edit_ime" class="form-label">Ime</label>
                            <input type="text" class="form-control" id="edit_ime" name="ime" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_prezime" class="form-label">Prezime</label>
                            <input type="text" class="form-control" id="edit_prezime" name="prezime" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_uloga" class="form-label">Uloga</label>
                            <select class="form-select" id="edit_uloga" name="uloga" required>
                                <option value="1">Korisnik</option>
                                <option value="2">Moderator</option>
                                <option value="3">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                        <button type="submit" class="btn btn-primary">Sačuvaj izmene</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
    <script>
        function editUser(id, ime, prezime, email, uloga) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_ime').value = ime;
            document.getElementById('edit_prezime').value = prezime;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_uloga').value = uloga;
            
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
    </script>
</body>
</html>
