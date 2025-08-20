<?php
http_response_code(404);
include __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-4 fw-bold">404</h1>
  <p class="lead text-muted mb-4">The page you’re looking for could not be found.</p>
  <a class="btn btn-primary" href="/blog/">Go to Homepage</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
