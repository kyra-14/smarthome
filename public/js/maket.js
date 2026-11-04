/**
 * Smart Maket IoT - Dual Layout Engine (Versi Desktop & Versi Mobile PWA)
 * Mendukung switch dinamis tampilan Desktop (Layar Lebar) vs Mobile (HP).
 */

// 1. Audio Click Synthesizer (Web Audio API)
class SwitchAudio {
  constructor() {
    this.ctx = null;
  }

  init() {
    if (!this.ctx) {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      this.ctx = new AudioCtx();
    }
  }

  playToggle(state) {
    try {
      this.init();
      if (this.ctx.state === 'suspended') this.ctx.resume();
      const osc = this.ctx.createOscillator();
      const gain = this.ctx.createGain();
      osc.type = 'sine';

      if (state) {
        osc.frequency.setValueAtTime(600, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(900, this.ctx.currentTime + 0.05);
      } else {
        osc.frequency.setValueAtTime(800, this.ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(350, this.ctx.currentTime + 0.05);
      }

      gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.05);
      osc.connect(gain);
      gain.connect(this.ctx.destination);
      osc.start();
      osc.stop(this.ctx.currentTime + 0.06);
    } catch (e) {}
  }
}

const audio = new SwitchAudio();

// 2. Haptic Vibration Helper
function triggerHaptic(duration = 20) {
  if ('vibrate' in navigator) {
    try {
      navigator.vibrate(duration);
    } catch (e) {}
  }
}

// 3. CSRF Token Helper
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

// 4. Toast Notification
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = 'toast';
  let icon = 'fa-circle-info';
  if (type === 'success') icon = 'fa-circle-check';
  if (type === 'warning') icon = 'fa-triangle-exclamation';

  toast.innerHTML = `
    <i class="fa-solid ${icon}" style="color: ${type === 'success' ? '#fbbf24' : (type === 'warning' ? '#f43f5e' : '#38bdf8')}"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(8px)';
    toast.style.transition = 'all 0.25s ease';
    setTimeout(() => toast.remove(), 250);
  }, 2400);
}

// 5. Dual Model Mode Switcher (Desktop vs Mobile HP)
function setViewMode(mode) {
  triggerHaptic(15);
  const container = document.getElementById('app-container');
  const btnDesktop = document.getElementById('btn-mode-desktop');
  const btnMobile = document.getElementById('btn-mode-mobile');

  if (mode === 'mobile') {
    if (container) container.classList.add('force-mobile');
    if (btnMobile) btnMobile.classList.add('active');
    if (btnDesktop) btnDesktop.classList.remove('active');
    localStorage.setItem('maket_view_mode', 'mobile');
    showToast('Beralih ke Versi HP (Mobile)', 'info');
  } else {
    if (container) container.classList.remove('force-mobile');
    if (btnDesktop) btnDesktop.classList.add('active');
    if (btnMobile) btnMobile.classList.remove('active');
    localStorage.setItem('maket_view_mode', 'desktop');
    showToast('Beralih ke Versi Laptop (Desktop)', 'info');
  }
}

function initViewMode() {
  const savedMode = localStorage.getItem('maket_view_mode');
  const isSmallScreen = window.innerWidth < 992;

  if (savedMode === 'mobile' || (isSmallScreen && !savedMode)) {
    setViewMode('mobile');
  } else {
    setViewMode('desktop');
  }
}

// 6. Toggle Status Lampu Tertentu
async function toggleLampu(deviceId) {
  triggerHaptic(25);

  const card = document.getElementById(`device-card-${deviceId}`);
  const chk = document.getElementById(`toggle-chk-${deviceId}`);
  const nextState = chk ? !chk.checked : true;
  audio.playToggle(nextState);

  // Optimistic UI update
  if (chk) chk.checked = nextState;
  if (card) {
    card.classList.toggle('is-active', nextState);
    const lbl = card.querySelector('.switch-label');
    if (lbl) lbl.textContent = nextState ? 'MENYALA' : 'MATI';
  }

  try {
    const res = await fetch(`/api/lampu/${deviceId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });

    const data = await res.json();
    if (data.success) {
      syncMaketFloorplan(data.status);
      showToast(`${data.device.name} ${data.device.is_on ? 'Dinyalakan' : 'Dimatikan'}`, 'success');
      if (data.log) addLogEntryUI(data.log);
    }
  } catch (err) {
    console.error('Toggle error:', err);
    // revert jika gagal
    if (chk) chk.checked = !nextState;
    if (card) card.classList.toggle('is-active', !nextState);
    showToast('Gagal mengubah saklar', 'warning');
  }
}

// 7. Master Action: Nyalakan Semua / Matikan Semua Lampu
async function toggleAllLampu(action) {
  triggerHaptic(30);
  const isTurnOn = (action === 'on');
  audio.playToggle(isTurnOn);

  showToast(isTurnOn ? 'Menyalakan semua 6 lampu...' : 'Mematikan semua lampu...', 'info');

  try {
    const res = await fetch('/api/lampu/all', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({ action: action }),
    });

    const data = await res.json();
    if (data.success) {
      syncMaketFloorplan(data.status);
      showToast(data.message, 'success');
      if (data.log) addLogEntryUI(data.log);
    }
  } catch (err) {
    console.error('Master toggle error:', err);
  }
}

