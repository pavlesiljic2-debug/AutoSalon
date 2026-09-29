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

// Brisanje vozila
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $vozilo_id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM vozilo WHERE id = ?");
        $stmt->execute([$vozilo_id]);
        $success = 'Vozilo je uspešno obrisano!';
    } catch (PDOException $e) {
        $error = 'Greška pri brisanju: ' . $e->getMessage();
    }
}

// Ažuriranje vozila
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $vozilo_id = (int)$_POST['vozilo_id'];
    $marka = trim($_POST['marka']);
    $model = trim($_POST['model']);
    $godiste = (int)$_POST['godiste'];
    $tip_motora = trim($_POST['tip_motora']);
    $kubikaza_motora = (int)$_POST['kubikaza_motora'];
    $snaga_motora = (int)$_POST['snaga_motora'];
    $cena = (float)$_POST['cena'];
    $opis = trim($_POST['opis']);
    $naziv_slike = trim($_POST['naziv_slike']);
    
    if (empty($marka) || empty($model) || empty($godiste) || empty($cena)) {
        $error = 'Marka, model, godište i cena su obavezni!';
    } elseif ($godiste < 1900 || $godiste > date('Y') + 1) {
        $error = 'Neispravno godište!';
    } elseif ($cena <= 0) {
        $error = 'Cena mora biti veća od 0!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE vozilo SET marka = ?, model = ?, godiste = ?, tip_motora = ?, kubikaza_motora = ?, snaga_motora = ?, cena = ?, opis = ?, naziv_slike = ? WHERE id = ?");
            $stmt->execute([$marka, $model, $godiste, $tip_motora, $kubikaza_motora, $snaga_motora, $cena, $opis, $naziv_slike, $vozilo_id]);
            $success = 'Vozilo je uspešno ažurirano!';
        } catch (PDOException $e) {
            $error = 'Greška pri ažuriranju: ' . $e->getMessage();
        }
    }
}

// Dodavanje novog vozila
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $marka = trim($_POST['marka']);
    $model = trim($_POST['model']);
    $godiste = (int)$_POST['godiste'];
    $tip_motora = trim($_POST['tip_motora']);
    $kubikaza_motora = (int)$_POST['kubikaza_motora'];
    $snaga_motora = (int)$_POST['snaga_motora'];
    $cena = (float)$_POST['cena'];
    $opis = trim($_POST['opis']);
    $naziv_slike = trim($_POST['naziv_slike']);
    
    if (empty($marka) || empty($model) || empty($godiste) || empty($cena)) {
        $error = 'Marka, model, godište i cena su obavezni!';
    } elseif ($godiste < 1900 || $godiste > date('Y') + 1) {
        $error = 'Neispravno godište!';
    } elseif ($cena <= 0) {
        $error = 'Cena mora biti veća od 0!';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO vozilo (marka, model, godiste, tip_motora, kubikaza_motora, snaga_motora, cena, opis, naziv_slike) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$marka, $model, $godiste, $tip_motora, $kubikaza_motora, $snaga_motora, $cena, $opis, $naziv_slike]);
            $success = 'Vozilo je uspešno dodato!';
        } catch (PDOException $e) {
            $error = 'Greška pri dodavanju: ' . $e->getMessage();
        }
    }
}

