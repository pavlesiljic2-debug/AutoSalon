<?php
session_start();
require_once 'db.php';

// Parametri za pretragu i filtriranje
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$marka_filter = isset($_GET['marka']) ? trim($_GET['marka']) : '';
$model_filter = isset($_GET['model']) ? trim($_GET['model']) : '';
$cena_od = isset($_GET['cena_od']) ? (float)$_GET['cena_od'] : '';
$cena_do = isset($_GET['cena_do']) ? (float)$_GET['cena_do'] : '';
$godina_od = isset($_GET['godina_od']) ? (int)$_GET['godina_od'] : '';
$godina_do = isset($_GET['godina_do']) ? (int)$_GET['godina_do'] : '';

// Kreiranje SQL upita sa filtrima
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(marka LIKE ? OR model LIKE ? OR opis LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($marka_filter)) {
    $where_conditions[] = "marka = ?";
    $params[] = $marka_filter;
}

if (!empty($model_filter)) {
    $where_conditions[] = "model = ?";
    $params[] = $model_filter;
}

if (!empty($cena_od)) {
    $where_conditions[] = "cena >= ?";
    $params[] = $cena_od;
}

if (!empty($cena_do)) {
    $where_conditions[] = "cena <= ?";
    $params[] = $cena_do;
}

if (!empty($godina_od)) {
    $where_conditions[] = "godiste >= ?";
    $params[] = $godina_od;
}

if (!empty($godina_do)) {
    $where_conditions[] = "godiste <= ?";
    $params[] = $godina_do;
}

// Osnovni SQL upit
$sql = "SELECT * FROM vozilo";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY cena ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vozila = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju vozila: ' . $e->getMessage();
}

// Učitava sve marke za filter
try {
    $stmt = $pdo->prepare("SELECT DISTINCT marka FROM vozilo ORDER BY marka");
    $stmt->execute();
    $marke = $stmt->fetchAll();
} catch (PDOException $e) {
    $marke = [];
}

