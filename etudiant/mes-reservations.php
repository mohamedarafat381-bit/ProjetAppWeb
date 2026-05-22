<?php
require_once '../includes/functions.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Récupération des réservations
$stmt = $db->prepare("SELECT r.*, c.numero_chambre, c.type_chambre, c.prix_mensuel, 
                      ct.nom as cite_nom, ct.ville, ct.adresse 
                      FROM reservations r 
                      JOIN chambres c ON r.chambre_id = c.id 
                      JOIN cites ct ON c.cite_id = ct.id 
                      WHERE r.utilisateur_id = ? 
                      ORDER BY r.date_reservation DESC");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">
                <i class="bi bi-calendar"></i> Mes Réservations
            </h2>
        </div>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Vous n'avez pas encore de réservation.
            <a href="../chambres/" class="alert-link">Chercher une chambre</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($reservations as $reservation): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-<?php 
                            echo $reservation['statut'] == 'confirmee' ? 'success' : 
                                ($reservation['statut'] == 'en_attente' ? 'warning' : 
                                ($reservation['statut'] == 'annulee' ? 'danger' : 'secondary')); 
                        ?> text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Réservation #<?php echo $reservation['id']; ?></h5>
                                <span class="badge bg-light text-dark">
                                    <?php echo ucfirst($reservation['statut']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Informations de la chambre</h6>
                                    <p class="mb-1">
                                        <strong>Chambre :</strong> <?php echo htmlspecialchars($reservation['numero_chambre']); ?><br>
                                        <strong>Type :</strong> <?php echo ucfirst($reservation['type_chambre']); ?><br>
                                        <strong>Cité :</strong> <?php echo htmlspecialchars($reservation['cite_nom']); ?><br>
                                        <strong>Adresse :</strong> <?php echo htmlspecialchars($reservation['adresse']); ?><br>
                                        <strong>Ville :</strong> <?php echo htmlspecialchars($reservation['ville']); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Détails de la réservation</h6>
                                    <p class="mb-1">
                                        <strong>Date d'arrivée :</strong> <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?><br>
                                        <strong>Date de départ :</strong> <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?><br>
                                        <strong>Prix mensuel :</strong> <?php echo number_format($reservation['prix_mensuel'], 2); ?> FCFA<br>
                                        <strong>Montant total :</strong> <span class="fw-bold"><?php echo number_format($reservation['montant_total'], 2); ?> FCFA</span><br>
                                        <strong>Réservé le :</strong> <?php echo date('d/m/Y', strtotime($reservation['date_reservation'])); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <div>
                                    <?php if ($reservation['statut'] == 'en_attente'): ?>
                                        <span class="text-warning">
                                            <i class="bi bi-clock"></i> En attente de confirmation
                                        </span>
                                    <?php elseif ($reservation['statut'] == 'confirmee'): ?>
                                        <span class="text-success">
                                            <i class="bi bi-check-circle"></i> Réservation confirmée
                                        </span>
                                    <?php elseif ($reservation['statut'] == 'annulee'): ?>
                                        <span class="text-danger">
                                            <i class="bi bi-x-circle"></i> Réservation annulée
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="btn-group">
                                    <?php if ($reservation['statut'] == 'en_attente' || $reservation['statut'] == 'confirmee'): ?>
                                        <?php if (strtotime($reservation['date_debut']) > time()): ?>
                                            <a href="modifier-reservation.php?id=<?php echo $reservation['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <a href="annuler-reservation.php?id=<?php echo $reservation['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                                <i class="bi bi-x-circle"></i> Annuler
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php if ($reservation['statut'] == 'terminee'): ?>
                                        <a href="evaluations.php?chambre_id=<?php echo $reservation['chambre_id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-star"></i> Évaluer
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="../chambres/details.php?id=<?php echo $reservation['chambre_id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> Voir la chambre
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>