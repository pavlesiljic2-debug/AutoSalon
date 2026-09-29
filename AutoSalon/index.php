<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarPoint - Početna</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="icons/bootstrap-icons-1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css?v=2" rel="stylesheet">
</head>
<body>
    <div class="page-container">
    <?php include 'navbar.php'; ?>
    <div class="page-content">
    <div class="container mt-5">
        
        
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Široka ponuda</h5>
                        <p class="card-text">Naš auto salon nudi široku paletu vozila različitih marki i modela.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Test vožnja</h5>
                        <p class="card-text">Zakažite test vožnju i iskusite vozilo pre kupovine.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">Profesionalni saveti</h5>
                        <p class="card-text">Naš tim stručnjaka će vam pomoći da pronađete idealno vozilo.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="alert alert-success">
                    <h4>Dobrodošli, <?php echo htmlspecialchars($_SESSION['ime'] . ' ' . $_SESSION['prezime']); ?>!</h4>
                    <p>Hvala Vam što ste se prijavili.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Registrujte se za bolje iskustvo</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Registracijom možete ostaviti recenzije, zatražiti test vožnju i pristupiti ekskluzivnim ponudama.</p>
                        <a href="register.php" class="btn btn-primary me-2">Registruj se</a>
                        <a href="login.php" class="btn btn-outline-primary">Prijavi se</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
