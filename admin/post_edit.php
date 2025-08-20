<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /blog/admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
    header('Location: /blog/admin/dashboard.php');
    exit;
}

$errors = [];
$title = $post['title'];
$content = $post['content'];
$author = $post['author'];
$category = $post['category'] ?? '';
$tags = $post['tags'] ?? '';
$currentCover = $post['cover_image'] ?? '';
$videoUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $removeCover = isset($_POST['remove_cover']);

    if ($title === '') $errors[] = 'Title is required';
    if ($content === '') $errors[] = 'Content is required';
    if ($author === '') $errors[] = 'Author is required';

    // Handle cover image remove/upload
    $newCoverPath = $currentCover; // default keep
    if ($removeCover && $currentCover) {
        $filePath = dirname(__DIR__) . '/' . $currentCover;
        if (is_file($filePath)) { @unlink($filePath); }
        $newCoverPath = null;
    }
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['cover_image'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!isset($allowed[$mime])) {
                $errors[] = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
            } elseif ($file['size'] > 20 * 1024 * 1024) {
                $errors[] = 'Image too large. Max 20MB.';
            } else {
                $ext = $allowed[$mime];
                $uploadDir = dirname(__DIR__) . '/uploads';
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }
                $name = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = $uploadDir . '/' . $name;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // remove old if existed and not already removed
                    if ($currentCover && (!$removeCover)) {
                        $old = dirname(__DIR__) . '/' . $currentCover;
                        if (is_file($old)) { @unlink($old); }
                    }
                    $newCoverPath = 'uploads/' . $name;
                } else {
                    $errors[] = 'Failed to save uploaded image.';
                }
            }
        } else {
            $errors[] = 'Upload error.';
        }
    }

    // Validate optional video URL if provided (support YouTube/Vimeo/MP4)
    if ($videoUrl !== '') {
        $isYoutube = (bool)preg_match('/https?:\\/\\/(?:www\\.)?youtube\\.com\\/watch\\?|https?:\\/\\/youtu\.be\\//i', $videoUrl);
        $isYoutube = $isYoutube || (bool)preg_match('/https?:\\/\\/(?:www\\.)?youtube\\.com\\/(?:embed|shorts)\\//i', $videoUrl);
        $isVimeo = (bool)preg_match('/https?:\\/\\/(?:www\\.)?vimeo\\.com\\//i', $videoUrl) || (bool)preg_match('/https?:\\/\\/player\\.vimeo\\.com\\/video\\//i', $videoUrl);
        $isMp4 = (bool)preg_match('/https?:\\/\\\/[^\s\"\']+?\.mp4(\?[^\s\"\']*)?$/i', $videoUrl);
        if (!($isYoutube || $isVimeo || $isMp4)) {
            $errors[] = 'Invalid video URL. Only YouTube, Vimeo, or direct .mp4 links are supported.';
        }
    }

    if (!$errors) {
        // If a video URL was provided and not already present, append to content so the frontend auto-embeds it
        if ($videoUrl !== '' && strpos($content, $videoUrl) === false) {
            $content = rtrim($content) . "\n\n" . $videoUrl;
        }
        $upd = $pdo->prepare('UPDATE posts SET title = ?, content = ?, author = ?, category = ?, tags = ?, cover_image = ? WHERE id = ?');
        $upd->execute([$title, $content, $author, $category !== '' ? $category : null, $tags !== '' ? $tags : null, $newCoverPath !== '' ? $newCoverPath : null, $id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post updated successfully.'];
        header('Location: /blog/admin/dashboard.php');
        exit;
    }
}
include __DIR__ . '/header.php';
?>
<h3>Edit Post</h3>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?><li><?php echo e($e); ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="<?php echo e($title); ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Author</label>
    <input type="text" name="author" class="form-control" value="<?php echo e($author); ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Category</label>
    <input type="text" name="category" class="form-control" value="<?php echo e($category); ?>" placeholder="e.g. News">
  </div>
  <div class="mb-3">
    <label class="form-label">Tags</label>
    <input type="text" name="tags" class="form-control" value="<?php echo e($tags); ?>" placeholder="Comma-separated, e.g. php, web, tips">
  </div>
  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control" rows="10" required><?php echo e($content); ?></textarea>
    <div class="form-text">Tip: You can paste a YouTube/Vimeo/MP4 link here or use the field below.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Video URL (optional)</label>
    <input type="url" name="video_url" class="form-control" value="<?php echo e($videoUrl); ?>" placeholder="https://youtu.be/VIDEOID or https://vimeo.com/VIDEOID or https://example.com/video.mp4">
    <div class="form-text">Only YouTube, Vimeo, or direct .mp4 links are supported.</div>
    <div id="videoPreview" class="mt-2"></div>
  </div>
  <div class="mb-3">
    <label class="form-label">Cover Image</label>
    <?php if ($currentCover): ?>
      <div class="mb-2">
        <img src="/blog/<?php echo e($currentCover); ?>" alt="Current cover" class="img-fluid rounded border" style="max-height:200px;object-fit:cover;">
      </div>
    <?php endif; ?>
    <input type="file" name="cover_image" class="form-control" accept="image/*">
    <div class="form-text">Max 20MB. JPG, PNG, GIF, WEBP.</div>
    <img id="coverPreview" class="img-preview mt-2 d-none" alt="Cover preview">
    <?php if ($currentCover): ?>
      <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="remove_cover" id="removeCover">
        <label class="form-check-label" for="removeCover">Remove current cover image</label>
      </div>
    <?php endif; ?>
  </div>
  <button class="btn btn-primary" type="submit">Save Changes</button>
  <a class="btn btn-secondary" href="/blog/admin/dashboard.php">Cancel</a>
</form>
<?php include __DIR__ . '/footer.php'; ?>
