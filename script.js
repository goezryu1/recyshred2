let userCoins = 120;
let totalWeight = 12; // kg

const COINS_PER_KG = 5;
const PESO_VALUE = 1; // 1 coin = ₱1

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

import QRCode from 'qrcode';

const userQR = document.getElementById('userQR');
QRCode.toCanvas(userQR, 'https://yourwebsite.com', function (error) {
  if (error) console.error(error);
  console.log('User QR code generated!');
});

const adminQR = document.getElementById('adminQR');
const adminUrl = 'https://yourwebsite.com/admin?token=YOUR_SECURE_TOKEN';
QRCode.toCanvas(adminQR, adminUrl, function (error) {
  if (error) console.error(error);
  console.log('Admin QR code generated!');
});

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

if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-mode");
  if (toggleBtn) toggleBtn.textContent = "☀️ Light Mode";
}

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