// Učitava sva vozila
try {
    $stmt = $pdo->prepare("SELECT * FROM vozilo ORDER BY id DESC");
    $stmt->execute();
    $vozila = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Greška pri učitavanju vozila: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Upravljanje vozilima - Auto Salon</title>
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
                <h2>Admin Panel - Upravljanje vozilima</h2>
                <p class="lead">Ovde možete upravljati svim vozilima u sistemu.</p>
                <hr>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Dodavanje novog vozila -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Dodaj novo vozilo</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="marka" class="form-label">Marka *</label>
                                        <input type="text" class="form-control" id="marka" name="marka" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model *</label>
                                        <input type="text" class="form-control" id="model" name="model" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="godiste" class="form-label">Godište *</label>
                                        <input type="number" class="form-control" id="godiste" name="godiste" min="1900" max="2030" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="cena" class="form-label">Cena (€) *</label>
                                        <input type="number" class="form-control" id="cena" name="cena" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="naziv_slike" class="form-label">Naziv slike</label>
                                        <input type="text" class="form-control" id="naziv_slike" name="naziv_slike" placeholder="npr. bmw_x5.jpg">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="tip_motora" class="form-label">Tip motora</label>
                                        <select class="form-select" id="tip_motora" name="tip_motora">
                                            <option value="">Izaberi tip</option>
                                            <option value="Benzin">Benzin</option>
                                            <option value="Dizel">Dizel</option>
                                            <option value="Hibrid">Hibrid</option>
                                            <option value="Električni">Električni</option>
                                            <option value="Gas">Gas</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="kubikaza_motora" class="form-label">Kubikaža (cm³)</label>
                                        <input type="number" class="form-control" id="kubikaza_motora" name="kubikaza_motora" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="snaga_motora" class="form-label">Snaga (KS)</label>
                                        <input type="number" class="form-control" id="snaga_motora" name="snaga_motora" min="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="opis" class="form-label">Opis</label>
                                        <textarea class="form-control" id="opis" name="opis" rows="2" placeholder="Kratak opis vozila..."></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Dodaj vozilo</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista vozila -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Sva vozila (<?php echo count($vozila); ?>)</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Marka</th>
                                        <th>Model</th>
                                        <th>Godište</th>
                                        <th>Tip motora</th>
                                        <th>Kubikaža</th>
                                        <th>Snaga</th>
                                        <th>Cena (€)</th>
                                        <th>Slika</th>
                                        <th>Akcije</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vozila as $vozilo): ?>
                                    <tr>
                                        <td><?php echo $vozilo['id']; ?></td>
                                        <td><?php echo htmlspecialchars($vozilo['marka']); ?></td>
                                        <td><?php echo htmlspecialchars($vozilo['model']); ?></td>
                                        <td><?php echo $vozilo['godiste']; ?></td>
                                        <td><?php echo htmlspecialchars($vozilo['tip_motora']); ?></td>
                                        <td><?php echo $vozilo['kubikaza_motora']; ?> cm³</td>
                                        <td><?php echo $vozilo['snaga_motora']; ?> KS</td>
                                        <td><strong><?php echo number_format($vozilo['cena'], 2); ?></strong></td>
                                        <td>
                                            <?php if ($vozilo['naziv_slike']): ?>
                                                <span class="badge bg-success"><?php echo htmlspecialchars($vozilo['naziv_slike']); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Nema slike</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editVozilo(<?php echo $vozilo['id']; ?>, '<?php echo htmlspecialchars($vozilo['marka']); ?>', '<?php echo htmlspecialchars($vozilo['model']); ?>', <?php echo $vozilo['godiste']; ?>, '<?php echo htmlspecialchars($vozilo['tip_motora']); ?>', <?php echo $vozilo['kubikaza_motora']; ?>, <?php echo $vozilo['snaga_motora']; ?>, <?php echo $vozilo['cena']; ?>, '<?php echo htmlspecialchars($vozilo['opis']); ?>', '<?php echo htmlspecialchars($vozilo['naziv_slike']); ?>')">
                                                Izmeni
                                            </button>
                                            <a href="admin_vozila.php?action=delete&id=<?php echo $vozilo['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Da li ste sigurni da želite da obrišete ovo vozilo?')">
                                                Obriši
                                            </a>
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
    
    <!-- Modal za izmenu vozila -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Izmeni vozilo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="vozilo_id" id="edit_vozilo_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_marka" class="form-label">Marka *</label>
                                    <input type="text" class="form-control" id="edit_marka" name="marka" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_model" class="form-label">Model *</label>
                                    <input type="text" class="form-control" id="edit_model" name="model" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_godiste" class="form-label">Godište *</label>
                                    <input type="number" class="form-control" id="edit_godiste" name="godiste" min="1900" max="2030" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_cena" class="form-label">Cena (€) *</label>
                                    <input type="number" class="form-control" id="edit_cena" name="cena" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_naziv_slike" class="form-label">Naziv slike</label>
                                    <input type="text" class="form-control" id="edit_naziv_slike" name="naziv_slike">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_tip_motora" class="form-label">Tip motora</label>
                                    <select class="form-select" id="edit_tip_motora" name="tip_motora">
                                        <option value="">Izaberi tip</option>
                                        <option value="Benzin">Benzin</option>
                                        <option value="Dizel">Dizel</option>
                                        <option value="Hibrid">Hibrid</option>
                                        <option value="Električni">Električni</option>
                                        <option value="Gas">Gas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_kubikaza_motora" class="form-label">Kubikaža (cm³)</label>
                                    <input type="number" class="form-control" id="edit_kubikaza_motora" name="kubikaza_motora" min="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_snaga_motora" class="form-label">Snaga (KS)</label>
                                    <input type="number" class="form-control" id="edit_snaga_motora" name="snaga_motora" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_opis" class="form-label">Opis</label>
                            <textarea class="form-control" id="edit_opis" name="opis" rows="3" placeholder="Detaljan opis vozila..."></textarea>
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
        function editVozilo(id, marka, model, godiste, tip_motora, kubikaza_motora, snaga_motora, cena, opis, naziv_slike) {
            document.getElementById('edit_vozilo_id').value = id;
            document.getElementById('edit_marka').value = marka;
            document.getElementById('edit_model').value = model;
            document.getElementById('edit_godiste').value = godiste;
            document.getElementById('edit_tip_motora').value = tip_motora;
            document.getElementById('edit_kubikaza_motora').value = kubikaza_motora;
            document.getElementById('edit_snaga_motora').value = snaga_motora;
            document.getElementById('edit_cena').value = cena;
            document.getElementById('edit_opis').value = opis;
            document.getElementById('edit_naziv_slike').value = naziv_slike;
            
            var modal = new bootstrap.Modal(document.getElementById('editModal'));
            modal.show();
        }
    </script>
</body>
</html>
