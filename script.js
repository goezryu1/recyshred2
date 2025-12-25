// Initial user data
let userCoins = 120;
let totalWeight = 12; // kg
const COINS_PER_KG = 5;
const PESO_VALUE = 1; // 1 coin = ₱1

// Update dashboard stats
function updateDashboard() {
  document.getElementById("coins").innerText = userCoins.toFixed(0);
  document.getElementById("peso").innerText = (userCoins * PESO_VALUE).toFixed(2);
  document.getElementById("weight").innerText = totalWeight.toFixed(1);
}

updateDashboard();

// Add recycling simulation
function addRecycle() {
  const weightInput = parseFloat(document.getElementById("newWeight").value);
  if (isNaN(weightInput) || weightInput <= 0) {
    alert("Enter a valid weight.");
    return;
  }

  const earnedCoins = weightInput * COINS_PER_KG;
  userCoins += earnedCoins;
  totalWeight += weightInput;

  updateDashboard();
  document.getElementById("newWeight").value = "";
  alert(`You earned ${earnedCoins} coins!`);
}

// REWARDS PAGE LOGIC
if (document.querySelectorAll(".reward-card").length) {
  document.querySelectorAll(".reward-card").forEach(card => {
    const cost = Number(card.dataset.cost);
    const progress = card.querySelector(".progress-fill");
    const button = card.querySelector(".redeem-btn");

    const percent = Math.min((userCoins / cost) * 100, 100);
    progress.style.width = percent + "%";

    if (userCoins >= cost) {
      button.disabled = false;
    } else {
      button.disabled = true;
      button.style.opacity = 0.5;
    }

    button.addEventListener("click", () => {
      if (userCoins >= cost) {
        userCoins -= cost;
        alert("Reward redeemed successfully!");
        location.reload();
      }
    });
  });
}
  const toggleBtn = document.getElementById("theme-toggle");

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

  // Load saved theme
  if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
    toggleBtn.textContent = "☀️ Light Mode";
  }
