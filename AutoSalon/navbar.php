<?php
// session_start() se poziva u glavnim stranicama
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark" style="position: relative; z-index: 10000;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php"><i class="bi bi-speedometer2 me-2"></i>CarPoint</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Početna</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vozila.php">Vozila</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="kontakt.php">Kontakt</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['ime'] . ' ' . $_SESSION['prezime']); ?>
                        </a>
                        <ul class="dropdown-menu" style="z-index: 99999 !important; position: absolute !important;">
                            <li><a class="dropdown-item" href="profil.php"><i class="bi bi-person-circle me-2"></i>Moj profil</a></li>
                            <?php if (isset($_SESSION['uloga']) && (int)$_SESSION['uloga'] === 1): ?>
                            <li><a class="dropdown-item" href="zahtev_test_voznja.php"><i class="bi bi-speedometer2 me-2"></i>Zakaži test vožnju</a></li>
                            <li><a class="dropdown-item" href="moji_zahtevi.php"><i class="bi bi-calendar-check me-2"></i>Moji zahtevi</a></li>
                            <li><a class="dropdown-item" href="moji_upiti.php"><i class="bi bi-chat-left-dots me-2"></i>Moji upiti</a></li>
                            <?php endif; ?>
                            <?php if ($_SESSION['uloga'] == 2 || $_SESSION['uloga'] == 3): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin_upiti.php"><i class="bi bi-inbox me-2"></i>Upravljanje upitima</a></li>
                            <li><a class="dropdown-item" href="admin_recenzije.php"><i class="bi bi-star-half me-2"></i>Recenzije</a></li>
                            <li><a class="dropdown-item" href="admin_test_voznje.php"><i class="bi bi-calendar-event me-2"></i>Test vožnje</a></li>
                            <?php endif; ?>
                            <?php if ($_SESSION['uloga'] == 3): ?>
                            <li><a class="dropdown-item" href="admin.php"><i class="bi bi-sliders me-2"></i>Admin Panel</a></li>
                            <li><a class="dropdown-item" href="admin_vozila.php"><i class="bi bi-car-front me-2"></i>Upravljanje vozilima</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Odjavi se</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Prijavi se</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Registruj se</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
