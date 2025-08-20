<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_login();

// Fetch posts
$stmt = $pdo->query('SELECT id, title, author, category, created_at FROM posts ORDER BY created_at DESC');
$posts = $stmt->fetchAll();
$count = count($posts);

include __DIR__ . '/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-1 text-white">Dashboard</h3>
    <div class="text-muted-2 small">Manage your posts and content</div>
  </div>
  <div class="d-flex gap-2">
    <span class="badge rounded-pill text-bg-primary align-self-center">Posts: <?php echo (int)$count; ?></span>
    <a class="btn btn-primary" href="/blog/admin/post_new.php">+ New Post</a>
  </div>
</div>
<div class="row g-2 mb-3">
  <div class="col-sm-8 col-md-6 col-lg-4">
    <input id="adminSearch" type="search" class="form-control" placeholder="Search posts (title, author, category)">
  </div>
  <div class="col-sm-4 col-md-3 col-lg-2">
    <span class="badge text-bg-secondary align-self-center" id="adminSearchCount">0</span>
  </div>
  <div class="col-12 d-none" id="adminNoResults">
    <div class="alert alert-info py-2 mb-0">No matching posts.</div>
  </div>
  
</div>
<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-dark table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Created</th>
            <th style="width:160px;">Actions</th>
          </tr>
        </thead>
        <tbody id="adminTableBody">
          <?php foreach ($posts as $p): ?>
            <tr class="admin-post-row">
              <td><?php echo (int)$p['id']; ?></td>
              <td><a href="/blog/post.php?id=<?php echo (int)$p['id']; ?>" target="_blank"><?php echo e($p['title']); ?></a></td>
              <td><?php echo e($p['author']); ?></td>
              <td><?php echo e($p['category'] ?? ''); ?></td>
              <td><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="/blog/admin/post_edit.php?id=<?php echo (int)$p['id']; ?>">Edit</a>
                <form class="d-inline js-delete-form" method="post" action="/blog/admin/post_delete.php">
                  <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                  <button class="btn btn-sm btn-danger js-delete-btn" type="button" data-post-title="<?php echo e($p['title']); ?>">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$posts): ?>
            <tr><td colspan="6" class="text-center text-muted">No posts yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- Delete confirmation modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmLabel">Delete post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete: <strong id="deletePostTitle"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