// 8. Sinkronisasi Denah SVG 6 Ruangan
function syncMaketFloorplan(status) {
  if (!status) return;

  const roomKeys = ['ruang_tamu', 'ruang_keluarga', 'kamar_1', 'kamar_2', 'dapur', 'wc'];
  roomKeys.forEach(k => {
    const isOn = (status[k] === 1);
    const box = document.getElementById(`svg-box-${k}`);
    const bulb = document.getElementById(`svg-bulb-${k}`);
    const glow = document.getElementById(`svg-glow-${k}`);
    const pinStatus = document.getElementById(`pin-status-${k}`);

    if (box) box.classList.toggle('glow-active', isOn);
    if (glow) glow.classList.toggle('is-on', isOn);
    if (bulb) {
      bulb.classList.toggle('is-on', isOn);
      bulb.classList.toggle('is-off', !isOn);
    }
    if (pinStatus) {
      pinStatus.innerHTML = isOn 
        ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' 
        : '<span style="color:#64748b;">○ LOW</span>';
    }
  });

  // Update counter aktif
  const counterElem = document.getElementById('stat-active-count');
  if (counterElem) {
    counterElem.textContent = `${status.active_count} / ${status.total_count}`;
  }

  // Sinkronisasi tombol saklar kartu
  if (status.devices && Array.isArray(status.devices)) {
    status.devices.forEach(dev => {
      const card = document.getElementById(`device-card-${dev.id}`);
      const chk = document.getElementById(`toggle-chk-${dev.id}`);
      if (card && chk) {
        chk.checked = dev.is_on;
        card.classList.toggle('is-active', dev.is_on);
        const lbl = card.querySelector('.switch-label');
        if (lbl) lbl.textContent = dev.is_on ? 'MENYALA' : 'MATI';
      }
    });
  }
}

// 9. Tambah Catatan Aktivitas
function addLogEntryUI(log) {
  const container = document.getElementById('activity-logs-list');
  if (!container) return;

  const item = document.createElement('div');
  item.className = 'log-item';
  item.innerHTML = `
    <div class="log-dot"></div>
    <div style="flex:1;">
      <div style="font-weight:600;">${log.action}</div>
      <div style="font-size:0.68rem; color:var(--text-dim);">Baru saja • ${log.user_or_source}</div>
    </div>
  `;

  container.insertBefore(item, container.firstChild);
  if (container.children.length > 7) {
    container.lastChild.remove();
  }
}

// 10. Real-time Telemetry Polling (Sinkronisasi HP & Komputer)
function startPolling() {
  setInterval(async () => {
    try {
      const res = await fetch('/api/telemetry');
      const data = await res.json();
      if (data && data.status) {
        syncMaketFloorplan(data.status);
      }
    } catch (e) {}
  }, 2500);
}

// 11. Modal Konek HP & Panduan PWA
function openPhoneModal() {
  triggerHaptic(20);
  const modal = document.getElementById('phone-connect-modal');
  if (modal) {
    modal.classList.add('open');
    const urlDisplay = document.getElementById('phone-url-display');
    if (urlDisplay) {
      const host = window.location.hostname || 'localhost';
      const port = window.location.port ? `:${window.location.port}` : '';
      urlDisplay.textContent = `http://${host}${port}`;
    }
  }
}

function closePhoneModal() {
  const modal = document.getElementById('phone-connect-modal');
  if (modal) modal.classList.remove('open');
}

function openArduinoModal() {
  triggerHaptic(20);
  const modal = document.getElementById('arduino-code-modal');
  if (modal) modal.classList.add('open');
}

function closeArduinoModal() {
  const modal = document.getElementById('arduino-code-modal');
  if (modal) modal.classList.remove('open');
}

function copyPhoneUrl() {
  const urlDisplay = document.getElementById('phone-url-display');
  if (!urlDisplay) return;
  navigator.clipboard.writeText(urlDisplay.textContent).then(() => {
    showToast('Tautan berhasil disalin!', 'success');
  });
}

// 12. PWA Installation Handler
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  const pwaBtn = document.getElementById('btn-pwa-install');
  if (pwaBtn) pwaBtn.style.display = 'flex';
});

function installPwa() {
  if (deferredPrompt) {
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choice) => {
      if (choice.outcome === 'accepted') {
        showToast('Aplikasi berhasil dipasang di HP!', 'success');
      }
      deferredPrompt = null;
      const pwaBtn = document.getElementById('btn-pwa-install');
      if (pwaBtn) pwaBtn.style.display = 'none';
    });
  } else {
    showToast('Buka menu browser: "Tambahkan ke Layar Utama"', 'info');
  }
}

// 13. Document Ready
document.addEventListener('DOMContentLoaded', () => {
  initViewMode();

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js')
      .then(() => console.log('Maket PWA Ready'))
      .catch((err) => console.warn('SW notice:', err));
  }

  startPolling();
});
