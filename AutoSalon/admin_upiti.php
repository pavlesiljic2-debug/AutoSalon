<?php
session_start();
require_once 'db.php';

// Proverava da li je korisnik prijavljen i da li je moderator ili admin (uloga 2 ili 3)
if (!isset($_SESSION['user_id']) || ($_SESSION['uloga'] != 2 && $_SESSION['uloga'] != 3)) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Odgovaranje na upit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'reply') {
    $upit_id = (int)$_POST['upit_id'];
    $odgovor = trim($_POST['odgovor']);
    $status = trim($_POST['status']);
    
    if (empty($odgovor)) {
        $error = 'Odgovor je obavezan!';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE upit SET odgovor = ?, status = ? WHERE id = ?");
            $stmt->execute([$odgovor, $status, $upit_id]);
            $success = 'Odgovor je uspešno poslat!';
        } catch (PDOException $e) {
            $error = 'Greška pri slanju odgovora: ' . $e->getMessage();
        }
    }
}

// Učitava sve upite
try {
    $stmt = $pdo->prepare("
        SELECT u.*, v.marka, v.model, v.godiste, v.cena 
        FROM upit u 
        LEFT JOIN vozilo v ON u.vozilo_fk = v.id 
        ORDER BY u.id DESC
    ");
    $stmt->execute();
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
    <title>Admin Panel - Upravljanje upitima - Auto Salon</title>
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
                <h2>Admin Panel - Upravljanje upitima</h2>
                <p class="lead">Ovde možete upravljati svim upitima i odgovarati na njih.</p>
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
            <?php foreach ($upiti as $upit): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><?php echo htmlspecialchars($upit['naziv']); ?></h5>
                        <div>
                            <span class="badge bg-secondary">#<?php echo $upit['id']; ?></span>
                            <?php if ($upit['status']): ?>
                                <span class="badge bg-success"><?php echo htmlspecialchars($upit['status']); ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning">Na čekanju</span>
                            <?php endif; ?>
                        </div>
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
                            <strong>Email:</strong><br>
                            <?php echo htmlspecialchars($upit['email']); ?>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Pitanje:</strong><br>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($upit['pitanje'])); ?></p>
                        </div>
                        
                        <?php if ($upit['odgovor']): ?>
                        <div class="mb-3">
                            <strong>Vaš odgovor:</strong><br>
                            <div class="alert alert-info">
                                <?php echo nl2br(htmlspecialchars($upit['odgovor'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <?php if ($upit['vozilo_fk']): ?>
                            <a href="vozilo_detalji.php?id=<?php echo $upit['vozilo_fk']; ?>" 
                               class="btn btn-outline-primary btn-sm">Pogledaj vozilo</a>
                            <?php endif; ?>
                            
                            <?php if (!$upit['odgovor']): ?>
                            <button class="btn btn-primary btn-sm" 
                                    onclick="replyToInquiry(<?php echo $upit['id']; ?>, '<?php echo htmlspecialchars($upit['naziv']); ?>')">
                                Odgovori
                            </button>
                            <?php else: ?>
                            <button class="btn btn-warning btn-sm" 
                                    onclick="replyToInquiry(<?php echo $upit['id']; ?>, '<?php echo htmlspecialchars($upit['naziv']); ?>', '<?php echo htmlspecialchars($upit['odgovor']); ?>', '<?php echo htmlspecialchars($upit['status']); ?>')">
                                Izmeni odgovor
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Modal za odgovaranje -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Odgovori na upit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="upit_id" id="reply_upit_id">
                        
                        <div class="mb-3">
                            <label for="reply_odgovor" class="form-label">Odgovor *</label>
                            <textarea class="form-control" id="reply_odgovor" name="odgovor" rows="5" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reply_status" class="form-label">Status</label>
                            <select class="form-select" id="reply_status" name="status">
                                <option value="Na čekanju">Na čekanju</option>
                                <option value="U obradi">U obradi</option>
                                <option value="Odgovoreno">Odgovoreno</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                        <button type="submit" class="btn btn-primary">Pošalji odgovor</button>
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
        function replyToInquiry(upitId, naziv, existingReply = '', existingStatus = '') {
            document.getElementById('reply_upit_id').value = upitId;
            document.getElementById('reply_odgovor').value = existingReply;
            document.getElementById('reply_status').value = existingStatus || 'Odgovoreno';
            
            var modal = new bootstrap.Modal(document.getElementById('replyModal'));
            modal.show();
        }
    </script>
</body>
</html>
