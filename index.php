<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<style>
  body::-webkit-scrollbar {
    display: none;
  }
</style>

<body>


  <?php
  $pageTitle = 'Find what matters';
  $activePage = 'home';
  require __DIR__ . '/includes/header.php';
  $totalItems = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status IN ('open','matched')")->fetchColumn();
  $totalFound = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type='found' AND status='open'")->fetchColumn();
  $totalReturned = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status='returned'")->fetchColumn();
  $latest = $pdo->query("SELECT i.*, u.name owner_name FROM items i JOIN users u ON u.id=i.user_id WHERE i.status='open' ORDER BY i.created_at DESC LIMIT 6")->fetchAll();
  ?>
  <section class="hero">
    <div class="hero-copy">
      <span class="eyebrow"><i></i> Lost & Found, rebuilt for campus life</span>
      <h1>Turn <span class="gradient-text">lost moments</span> into found ones.</h1>
      <p>CampusTrace gives students one trusted place to report lost belongings, post found items, discover matches, and safely coordinate returns.</p>
      <div class="hero-actions"><a class="btn btn-primary" href="<?= BASE_URL ?>/items/browse.php">Explore items →</a><a class="btn btn-secondary" href="<?= BASE_URL ?>/items/post.php">+ Report an item</a></div>
      <div class="hero-stats">
        <div class="stat"><strong><?= number_format($totalItems) ?></strong><span>active reports</span></div>
        <div class="stat"><strong><?= number_format($totalFound) ?></strong><span>found items</span></div>
        <div class="stat"><strong><?= number_format($totalReturned) ?></strong><span>returned</span></div>
      </div>
    </div>
    <div class="hero-visual"><canvas id="hero-canvas"></canvas>
      <div class="float-card float-a"><strong>✦ Smart matching</strong><small>Search by place, type & date</small></div>
      <div class="float-card float-b"><strong>✓ Verified flow</strong><small>Claim → review → return</small></div>
    </div>
  </section>
  <section class="section">
    <div class="section-head">
      <div><span class="eyebrow">Why CampusTrace</span>
        <h2>Simple for students. Serious about trust.</h2>
      </div>
      <p>Every interaction is designed around useful information, privacy-conscious handoffs, and a clean campus experience.</p>
    </div>
    <div class="cards">
      <article class="feature">
        <div class="feature-icon">⌕</div>
        <h3>Search faster</h3>
        <p>Filter reports by lost/found status, category, location and date instead of scrolling through a noisy feed.</p>
      </article>
      <article class="feature">
        <div class="feature-icon">◇</div>
        <h3>Prove ownership</h3>
        <p>Claimants can provide private identifying details so an item can be verified before it changes hands.</p>
      </article>
      <article class="feature">
        <div class="feature-icon">↗</div>
        <h3>Close the loop</h3>
        <p>Owners and finders can move an item through a clear open, matched and returned lifecycle.</p>
      </article>
    </div>
  </section>
  <section class="section" style="padding-top:10px">
    <div class="section-head">
      <div><span class="eyebrow">Latest reports</span>
        <h2>Fresh activity from campus</h2>
      </div><a class="btn btn-ghost" href="<?= BASE_URL ?>/items/browse.php">View all</a>
    </div>
    <div class="item-grid"><?php foreach ($latest as $item): ?><a class="item-card" href="<?= BASE_URL ?>/items/view.php?id=<?= (int)$item['id'] ?>">
          <div class="item-image"><img src="<?= BASE_URL ?>/<?= e(item_image($item['image_path'])) ?>" alt="<?= e($item['title']) ?>"><span style="position:absolute;top:12px;left:12px"><?= status_badge($item['type']) ?></span></div>
          <div class="item-body">
            <div class="item-meta"><span class="muted"><?= e($item['category']) ?></span><span class="muted"><?= e(time_ago($item['created_at'])) ?></span></div>
            <h3><?= e($item['title']) ?></h3>
            <p>📍 <?= e($item['location']) ?></p>
          </div>
        </a><?php endforeach; ?></div>
  </section>
  <section class="section" style="padding-top:10px">
    <div class="how-grid">
      <div class="step"><span class="step-num">01 / REPORT</span>
        <h3>Post the details</h3>
        <p class="muted">Add where, when and what makes the item identifiable.</p>
      </div>
      <div class="step"><span class="step-num">02 / DISCOVER</span>
        <h3>Search the feed</h3>
        <p class="muted">Use filters to narrow down possible matches.</p>
      </div>
      <div class="step"><span class="step-num">03 / CLAIM</span>
        <h3>Share proof</h3>
        <p class="muted">Explain details that only the true owner should know.</p>
      </div>
      <div class="step"><span class="step-num">04 / RETURN</span>
        <h3>Close the case</h3>
        <p class="muted">Approve the claim and mark the item returned.</p>
      </div>
    </div>
  </section>
  <section class="cta"><span class="eyebrow">Ready when you are</span>
    <h2>Make your next lost item easier to recover.</h2>
    <p>Create a free student account and keep campus belongings moving toward their owners.</p><a class="btn btn-primary" href="<?= BASE_URL ?>/auth/register.php">Create student account</a>
  </section>
  <?php require __DIR__ . '/includes/footer.php'; ?>

</body>

</html>