// Učitava modele za odabranu marku
$modeli = [];
if (!empty($marka_filter)) {
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT model FROM vozilo WHERE marka = ? ORDER BY model");
        $stmt->execute([$marka_filter]);
        $modeli = $stmt->fetchAll();
    } catch (PDOException $e) {
        $modeli = [];
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog vozila - CarPoint</title>
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
                <h2>Katalog vozila</h2>
                <p class="lead">Pronađite vozilo svojih snova u našoj bogatoj ponudi.</p>
                <hr>
            </div>
        </div>
        
        <!-- Filter forma -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Pretraga i filtriranje</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <!-- Pretraga -->
                            <div class="col-md-3">
                                <label for="search" class="form-label">Pretraga</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Marka, model, opis...">
                            </div>
                            
                            <!-- Marka -->
                            <div class="col-md-2">
                                <label for="marka" class="form-label">Marka</label>
                                <select class="form-select" id="marka" name="marka" onchange="this.form.submit()">
                                    <option value="">Sve marke</option>
                                    <?php foreach ($marke as $marka): ?>
                                        <option value="<?php echo htmlspecialchars($marka['marka']); ?>" 
                                                <?php echo $marka_filter == $marka['marka'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($marka['marka']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Model -->
                            <div class="col-md-2">
                                <label for="model" class="form-label">Model</label>
                                <select class="form-select" id="model" name="model">
                                    <option value="">Svi modeli</option>
                                    <?php foreach ($modeli as $model): ?>
                                        <option value="<?php echo htmlspecialchars($model['model']); ?>" 
                                                <?php echo $model_filter == $model['model'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($model['model']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Cena od -->
                            <div class="col-md-1">
                                <label for="cena_od" class="form-label">Cena od (€)</label>
                                <input type="number" class="form-control" id="cena_od" name="cena_od" 
                                       value="<?php echo $cena_od; ?>" min="0" step="100">
                            </div>
                            
                            <!-- Cena do -->
                            <div class="col-md-1">
                                <label for="cena_do" class="form-label">Cena do (€)</label>
                                <input type="number" class="form-control" id="cena_do" name="cena_do" 
                                       value="<?php echo $cena_do; ?>" min="0" step="100">
                            </div>
                            
                            <!-- Godina od -->
                            <div class="col-md-1">
                                <label for="godina_od" class="form-label">Godina od</label>
                                <input type="number" class="form-control" id="godina_od" name="godina_od" 
                                       value="<?php echo $godina_od; ?>" min="1900" max="2030">
                            </div>
                            
                            <!-- Godina do -->
                            <div class="col-md-1">
                                <label for="godina_do" class="form-label">Godina do</label>
                                <input type="number" class="form-control" id="godina_do" name="godina_do" 
                                       value="<?php echo $godina_do; ?>" min="1900" max="2030">
                            </div>
                            
                            <!-- Dugmad -->
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Filtriraj</button>
                                </div>
                            </div>
                            
                            <div class="col-md-1">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <a href="vozila.php" class="btn btn-outline-secondary">Resetuj</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rezultati -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Rezultati pretrage (<?php echo count($vozila); ?> vozila)</h4>
                    <?php if (!empty($search) || !empty($marka_filter) || !empty($model_filter) || !empty($cena_od) || !empty($cena_do) || !empty($godina_od) || !empty($godina_do)): ?>
                        <span class="badge bg-info">Filtrirano</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Katalog vozila -->
        <div class="row">
            <?php if (empty($vozila)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h5>Nema vozila koja odgovaraju vašim kriterijumima</h5>
                        <p>Pokušajte da promenite filtere ili pretragu.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($vozila as $vozilo): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <!-- Slika vozila -->
                        <div class="position-relative">
                            <?php if ($vozilo['naziv_slike']): ?>
                                <img src="images/<?php echo htmlspecialchars($vozilo['naziv_slike']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?>"
                                     style="height: 250px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 250px;">
                                    <div class="text-center text-muted">
                                        <i class="bi bi-car-front" style="font-size: 3rem;"></i>
                                        <p class="mt-2">Nema slike</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Badge za cenu -->
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge-price fs-6"><?php echo number_format($vozilo['cena'], 0); ?>€</span>
                            </div>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php echo htmlspecialchars($vozilo['marka'] . ' ' . $vozilo['model']); ?>
                            </h5>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <strong>Godište:</strong> <?php echo $vozilo['godiste']; ?> |
                                    <strong>Tip motora:</strong> <?php echo htmlspecialchars($vozilo['tip_motora'] ?: 'N/A'); ?>
                                </small>
                            </div>
                            
                            <?php if ($vozilo['kubikaza_motora'] || $vozilo['snaga_motora']): ?>
                            <div class="mb-2">
                                <small class="text-muted">
                                    <?php if ($vozilo['kubikaza_motora']): ?>
                                        <strong>Kubikaža:</strong> <?php echo $vozilo['kubikaza_motora']; ?>cm³
                                    <?php endif; ?>
                                    <?php if ($vozilo['kubikaza_motora'] && $vozilo['snaga_motora']): ?> | <?php endif; ?>
                                    <?php if ($vozilo['snaga_motora']): ?>
                                        <strong>Snaga:</strong> <?php echo $vozilo['snaga_motora']; ?>KS
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($vozilo['opis']): ?>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($vozilo['opis'], 0, 100)); ?>
                                <?php if (strlen($vozilo['opis']) > 100): ?>...<?php endif; ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <a href="vozilo_detalji.php?id=<?php echo $vozilo['id']; ?>" 
                                       class="btn btn-primary">Pogledaj detalje</a>
                                    <a href="kontakt.php?vozilo_id=<?php echo $vozilo['id']; ?>" 
                                       class="btn btn-outline-primary">Kontaktiraj nas</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    </div>
    <?php include 'footer.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-submit forma kada se promeni marka
        document.getElementById('marka').addEventListener('change', function() {
            this.form.submit();
        });
    </script>
</body>
</html>
