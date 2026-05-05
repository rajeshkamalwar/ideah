<?php
// paytabs-return.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Status - Your Store</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      padding-top: 80px;
    }

    .card {
      max-width: 500px;
      margin: auto;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="card p-5 text-center">
      <?php
      $tran_ref = $_GET['tran_ref'] ?? $_POST['tran_ref'] ?? 'N/A';
      $status   = $_GET['resp_status'] ?? $_POST['resp_status'] ?? '';

      if ($status === 'A') {
        echo '<h2 class="text-success mb-4">✅ Payment Successful!</h2>';
        echo '<p class="lead">Thank you! Your payment has been received.</p>';
        echo '<p><strong>Transaction ID:</strong> ' . htmlspecialchars($tran_ref) . '</p>';
        echo '<a href="/" class="btn btn-success btn-lg mt-3">Back to Home</a>';
      } else {
        echo '<h2 class="text-danger mb-4">❌ Payment Failed or Cancelled</h2>';
        echo '<p>We could not process your payment. Please try again.</p>';
        echo '<p><strong>Reason:</strong> ' . htmlspecialchars($_GET['resp_message'] ?? 'Unknown') . '</p>';
        echo '<a href="/" class="btn btn-outline-danger btn-lg mt-3">Try Again</a>';
      }
      ?>
    </div>
  </div>
</body>
</html>
