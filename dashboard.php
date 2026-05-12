<?php
// dashboard.php — User dashboard, reads real data from MySQL via PHP session.
// If not logged in, redirects to auth.html.

session_start();
require_once "db.php";

// ── Auth guard: redirect to login if no session ──
if (empty($_SESSION["user_id"])) {
    header("Location: auth.php?next=dashboard.php");
    exit;
}

$userId = $_SESSION["user_id"];

// ── Fetch user record ──
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($id, $name, $email, $createdAt);
$stmt->fetch();
$stmt->close();

// ── Fetch stats from transactions table ──
// Adjust column names below if your transactions table uses different names.
$stats = [
    "total_weight"  => 0,
    "total_points"  => 0,
    "total_payout"  => "0.00",
    "sessions"      => 0,
    "last_activity" => "—",
];

$tq = $conn->prepare("
    SELECT
        COUNT(*)                        AS sessions,
        COALESCE(SUM(weight_kg), 0)     AS total_weight,
        COALESCE(SUM(coins_issued), 0)  AS total_coins,
        COALESCE(SUM(payout), 0)        AS total_payout,
        MAX(created_at)                 AS last_activity
    FROM transactions
    WHERE user_id = ?
");

if ($tq) {
    $tq->bind_param("i", $userId);
    $tq->execute();
    $tq->bind_result($sessions, $totalWeight, $totalCoins, $totalPayout, $lastActivity);
    if ($tq->fetch()) {
        $stats["sessions"]      = (int)$sessions;
        $stats["total_weight"]  = round((float)$totalWeight, 2);
        $stats["total_points"]  = (int)$totalCoins;
        $stats["total_payout"]  = number_format((float)$totalPayout, 2);
        $stats["last_activity"] = $lastActivity
            ? date("M d, Y", strtotime($lastActivity))
            : "—";
    }
    $tq->close();
}

$avg  = $stats["sessions"] > 0
    ? round($stats["total_points"] / $stats["sessions"])
    : 0;
$peso = $stats["total_payout"]; // from payout column, already formatted
$pct  = min(100, round(($stats["total_weight"] / 10) * 100));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>RecyShred | Dashboard</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<div id="site-content">

  <header>
    <img src="logo.png" class="site-logo" alt="RecyShred Logo" />
    <div class="brand-text">
      <h1>RecyShred</h1>
      <p>Turn Waste into Worth</p>
    </div>

    <button class="theme-btn" id="themeToggle" aria-label="Toggle theme" type="button">
      <svg class="sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
      </svg>
      <svg class="moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    </button>

    <nav>
      <a href="index.html">Home</a>
      <a href="dashboard.php" class="active">Dashboard</a>
      <a href="founders.html">Founders</a>
    </nav>
  </header>

  <div class="dashboard-container">

    <!-- LOGGED IN: user info card -->
    <div class="welcome-card" id="user-card">
      <h2 id="user-name">Welcome, <?= htmlspecialchars($name ?: $email) ?></h2>
      <p id="user-balance">
        Current Balance:
        <?= $stats["total_points"] ?> coins
        (₱<?= $peso ?> payout)
      </p>
      <p style="margin-top:6px; font-size:0.85rem; opacity:0.6;">
        <?= htmlspecialchars($email) ?> &nbsp;·&nbsp;
        Member since <?= date("M Y", strtotime($createdAt)) ?>
      </p>
      <div style="margin-top:14px;">
        <a class="dashboard-btn" href="logout.php">Log out</a>
      </div>
    </div>

    <!-- Stats row (reuses your existing CSS class names) -->
    <div class="stats-grid" style="margin-top:20px;">
      <div class="stat-card">
        <span class="stat-label">Total Weight</span>
        <span class="stat-value" id="stat-weight"><?= $stats["total_weight"] ?> kg</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Last Activity</span>
        <span class="stat-value" id="stat-last"><?= htmlspecialchars($stats["last_activity"]) ?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Sessions</span>
        <span class="stat-value" id="stat-sessions"><?= $stats["sessions"] ?> Sessions</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Avg per Session</span>
        <span class="stat-value" id="stat-avg"><?= $avg ?> RSC</span>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="progress-wrap" style="margin-top:20px;">
      <div class="progress-bar">
        <div class="progress-fill" id="progress-fill" style="width:<?= $pct ?>%;"></div>
      </div>
      <p class="progress-text" id="progress-text">
        <?= $pct ?>% towards daily target
      </p>
    </div>

  </div>
</div>

<script>
  // ── Theme toggle ──
  const themeBtn = document.getElementById("themeToggle");
  function applyTheme(theme) {
    const isDark = theme === "dark";
    document.body.classList.toggle("dark-mode", isDark);
    localStorage.setItem("theme", isDark ? "dark" : "light");
    const sun  = themeBtn?.querySelector(".sun-icon");
    const moon = themeBtn?.querySelector(".moon-icon");
    if (sun)  sun.style.display  = isDark ? "none"  : "block";
    if (moon) moon.style.display = isDark ? "block" : "none";
  }
  (function initTheme() {
    applyTheme(localStorage.getItem("theme") === "dark" ? "dark" : "light");
  })();
  themeBtn?.addEventListener("click", () => {
    applyTheme(document.body.classList.contains("dark-mode") ? "light" : "dark");
  });

  // ── Header shrink ──
  (function () {
    const header = document.querySelector("#site-content header");
    if (!header) return;
    const setHeaderHeight = () => {
      document.documentElement.style.setProperty("--header-h", header.offsetHeight + "px");
    };
    const update = () => {
      const max = 220;
      const y = Math.max(0, Math.min(max, window.scrollY || 0));
      const t = y / max;
      document.documentElement.style.setProperty("--shrink", t.toFixed(3));
      document.body.classList.toggle("progressive-shrink", t > 0.65);
      requestAnimationFrame(setHeaderHeight);
    };
    window.addEventListener("load",   () => { setHeaderHeight(); update(); });
    window.addEventListener("resize", () => { setHeaderHeight(); update(); });
    window.addEventListener("scroll", update, { passive: true });
  })();
</script>

</body>
</html>