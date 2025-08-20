<?php
require_once __DIR__ . '/config.php';

// Pagination
$perPage = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$total = (int)$pdo->query('SELECT COUNT(*) FROM posts')->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
if ($page > $pages) { $page = $pages; }
$offset = ($page - 1) * $perPage;

// Fetch posts for current page
$stmt = $pdo->prepare('SELECT id, title, content, author, category, tags, cover_image, created_at FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Helper: excerpt
function excerpt($text, $len = 180) {
    $plain = trim(strip_tags($text));
    if (mb_strlen($plain) <= $len) return $plain;
    return mb_substr($plain, 0, $len - 1) . '…';
}

include __DIR__ . '/includes/header.php';
?>
<section class="hero p-4 p-md-5 mb-4 border rounded-3">
  <div class="container-fluid py-2">
    <h1 class="display-5 fw-semibold mb-2 text-white">Welcome to Simple Blog</h1>
    <p class="lead text-white-50 mb-3">Read the latest posts from our authors.</p>
    <div class="row g-2 align-items-center">
      <div class="col-12 col-lg-8">
        <div class="input-group input-group-lg">
          <span class="input-group-text bg-transparent text-white-50 border-secondary">🔎</span>
          <input id="searchInput" type="search" class="form-control form-control-lg bg-transparent text-white border-secondary" placeholder="Search posts by title, author or text..." autocomplete="off">
        </div>
      </div>
      <div class="col-auto">
        <span id="searchCount" class="badge rounded-pill text-bg-secondary"><?php echo (int)count($posts); ?></span>
      </div>
      <div class="col-auto">
        <span class="badge rounded-pill text-bg-primary">Total: <?php echo (int)$total; ?></span>
      </div>
    </div>
  </div>
  
</section>
<div id="noResultsAlert" class="alert alert-warning d-none">No posts match your search.</div>

<div class="row g-4">
  <?php if ($posts): ?>
    <?php foreach ($posts as $p): ?>
      <div class="col-md-6 col-lg-4 post-item">
        <div class="card h-100 shadow-sm position-relative">
          <?php if (!empty($p['cover_image'])): ?>
            <img
              src="/blog/<?php echo e($p['cover_image']); ?>"
              class="card-img-top"
              alt="Cover image for <?php echo e($p['title']); ?>"
              loading="lazy"
              decoding="async"
              referrerpolicy="no-referrer"
              style="aspect-ratio: 16/9; object-fit: cover;"
            >
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-2">
              <a href="/blog/post.php?id=<?php echo (int)$p['id']; ?>" class="text-decoration-none stretched-link"><?php echo e($p['title']); ?></a>
            </h5>
            <div class="text-muted small mb-3">
              By <?php echo e($p['author']); ?> · <?php echo date('M d, Y', strtotime($p['created_at'])); ?>
            </div>
            <?php if (!empty($p['category'])): ?>
              <span class="badge rounded-pill text-bg-info me-1 mb-2"><?php echo e($p['category']); ?></span>
            <?php endif; ?>
            <?php if (!empty($p['tags'])): ?>
              <div class="mb-2">
                <?php foreach (explode(',', $p['tags']) as $tg): $tg=trim($tg); if ($tg==='') continue; ?>
                  <span class="badge text-bg-secondary me-1"><?php echo e($tg); ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <p class="card-text post-card-excerpt mb-4"><?php echo e(excerpt($p['content'])); ?></p>
            <div class="mt-auto">
              <a class="btn btn-primary" href="/blog/post.php?id=<?php echo (int)$p['id']; ?>">Read More</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-12">
      <div class="alert alert-info mb-0">No posts yet. <a href="/blog/admin/login.php" class="alert-link">Add one from the Admin</a>.</div>
    </div>
  <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
  <ul class="pagination justify-content-center">
    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
      <a class="page-link" href="?page=<?php echo max(1, $page-1); ?>" aria-label="Previous">
        <span aria-hidden="true">&laquo;</span>
      </a>
    </li>
    <?php for ($i=1; $i<=$pages; $i++): ?>
      <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
    <?php endfor; ?>
    <li class="page-item <?php echo $page >= $pages ? 'disabled' : ''; ?>">
      <a class="page-link" href="?page=<?php echo min($pages, $page+1); ?>" aria-label="Next">
        <span aria-hidden="true">&raquo;</span>
      </a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>