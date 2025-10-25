<?php
// submit_review.php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['client_id'])) {
  header("Location: login.php");
  exit;
}

$clientId = $_SESSION['client_id'];
$rating = intval($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$serviceId = !empty($_POST['service_id']) ? intval($_POST['service_id']) : null;
$reviewId = intval($_POST['review_id'] ?? 0);

if ($rating >= 1 && $rating <= 5) {
  try {
    if ($reviewId) {
      $stmt = $pdo->prepare("UPDATE reviews SET rating=?, comment=?, service_id=? WHERE id=? AND client_id=?");
      $stmt->execute([$rating, $comment, $serviceId, $reviewId, $clientId]);
    } else {
      $stmt = $pdo->prepare("INSERT INTO reviews (client_id, rating, comment, service_id) VALUES (?,?,?,?)");
      $stmt->execute([$clientId, $rating, $comment, $serviceId]);
    }
    header("Location: index.php?msg=review_saved");
    exit;
  } catch (Exception $e) {
    echo "Error saving review: " . htmlspecialchars($e->getMessage());
  }
} else {
  echo "Invalid rating value.";
}
