/* =====================================================
   USER DATA & CONSTANTS
===================================================== */

let userCoins = 120;
let totalWeight = 12; // kg

const COINS_PER_KG = 5;
const PESO_VALUE = 1; // 1 coin = ₱1


/* =====================================================
   DASHBOARD UPDATE
===================================================== */

function updateDashboard() {
  const coinsEl = document.getElementById("coins");
  const pesoEl = document.getElementById("peso");
  const weightEl = document.getElementById("weight");

  if (!coinsEl || !pesoEl || !weightEl) return;

  coinsEl.innerText = userCoins.toFixed(0);
  pesoEl.innerText = (userCoins * PESO_VALUE).toFixed(2);
  weightEl.innerText = totalWeight.toFixed(1);
}

updateDashboard();


/* =====================================================
   RECYCLING SIMULATION
===================================================== */

function addRecycle() {
  const input = document.getElementById("newWeight");
  if (!input) return;

  const weightInput = parseFloat(input.value);

  if (isNaN(weightInput) || weightInput <= 0) {
    alert("Enter a valid weight.");
    return;
  }

  const earnedCoins = weightInput * COINS_PER_KG;

  userCoins += earnedCoins;
  totalWeight += weightInput;

  updateDashboard();
  input.value = "";

  alert(`You earned ${earnedCoins} coins!`);
}


/* =====================================================
   REWARDS PAGE LOGIC
===================================================== */

document.querySelectorAll(".reward-card").forEach(card => {
  const cost = Number(card.dataset.cost);
  const progress = card.querySelector(".progress-fill");
  const button = card.querySelector(".redeem-btn");

  if (!progress || !button) return;

  const percent = Math.min((userCoins / cost) * 100, 100);
  progress.style.width = percent + "%";

  if (userCoins >= cost) {
    button.disabled = false;
    button.style.opacity = 1;
  } else {
    button.disabled = true;
    button.style.opacity = 0.5;
  }

  button.addEventListener("click", () => {
    if (userCoins < cost) return;

    userCoins -= cost;
    alert("Reward redeemed successfully!");
    location.reload();
  });
});


/* =====================================================
   THEME TOGGLE (DARK / LIGHT)
===================================================== */

const toggleBtn = document.getElementById("theme-toggle");

if (toggleBtn) {
  toggleBtn.addEventListener("click", () => {
    document.body.classList.toggle("dark-mode");

    if (document.body.classList.contains("dark-mode")) {
      toggleBtn.textContent = "☀️ Light Mode";
      localStorage.setItem("theme", "dark");
    } else {
      toggleBtn.textContent = "🌙 Dark Mode";
      localStorage.setItem("theme", "light");
    }
  });
}

/* Load saved theme */
if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-mode");
  if (toggleBtn) toggleBtn.textContent = "☀️ Light Mode";
}

/* =====================================================
   HOVER GIF – DEAD ZONE CONTROL (FINAL FIX)
===================================================== */

document.querySelectorAll('.hover-gif').forEach(card => {
  const gif = card.querySelector('.card-gif');

  card.addEventListener('mousemove', e => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left - rect.width / 2;
    const y = e.clientY - rect.top - rect.height / 2;

    gif.style.transform = `translate(${x * 0.04}px, ${y * 0.04}px)`;
  });

  card.addEventListener('mouseleave', () => {
    gif.style.transform = 'translate(0, 0)';
  });
});
