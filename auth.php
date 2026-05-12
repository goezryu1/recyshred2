<?php
// ── If this is a POST request, handle the API ──────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    session_start();
    require_once "db.php";

    $body   = json_decode(file_get_contents("php://input"), true);
    $action = $body["action"] ?? "";

    // REGISTER
    if ($action === "register") {
        $email    = trim($body["email"] ?? "");
        $password = $body["password"] ?? "";
        $name     = trim($body["name"] ?? "");

        if (!$email || !$password) {
            echo json_encode(["ok" => false, "error" => "Email and password are required."]);
            exit;
        }
        if (strlen($password) < 6) {
            echo json_encode(["ok" => false, "error" => "Password must be at least 6 characters."]);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["ok" => false, "error" => "Invalid email address."]);
            exit;
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            echo json_encode(["ok" => false, "error" => "Email is already registered."]);
            exit;
        }
        $check->close();

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hash);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt->close();
            $_SESSION["user_id"] = $userId;
            $_SESSION["name"]    = $name;
            $_SESSION["email"]   = $email;
            _setActiveSession($conn, $userId);
            echo json_encode(["ok" => true, "email" => $email, "name" => $name]);
        } else {
            echo json_encode(["ok" => false, "error" => "Registration failed. Please try again."]);
        }
        exit;
    }

    // LOGIN
    if ($action === "login") {
        $email    = trim($body["email"] ?? "");
        $password = $body["password"] ?? "";

        if (!$email || !$password) {
            echo json_encode(["ok" => false, "error" => "Email and password are required."]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($userId, $name, $hash);

        if ($stmt->fetch() && password_verify($password, $hash)) {
            $stmt->close();
            $_SESSION["user_id"] = $userId;
            $_SESSION["name"]    = $name;
            $_SESSION["email"]   = $email;
            _setActiveSession($conn, $userId);
            echo json_encode(["ok" => true, "email" => $email, "name" => $name]);
        } else {
            echo json_encode(["ok" => false, "error" => "Incorrect email or password."]);
        }
        exit;
    }

    echo json_encode(["ok" => false, "error" => "Unknown action."]);
    exit;
}

// ── Helper ─────────────────────────────────────────────────────────────
function _setActiveSession($conn, $userId) {
    $conn->query("DELETE FROM active_session WHERE 1=1");
    $stmt = $conn->prepare("INSERT INTO active_session (user_id) VALUES (?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// ── If this is a GET request, show the HTML login/register page ────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RecyShred | Connect Account</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

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

  <div class="auth-shell">
    <div class="auth-frame">

      <div class="welcome-card auth-hero">
        <h2>Connect your account</h2>
        <p>Login or create an account to continue.</p>
      </div>

      <div class="card auth-card">
        <div class="auth-tabs">
          <button class="auth-tab active" id="tabLogin" type="button">Login</button>
          <button class="auth-tab" id="tabRegister" type="button">Register</button>
        </div>

        <!-- LOGIN -->
        <form id="loginForm" class="auth-form" autocomplete="on">
          <h3 class="auth-title">Welcome back</h3>

          <div class="auth-field">
            <label class="auth-label" for="loginEmail">Email</label>
            <input class="auth-input" type="email" id="loginEmail" placeholder="you@example.com" required />
          </div>

          <div class="auth-field">
            <label class="auth-label" for="loginPass">Password</label>
            <div class="auth-pass">
              <input class="auth-input" type="password" id="loginPass" placeholder="••••••••" required />
              <button class="auth-eye" type="button" aria-label="Toggle password" data-target="loginPass">Show</button>
            </div>
          </div>

          <button class="dashboard-btn auth-submit" type="submit">Login</button>
          <p id="loginMsg" class="auth-msg"></p>
        </form>

        <!-- REGISTER -->
        <form id="registerForm" class="auth-form" style="display:none;" autocomplete="on">
          <h3 class="auth-title">Create your account</h3>

          <div class="auth-field">
            <label class="auth-label" for="regName">Name</label>
            <input class="auth-input" type="text" id="regName" placeholder="Your name" required />
          </div>

          <div class="auth-field">
            <label class="auth-label" for="regEmail">Email</label>
            <input class="auth-input" type="email" id="regEmail" placeholder="you@example.com" required />
          </div>

          <div class="auth-field">
            <label class="auth-label" for="regPass">Password</label>
            <div class="auth-pass">
              <input class="auth-input" type="password" id="regPass" minlength="6" placeholder="At least 6 characters" required />
              <button class="auth-eye" type="button" aria-label="Toggle password" data-target="regPass">Show</button>
            </div>
            <small class="auth-hint">Use 6+ characters for a stronger password.</small>
          </div>

          <button class="dashboard-btn auth-submit" type="submit">Register</button>
          <p id="regMsg" class="auth-msg"></p>
        </form>

      </div>
    </div>
  </div>

  <script>
    // ===== Tabs =====
    const tabLogin     = document.getElementById("tabLogin");
    const tabRegister  = document.getElementById("tabRegister");
    const loginForm    = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    function setActiveTab(which) {
      const isLogin = which === "login";
      tabLogin.classList.toggle("active", isLogin);
      tabRegister.classList.toggle("active", !isLogin);
      loginForm.style.display    = isLogin ? "block" : "none";
      registerForm.style.display = isLogin ? "none"  : "block";
      document.getElementById("loginMsg").textContent = "";
      document.getElementById("regMsg").textContent   = "";
    }

    tabLogin.addEventListener("click",    () => setActiveTab("login"));
    tabRegister.addEventListener("click", () => setActiveTab("register"));

    // ===== Password toggles =====
    document.querySelectorAll(".auth-eye").forEach(btn => {
      btn.addEventListener("click", () => {
        const input = document.getElementById(btn.getAttribute("data-target"));
        const isPassword = input.type === "password";
        input.type      = isPassword ? "text" : "password";
        btn.textContent = isPassword ? "Hide" : "Show";
      });
    });

    // ===== Redirect after auth =====
    const params = new URLSearchParams(window.location.search);
    const next   = params.get("next") || "dashboard.php";

    function finishAuth(email, name) {
      localStorage.setItem("connectedAccount", "true");
      localStorage.setItem("connectedEmail",   email);
      localStorage.setItem("connectedName",    name || email);
      window.location.href = next;
    }

    // ===== Shared fetch helper =====
    async function callAuth(action, payload, msgEl, btnEl) {
      msgEl.classList.remove("error");
      msgEl.textContent = action === "login" ? "Logging in…" : "Creating account…";
      btnEl.disabled    = true;

      try {
        const res  = await fetch("auth.php", {
          method:  "POST",
          headers: { "Content-Type": "application/json" },
          body:    JSON.stringify({ action, ...payload }),
        });
        const data = await res.json();

        if (data.ok) {
          msgEl.textContent = action === "login" ? "Welcome back!" : "Account created!";
          setTimeout(() => finishAuth(data.email, data.name), 400);
        } else {
          msgEl.textContent = data.error || "Something went wrong.";
          msgEl.classList.add("error");
          btnEl.disabled = false;
        }
      } catch (err) {
        msgEl.textContent = "Could not reach the server. Check your connection.";
        msgEl.classList.add("error");
        btnEl.disabled = false;
      }
    }

    // ===== Login =====
    loginForm.addEventListener("submit", (e) => {
      e.preventDefault();
      callAuth(
        "login",
        {
          email:    document.getElementById("loginEmail").value.trim(),
          password: document.getElementById("loginPass").value,
        },
        document.getElementById("loginMsg"),
        loginForm.querySelector(".auth-submit")
      );
    });

    // ===== Register =====
    registerForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const pass = document.getElementById("regPass").value;
      const msg  = document.getElementById("regMsg");

      if (pass.length < 6) {
        msg.textContent = "Password must be at least 6 characters.";
        msg.classList.add("error");
        return;
      }

      callAuth(
        "register",
        {
          name:     document.getElementById("regName").value.trim(),
          email:    document.getElementById("regEmail").value.trim(),
          password: pass,
        },
        msg,
        registerForm.querySelector(".auth-submit")
      );
    });

    // ===== Theme toggle =====
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
  </script>
</body>
</html>