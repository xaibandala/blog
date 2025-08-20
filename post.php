<?php
require_once __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

// Previous and next post (optional)
$prev = $pdo->prepare('SELECT id, title FROM posts WHERE id < ? ORDER BY id DESC LIMIT 1');
$prev->execute([$id]);
$prevPost = $prev->fetch();

$next = $pdo->prepare('SELECT id, title FROM posts WHERE id > ? ORDER BY id ASC LIMIT 1');
$next->execute([$id]);
$nextPost = $next->fetch();

// Helper: extract first YouTube embed URL from a text blob
if (!function_exists('youtube_embed_url')) {
    function youtube_embed_url(string $text): ?string {
        // Match common YouTube URL forms
        $patterns = [
            // https://www.youtube.com/watch?v=VIDEOID or with params
            '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/watch\\?[^\\s]*v=([a-zA-Z0-9_-]{11})/i',
            // https://youtu.be/VIDEOID
            '/https?:\\/\\/youtu\.be\\/([a-zA-Z0-9_-]{11})/i',
            // https://www.youtube.com/embed/VIDEOID
            '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/embed\\/([a-zA-Z0-9_-]{11})/i',
            // Shorts: https://www.youtube.com/shorts/VIDEOID
            '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/shorts\\/([a-zA-Z0-9_-]{11})/i',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $text, $m)) {
                $id = $m[1];
                return 'https://www.youtube.com/embed/' . $id . '?rel=0&modestbranding=1';
            }
        }
        return null;
    }
}

// Helper: extract first Vimeo embed URL
if (!function_exists('vimeo_embed_url')) {
    function vimeo_embed_url(string $text): ?string {
        // Match vimeo.com/VIDEOID or player.vimeo.com/video/VIDEOID
        $patterns = [
            '/https?:\\/\\/(?:www\\.)?vimeo\\.com\\/(?:video\\/)?(\d{6,12})/i',
            '/https?:\\/\\/(?:player\\.)?vimeo\\.com\\/video\\/(\d{6,12})/i',
        ];
        foreach ($patterns as $re) {
            if (preg_match($re, $text, $m)) {
                $id = $m[1];
                return 'https://player.vimeo.com/video/' . $id;
            }
        }
        return null;
    }
}

// Helper: extract first direct MP4 URL
if (!function_exists('first_mp4_url')) {
    function first_mp4_url(string $text): ?string {
        if (preg_match('/https?:\\/\\\/[^\s\"\']+?\.mp4(\?[^\s\"\']*)?/i', $text, $m)) {
            return $m[0];
        }
        return null;
    }
}

include __DIR__ . '/includes/header.php';
?>
<article class="mx-auto" style="max-width: 800px;">
  <h1 class="display-5 mb-3"><?php echo e($post['title']); ?></h1>
  <div class="post-byline mb-4">
    By <?php echo e($post['author']); ?> · <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
  </div>
  <?php if (!empty($post['cover_image'])): ?>
    <img
      src="/blog/<?php echo e($post['cover_image']); ?>"
      alt="Cover image for <?php echo e($post['title']); ?>"
      class="img-fluid rounded mb-3"
      style="max-height:420px;object-fit:cover;width:100%;aspect-ratio: 16/9;"
      loading="eager"
      fetchpriority="high"
      decoding="async"
      referrerpolicy="no-referrer"
    >
  <?php endif; ?>
  <?php $contentText = $post['content'] ?? ''; $yt = youtube_embed_url($contentText); $vim = !$yt ? vimeo_embed_url($contentText) : null; $mp4 = (!$yt && !$vim) ? first_mp4_url($contentText) : null; if ($yt): ?>
    <div class="ratio ratio-16x9 mb-3">
      <iframe
        src="<?php echo e($yt); ?>"
        title="YouTube video player"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  <?php elseif ($vim): ?>
    <div class="ratio ratio-16x9 mb-3">
      <iframe
        src="<?php echo e($vim); ?>"
        title="Vimeo video player"
        allow="autoplay; fullscreen; picture-in-picture; clipboard-write"
        allowfullscreen
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  <?php elseif ($mp4): ?>
    <div class="mb-3">
      <video class="w-100 rounded" style="max-height:420px; object-fit:cover; width:100%; aspect-ratio: 16/9;" controls preload="metadata" playsinline>
        <source src="<?php echo e($mp4); ?>" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>
  <?php endif; ?>
  <?php if (!empty($post['category'])): ?>
    <span class="badge rounded-pill text-bg-info me-1 mb-2"><?php echo e($post['category']); ?></span>
  <?php endif; ?>
  <?php if (!empty($post['tags'])): ?>
    <div class="mb-3">
      <?php foreach (explode(',', $post['tags']) as $tg): $tg=trim($tg); if ($tg==='') continue; ?>
        <span class="badge text-bg-secondary me-1"><?php echo e($tg); ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="post-content">
    <?php
      $displayContent = $post['content'] ?? '';
      // Remove supported video URLs from content (already embedded above)
      $removePatterns = [
        // YouTube full watch URLs
        '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/watch\?[^\s<>"]*/i',
        // youtu.be short links with optional params
        '/https?:\\/\\/youtu\\.be\\/[a-zA-Z0-9_-]{11}[^\s<>"]*/i',
        // YouTube embed and shorts
        '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/embed\\/[a-zA-Z0-9_-]{11}[^\s<>"]*/i',
        '/https?:\\/\\/(?:www\\.)?youtube\\.com\\/shorts\\/[a-zA-Z0-9_-]{11}[^\s<>"]*/i',
        // Vimeo URLs
        '/https?:\\/\\/(?:www\\.)?vimeo\\.com\\/(?:video\\/)?\d{6,12}[^\s<>"]*/i',
        '/https?:\\/\\/(?:player\\.)?vimeo\\.com\\/video\\/\d{6,12}[^\s<>"]*/i',
        // Direct MP4 links
        '/https?:\\/\\/[^\s"\'<>\)]+?\\.mp4(\?[^\s"\'<>\)]*)?/i',
      ];
      $displayContent = preg_replace($removePatterns, '', $displayContent);
      // Collapse excessive blank lines after removal
      $displayContent = preg_replace('/\n{3,}/', "\n\n", $displayContent);
      echo nl2br(e(trim($displayContent)));
    ?>
  </div>

  <div class="d-flex justify-content-between mt-5">
    <div>
      <?php if ($prevPost): ?>
        <a class="btn btn-outline-secondary" href="post.php?id=<?php echo (int)$prevPost['id']; ?>">&laquo; <?php echo e($prevPost['title']); ?></a>
      <?php endif; ?>
    </div>
    <div>
      <?php if ($nextPost): ?>
        <a class="btn btn-outline-secondary" href="post.php?id=<?php echo (int)$nextPost['id']; ?>"><?php echo e($nextPost['title']); ?> &raquo;</a>
      <?php endif; ?>
    </div>
  </div>
</article>
<?php include __DIR__ . '/includes/footer.php'; ?>
