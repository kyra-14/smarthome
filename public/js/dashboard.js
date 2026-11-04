/**
 * SmartHome IoT Command Center - Core Interactive Engine
 */

// Sound Synthesizer via Web Audio API
class AudioFX {
  constructor() {
    this.ctx = null;
    this.muted = false;
  }

  init() {
    if (!this.ctx) {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      this.ctx = new AudioContext();
    }
  }

  playToggle(state) {
    if (this.muted) return;
    this.init();
    if (this.ctx.state === 'suspended') {
      this.ctx.resume();
    }
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();
    osc.type = 'sine';

    if (state) {
      // Ascending sci-fi chirp
      osc.frequency.setValueAtTime(580, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(880, this.ctx.currentTime + 0.08);
    } else {
      // Descending tone
      osc.frequency.setValueAtTime(740, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(320, this.ctx.currentTime + 0.08);
    }

    gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.08);

    osc.connect(gain);
    gain.connect(this.ctx.destination);
    osc.start();
    osc.stop(this.ctx.currentTime + 0.09);
  }

  playBeep() {
    if (this.muted) return;
    this.init();
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();
    osc.type = 'triangle';
    osc.frequency.setValueAtTime(900, this.ctx.currentTime);
    gain.gain.setValueAtTime(0.05, this.ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.05);
    osc.connect(gain);
    gain.connect(this.ctx.destination);
    osc.start();
    osc.stop(this.ctx.currentTime + 0.06);
  }

  playVoicePing(type = 'start') {
    if (this.muted) return;
    this.init();
    if (this.ctx.state === 'suspended') this.ctx.resume();
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();
    osc.type = 'sine';

    if (type === 'start') {
      osc.frequency.setValueAtTime(480, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(960, this.ctx.currentTime + 0.12);
      gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.14);
      osc.connect(gain);
      gain.connect(this.ctx.destination);
      osc.start();
      osc.stop(this.ctx.currentTime + 0.15);
    } else {
      osc.frequency.setValueAtTime(960, this.ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(1440, this.ctx.currentTime + 0.15);
      gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.18);
      osc.connect(gain);
      gain.connect(this.ctx.destination);
      osc.start();
      osc.stop(this.ctx.currentTime + 0.19);
    }
  }
}

const audio = new AudioFX();

// CSRF Token Helper
function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

// Toast Notification System
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = 'toast';
  
  let icon = 'fa-info-circle';
  if (type === 'success') icon = 'fa-check-circle';
  if (type === 'warning') icon = 'fa-triangle-exclamation';

  toast.innerHTML = `
    <i class="fa-solid ${icon}" style="color: ${type === 'success' ? '#10b981' : (type === 'warning' ? '#f59e0b' : '#22d3ee')}"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(10px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// Global Device Toggle Handler
async function toggleDevice(deviceId, checkboxElem) {
  const isChecked = checkboxElem.checked;
  audio.playToggle(isChecked);

  const card = document.getElementById(`device-card-${deviceId}`);
  if (card) {
    card.classList.toggle('is-on', isChecked);
    const statusText = card.querySelector('.status-text');
    if (statusText) {
      statusText.textContent = isChecked ? 'Aktif' : 'Nonaktif';
    }
  }

  // Optimistic pin update
  const pin = document.getElementById(`floor-pin-${deviceId}`);
  if (pin) {
    pin.classList.toggle('is-on', isChecked);
  }
  updateRoomGlows();

  try {
    const res = await fetch(`/api/devices/${deviceId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });

    const data = await res.json();
    if (data.success) {
      updateMetricsUI(data.metrics);
      if (data.log) addLogEntryUI(data.log);
      showToast(`${data.device.name} ${data.device.is_on ? 'dinyalakan' : 'dimatikan'}`, 'success');
      
      // Sync pin icon if lock
      if (pin && data.device.type === 'lock') {
        const icon = pin.querySelector('i');
        if (icon) icon.className = `fa-solid ${data.device.is_on ? 'fa-lock' : 'fa-lock-open'}`;
      }
      updateRoomGlows();
      if (window.CURRENT_SELECTED_ROOM) {
        selectFloorRoom(window.CURRENT_SELECTED_ROOM);
      }
    }
  } catch (err) {
    console.error('Toggle failed:', err);
    // revert checkbox if failed
    checkboxElem.checked = !isChecked;
    if (card) card.classList.toggle('is-on', !isChecked);
    if (pin) pin.classList.toggle('is-on', !isChecked);
    updateRoomGlows();
    showToast('Gagal mengubah status perangkat', 'warning');
  }
}

// Update Device Value (Sliders, Steppers)
let debounceTimers = {};
function handleValueChange(deviceId, value, type = 'general') {
  audio.playBeep();
  
  // Update local display immediately
  const display = document.getElementById(`val-display-${deviceId}`);
  if (display) {
    if (type === 'ac') display.textContent = `${value}°C`;
    else if (type === 'light') display.textContent = `${value}%`;
    else display.textContent = value;
  }

  clearTimeout(debounceTimers[deviceId]);
  debounceTimers[deviceId] = setTimeout(async () => {
    try {
      const res = await fetch(`/api/devices/${deviceId}/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ value: parseInt(value) }),
      });
      const data = await res.json();
      if (data.success) {
        updateMetricsUI(data.metrics);
        if (data.log) addLogEntryUI(data.log);
      }
    } catch (err) {
      console.error('Update failed:', err);
    }
  }, 400);
}

// AC Temperature Stepper
function adjustTemp(deviceId, delta) {
  const display = document.getElementById(`temp-val-${deviceId}`);
  if (!display) return;
  
  let current = parseInt(display.textContent);
  let next = Math.max(16, Math.min(30, current + delta));
  if (current === next) return;

  display.textContent = next;
  audio.playBeep();

  clearTimeout(debounceTimers[deviceId]);
  debounceTimers[deviceId] = setTimeout(async () => {
    try {
      const res = await fetch(`/api/devices/${deviceId}/update`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ value: next, is_on: true }),
      });
      const data = await res.json();
      if (data.success) {
        // ensure card is turned on
        const card = document.getElementById(`device-card-${deviceId}`);
        if (card) {
          card.classList.add('is-on');
          const toggle = card.querySelector('input[type="checkbox"]');
          if (toggle) toggle.checked = true;
        }
        updateMetricsUI(data.metrics);
        if (data.log) addLogEntryUI(data.log);
        showToast(`Suhu AC diatur ke ${next}°C`, 'info');
      }
    } catch (err) {
      console.error('Temp update failed:', err);
    }
  }, 400);
}

// Scene Activation
async function activateScene(sceneId, sceneSlug) {
  audio.playBeep();
  
  // Highlight active card
  document.querySelectorAll('.scene-card').forEach(card => card.classList.remove('active'));
  const activeCard = document.getElementById(`scene-card-${sceneId}`);
  if (activeCard) activeCard.classList.add('active');

  showToast('Mengaktifkan preset skenario...', 'info');

  try {
    const res = await fetch(`/api/scenes/${sceneId}/activate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });

    const data = await res.json();
    if (data.success) {
      showToast(`Skenario ${data.scene.name} aktif!`, 'success');
      updateMetricsUI(data.metrics);
      if (data.log) addLogEntryUI(data.log);
      
      // Sync devices on UI
      if (data.devices) {
        syncAllDevicesUI(data.devices);
      }
    }
  } catch (err) {
    console.error('Scene failed:', err);
    showToast('Gagal mengaktifkan skenario', 'warning');
  }
}

// Master Action Buttons
async function quickMasterAction(actionType) {
  audio.playBeep();

  let url = '/api/quick-action/all-off';
  let body = { category: 'all' };

  if (actionType === 'lights_off') {
    body = { category: 'lights' };
  } else if (actionType === 'lock_all') {
    url = '/api/quick-action/lock-all';
  }

  showToast('Memproses perintah master...', 'info');

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(body),
    });

    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      updateMetricsUI(data.metrics);
      if (data.log) addLogEntryUI(data.log);

      if (data.devices) {
        syncAllDevicesUI(data.devices);
      }
    }
  } catch (err) {
    console.error('Master action failed:', err);
  }
}

// Toggle Automation Schedule
async function toggleAutomation(autoId, checkboxElem) {
  audio.playToggle(checkboxElem.checked);
  try {
    const res = await fetch(`/api/automations/${autoId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });
    const data = await res.json();
    if (data.success) {
      if (data.log) addLogEntryUI(data.log);
      showToast(`Jadwal '${data.automation.title}' diperbarui`, 'info');
    }
  } catch (err) {
    console.error('Automation toggle failed:', err);
  }
}

// Room Tabs Filter
function filterRoom(roomSlug, tabElem) {
  audio.playBeep();
  document.querySelectorAll('.room-tab').forEach(t => t.classList.remove('active'));
  tabElem.classList.add('active');

  const cards = document.querySelectorAll('.device-card');
  cards.forEach(card => {
    const cardRoom = card.getAttribute('data-room');
    if (roomSlug === 'all' || cardRoom === roomSlug) {
      card.style.display = 'flex';
      card.style.animation = 'fadeInUp 0.3s ease';
    } else {
      card.style.display = 'none';
    }
  });
}

// Update UI Metrics
function updateMetricsUI(metrics) {
  if (!metrics) return;
  
  const activeCount = document.getElementById('metric-active-count');
  if (activeCount) activeCount.textContent = metrics.active_devices;

  const totalWatts = document.getElementById('metric-total-watts');
  if (totalWatts) totalWatts.textContent = metrics.total_power_watts;

  const totalKw = document.getElementById('metric-total-kw');
  if (totalKw) totalKw.textContent = metrics.total_power_kw;

  const costHour = document.getElementById('metric-cost-hour');
  if (costHour) costHour.textContent = `Rp ${Number(metrics.cost_per_hour).toLocaleString('id-ID')}`;

  const securityBadge = document.getElementById('metric-security-status');
  if (securityBadge) {
    if (metrics.all_locked) {
      securityBadge.innerHTML = '<span style="color:#10b981"><i class="fa-solid fa-lock"></i> Terkunci Aman</span>';
    } else {
      securityBadge.innerHTML = '<span style="color:#f59e0b"><i class="fa-solid fa-lock-open"></i> Akses Terbuka</span>';
    }
  }
}

// Add Log Entry to Sidebar / List
function addLogEntryUI(log) {
  const list = document.getElementById('activity-logs-list');
  if (!list) return;

  const item = document.createElement('div');
  item.className = 'log-item';
  item.style.animation = 'slideInDown 0.3s ease';

  let icon = 'fa-info';
  let badgeClass = 'info';
  if (log.type === 'success') { icon = 'fa-check'; badgeClass = 'success'; }
  if (log.type === 'warning') { icon = 'fa-triangle-exclamation'; badgeClass = 'warning'; }

  item.innerHTML = `
    <div class="log-badge ${badgeClass}">
      <i class="fa-solid ${icon}"></i>
    </div>
    <div class="log-content">
      <div class="log-title">${log.action}</div>
      <div class="log-time">Baru saja • ${log.user_or_source}</div>
    </div>
  `;

  list.insertBefore(item, list.firstChild);
  if (list.children.length > 15) {
    list.lastChild.remove();
  }
}

// Add Device Modal Handlers
function openAddDeviceModal() {
  audio.playBeep();
  const modal = document.getElementById('add-device-modal');
  if (modal) modal.classList.add('open');
}

function closeAddDeviceModal() {
  const modal = document.getElementById('add-device-modal');
  if (modal) modal.classList.remove('open');
}

async function handleAddDeviceSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);
  const payload = {
    room_id: formData.get('room_id'),
    name: formData.get('name'),
    type: formData.get('type'),
    power_watts: formData.get('power_watts'),
  };

  try {
    const res = await fetch('/api/devices/add', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (data.success) {
      showToast(`Perangkat ${data.device.name} berhasil ditambahkan!`, 'success');
      closeAddDeviceModal();
      form.reset();
      // Reload page to re-render grid with proper room associations
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (err) {
    console.error('Add device error:', err);
    showToast('Gagal menambahkan perangkat', 'warning');
  }
}

// Live CCTV AI Vision Canvas Simulation
function initCctvSimulation() {
  const canvas = document.getElementById('cctv-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  let width = canvas.width = 320;
  let height = canvas.height = 190;
  let angle = 0;

  function render() {
    // Simulated futuristic camera night-vision / thermal feed
    ctx.fillStyle = '#050c18';
    ctx.fillRect(0, 0, width, height);

    // Subtle background grid
    ctx.strokeStyle = 'rgba(6, 182, 212, 0.08)';
    ctx.lineWidth = 1;
    for (let x = 0; x < width; x += 30) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, height);
      ctx.stroke();
    }
    for (let y = 0; y < height; y += 30) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(width, y);
      ctx.stroke();
    }

    // Moving simulated object (e.g. car / person in front gate)
    angle += 0.02;
    let objX = 130 + Math.sin(angle) * 60;
    let objY = 85 + Math.cos(angle * 0.7) * 20;

    // Detection box
    ctx.strokeStyle = '#22d3ee';
    ctx.lineWidth = 1.5;
    ctx.strokeRect(objX, objY, 65, 45);

    // Corners highlight
    ctx.fillStyle = '#22d3ee';
    ctx.fillRect(objX - 2, objY - 2, 6, 2);
    ctx.fillRect(objX - 2, objY - 2, 2, 6);
    ctx.fillRect(objX + 61, objY - 2, 6, 2);
    ctx.fillRect(objX + 65, objY - 2, 2, 6);

    // Object Label
    ctx.fillStyle = 'rgba(6, 182, 212, 0.9)';
    ctx.fillRect(objX, objY - 14, 65, 14);
    ctx.fillStyle = '#000000';
    ctx.font = '9px Outfit, sans-serif';
    ctx.fontWeight = 'bold';
    ctx.fillText('MOBIL 96%', objX + 4, objY - 4);

    requestAnimationFrame(render);
  }

  render();
}

// Live Energy SVG Waveform Generator
function updateEnergyWave() {
  const path = document.getElementById('energy-wave-path');
  if (!path) return;

  const points = [];
  const width = 300;
  const height = 90;
  const segments = 12;

  for (let i = 0; i <= segments; i++) {
    const x = (i / segments) * width;
    const y = 45 + Math.sin((Date.now() / 600) + (i * 0.8)) * 18 + (Math.random() * 4 - 2);
    points.push(`${x},${y}`);
  }

  const d = `M 0,90 L ${points.join(' L ')} L ${width},90 Z`;
  path.setAttribute('d', d);
}

// Telemetry Polling (every 4 seconds)
function startTelemetryPolling() {
  setInterval(async () => {
    try {
      const res = await fetch('/api/telemetry');
      const data = await res.json();
      if (data) {
        const liveWatts = document.getElementById('metric-total-watts');
        if (liveWatts) liveWatts.textContent = data.live_watts;
        
        const gridVoltage = document.getElementById('metric-grid-voltage');
        if (gridVoltage) gridVoltage.textContent = `${data.grid_voltage} V`;

        const clockElem = document.getElementById('cctv-clock');
        if (clockElem) clockElem.textContent = data.timestamp;

        updateEnergyWave();
      }
    } catch (e) {
      // ignore
    }
  }, 4000);
}

// Document Ready Initialization
document.addEventListener('DOMContentLoaded', () => {
  initCctvSimulation();
  setInterval(updateEnergyWave, 800);
  startTelemetryPolling();

  // Add Device Form
  const form = document.getElementById('add-device-form');
  if (form) {
    form.addEventListener('submit', handleAddDeviceSubmit);
  }

  // Audio mute toggle
  const soundBtn = document.getElementById('btn-sound-toggle');
  if (soundBtn) {
    soundBtn.addEventListener('click', () => {
      audio.muted = !audio.muted;
      soundBtn.innerHTML = audio.muted ? '<i class="fa-solid fa-volume-xmark"></i>' : '<i class="fa-solid fa-volume-high"></i>';
      showToast(audio.muted ? 'Suara dinonaktifkan' : 'Efek audio aktif', 'info');
    });
  }

  // Initialize Smart Floor Plan & Voice Assistant
  initFloorplan();
  initVoiceAssistant();
});

// ============================================================
// SMART FLOOR PLAN (DENAH RUMAH INTERAKTIF) CONTROLLER
// ============================================================

window.CURRENT_SELECTED_ROOM = 'living-room';
window.CURRENT_FLOOR_LEVEL = 'floor-1';

function initFloorplan() {
  updateRoomGlows();
  selectFloorRoom('living-room');
}

// Switch between Classic Dashboard View and Denah Rumah (Floor Plan)
function switchViewMode(mode) {
  audio.playBeep();
  const classicView = document.getElementById('dashboard-classic-view');
  const floorplanView = document.getElementById('floorplan-view');
  const pillDashboard = document.getElementById('pill-dashboard');
  const pillFloorplan = document.getElementById('pill-floorplan');
  const navDashboard = document.getElementById('nav-dashboard');
  const navFloorplan = document.getElementById('nav-floorplan');

  if (mode === 'floorplan') {
    if (classicView) classicView.style.display = 'none';
    if (floorplanView) floorplanView.classList.add('active');
    if (pillDashboard) pillDashboard.classList.remove('active');
    if (pillFloorplan) pillFloorplan.classList.add('active');
    if (navDashboard) navDashboard.classList.remove('active');
    if (navFloorplan) navFloorplan.classList.add('active');
    updateRoomGlows();
    selectFloorRoom(window.CURRENT_SELECTED_ROOM || 'living-room');
    showToast('Tampilan Denah Rumah Interaktif Aktif', 'info');
  } else {
    if (classicView) classicView.style.display = 'block';
    if (floorplanView) floorplanView.classList.remove('active');
    if (pillDashboard) pillDashboard.classList.add('active');
    if (pillFloorplan) pillFloorplan.classList.remove('active');
    if (navDashboard) navDashboard.classList.add('active');
    if (navFloorplan) navFloorplan.classList.remove('active');
  }
}

// Switch Floor Levels (Lantai 1, Lantai 2, Overview)
function switchFloorLevel(levelId) {
  audio.playBeep();
  window.CURRENT_FLOOR_LEVEL = levelId;

  document.querySelectorAll('.floor-level-btn').forEach(btn => btn.classList.remove('active'));
  const activeBtn = document.getElementById(`btn-${levelId}`);
  if (activeBtn) activeBtn.classList.add('active');

  const svgFloor1 = document.getElementById('svg-layer-floor-1');
  const svgFloor2 = document.getElementById('svg-layer-floor-2');
  const pinsFloor1 = document.getElementById('pins-floor-1');
  const pinsFloor2 = document.getElementById('pins-floor-2');

  if (levelId === 'floor-1') {
    if (svgFloor1) svgFloor1.style.display = 'inline';
    if (svgFloor2) svgFloor2.style.display = 'none';
    if (pinsFloor1) pinsFloor1.style.display = 'block';
    if (pinsFloor2) pinsFloor2.style.display = 'none';
    selectFloorRoom('living-room');
  } else if (levelId === 'floor-2') {
    if (svgFloor1) svgFloor1.style.display = 'none';
    if (svgFloor2) svgFloor2.style.display = 'inline';
    if (pinsFloor1) pinsFloor1.style.display = 'none';
    if (pinsFloor2) pinsFloor2.style.display = 'block';
    selectFloorRoom('master-bedroom');
  } else if (levelId === 'floor-all') {
    if (svgFloor1) svgFloor1.style.display = 'inline';
    if (svgFloor2) svgFloor2.style.display = 'inline';
    if (pinsFloor1) pinsFloor1.style.display = 'block';
    if (pinsFloor2) pinsFloor2.style.display = 'block';
    selectFloorRoom('living-room');
  }
  updateRoomGlows();
}

// Change Floor Plan Visual Theme
function changeFloorplanTheme(theme) {
  const viewport = document.getElementById('floorplan-viewport');
  if (!viewport) return;
  viewport.classList.remove('theme-night', 'theme-clean');
  if (theme === 'night') viewport.classList.add('theme-night');
  else if (theme === 'clean') viewport.classList.add('theme-clean');
}

// Select a Room on the Floor Plan & Inspect Details
function selectFloorRoom(roomSlug) {
  window.CURRENT_SELECTED_ROOM = roomSlug;

  // Highlight polygon
  document.querySelectorAll('.room-polygon').forEach(poly => poly.classList.remove('selected'));
  const poly = document.getElementById(`poly-${roomSlug}`);
  if (poly) poly.classList.add('selected');

  // Find Room info from window.INITIAL_ROOMS if available
  let roomData = (window.INITIAL_ROOMS || []).find(r => r.slug === roomSlug);
  if (!roomData) {
    const meta = {
      'living-room': { name: 'Ruang Keluarga & Tamu', floor: 'Lantai 1 • Zona IoT Utama', temp: 24.2, humidity: 56, aqi: 24, icon: 'fa-couch' },
      'master-bedroom': { name: 'Kamar Tidur Utama', floor: 'Lantai 2 • Master Suite', temp: 22.0, humidity: 52, aqi: 18, icon: 'fa-bed' },
      'kitchen': { name: 'Dapur & Ruang Makan', floor: 'Lantai 1 • Service Zone', temp: 26.5, humidity: 64, aqi: 35, icon: 'fa-utensils' },
      'home-office': { name: 'Studio Kerja & IoT Lab', floor: 'Lantai 2 • Private Studio', temp: 23.0, humidity: 50, aqi: 20, icon: 'fa-laptop-code' },
      'outdoor-garden': { name: 'Teras & Halaman Depan', floor: 'Outdoor • Akses & Keamanan', temp: 29.8, humidity: 72, aqi: 45, icon: 'fa-tree' },
    };
    roomData = meta[roomSlug] || { name: 'Ruangan', floor: 'Lantai 1', temp: 24.0, humidity: 55, aqi: 25, icon: 'fa-door-open' };
  }

  // Update Inspector elements
  const titleElem = document.getElementById('inspector-room-title');
  if (titleElem) titleElem.textContent = roomData.name;
  
  const floorElem = document.getElementById('inspector-room-floor');
  if (floorElem) floorElem.textContent = roomData.floor || 'Lantai 1';

  const iconElem = document.getElementById('inspector-room-icon');
  if (iconElem) iconElem.className = `fa-solid ${roomData.icon || 'fa-house'}`;

  const tempElem = document.getElementById('inspector-temp');
  if (tempElem) tempElem.textContent = `${roomData.ambient_temp || roomData.temp || 24} °C`;

  const humElem = document.getElementById('inspector-humidity');
  if (humElem) humElem.textContent = `${roomData.ambient_humidity || roomData.humidity || 55}% RH`;

  const aqiElem = document.getElementById('inspector-aqi');
  if (aqiElem) {
    const aqi = roomData.aqi || 24;
    aqiElem.textContent = `${aqi} (Baik)`;
  }

  // Calculate power and populate devices in this room
  renderInspectorDevices(roomSlug);
}

// Render Devices List inside Floor Plan Inspector
function renderInspectorDevices(roomSlug) {
  const container = document.getElementById('inspector-devices-container');
  if (!container) return;

  const roomPins = document.querySelectorAll(`.device-pin[data-room="${roomSlug}"]`);
  let totalWatts = 0;
  container.innerHTML = '';

  if (roomPins.length === 0) {
    container.innerHTML = '<div style="font-size:0.78rem; color:var(--text-muted); padding:10px 0;">Tidak ada perangkat pada ruangan ini.</div>';
    const powerElem = document.getElementById('inspector-power');
    if (powerElem) powerElem.textContent = '0 W';
    return;
  }

  roomPins.forEach(pin => {
    const devId = pin.getAttribute('data-device-id');
    const name = pin.getAttribute('data-name');
    const type = pin.getAttribute('data-type');
    const isOn = pin.classList.contains('is-on');

    const card = document.getElementById(`device-card-${devId}`);
    const devWattsText = card ? card.querySelector('.device-top + div + div span:last-child') : null;
    let watts = 25;
    if (card) {
      const wSpan = card.querySelector('div[style*="font-family: Outfit"]');
      if (wSpan) watts = parseFloat(wSpan.textContent) || 25;
    }
    if (isOn) totalWatts += watts;

    let iconClass = 'fa-lightbulb';
    if (type === 'ac') iconClass = 'fa-snowflake';
    else if (type === 'lock') iconClass = isOn ? 'fa-lock' : 'fa-lock-open';
    else if (type === 'camera') iconClass = 'fa-video';
    else if (type === 'tv') iconClass = 'fa-tv';
    else if (type === 'curtain') iconClass = 'fa-blinds';
    else if (type === 'purifier') iconClass = 'fa-wind';
    else if (type === 'sensor') iconClass = 'fa-triangle-exclamation';

    const row = document.createElement('div');
    row.className = 'inspector-device-row';
    row.innerHTML = `
      <div class="inspector-device-info">
        <i class="fa-solid ${iconClass}" style="color: ${isOn ? 'var(--cyan-400)' : 'var(--text-dim)'}; width: 16px;"></i>
        <div>
          <div style="font-weight: 600; font-size: 0.82rem;">${name}</div>
          <div style="font-size: 0.68rem; color: var(--text-muted);">${isOn ? 'Menyala' : 'Nonaktif'} • ${Math.round(watts)} W</div>
        </div>
      </div>
      <label class="switch" style="transform: scale(0.8);">
        <input type="checkbox" ${isOn ? 'checked' : ''} onchange="toggleDevice(${devId}, this)">
        <span class="slider-toggle"></span>
      </label>
    `;
    container.appendChild(row);
  });

  const powerElem = document.getElementById('inspector-power');
  if (powerElem) powerElem.textContent = `${Math.round(totalWatts)} W`;
}

// Click Handler on Floor Plan Pin
function handleFloorPinClick(deviceId, type, event) {
  event.stopPropagation();
  audio.playBeep();

  const pin = document.getElementById(`floor-pin-${deviceId}`);
  if (!pin) return;

  const card = document.getElementById(`device-card-${deviceId}`);
  const checkbox = card ? card.querySelector('input[type="checkbox"]') : null;

  if (checkbox) {
    checkbox.checked = !checkbox.checked;
    toggleDevice(deviceId, checkbox);
  } else {
    // If not found in DOM, fetch toggle directly
    const nextState = !pin.classList.contains('is-on');
    pin.classList.toggle('is-on', nextState);
    updateRoomGlows();

    fetch(`/api/devices/${deviceId}/toggle`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          updateMetricsUI(data.metrics);
          if (data.log) addLogEntryUI(data.log);
          showToast(`${data.device.name} ${data.device.is_on ? 'dinyalakan' : 'dimatikan'}`, 'success');
          updateRoomGlows();
        }
      })
      .catch(e => console.error(e));
  }
}

// Quick Action: Turn All Lights in Currently Selected Room On/Off
async function quickToggleRoomLights(turnOn) {
  audio.playBeep();
  const roomSlug = window.CURRENT_SELECTED_ROOM || 'living-room';
  const lightPins = document.querySelectorAll(`.device-pin[data-room="${roomSlug}"][data-type="light"]`);

  if (lightPins.length === 0) {
    showToast('Tidak ada lampu pada ruangan ini', 'warning');
    return;
  }

  showToast(`Mengubah status lampu di ${roomSlug}...`, 'info');

  for (const pin of lightPins) {
    const devId = pin.getAttribute('data-device-id');
    const card = document.getElementById(`device-card-${devId}`);
    const chk = card ? card.querySelector('input[type="checkbox"]') : null;
    const currentState = pin.classList.contains('is-on');

    if (currentState !== turnOn) {
      if (chk) {
        chk.checked = turnOn;
        await toggleDevice(devId, chk);
      } else {
        pin.classList.toggle('is-on', turnOn);
      }
    }
  }

  updateRoomGlows();
  renderInspectorDevices(roomSlug);
}

// Update Dynamic Room Ambient Illumination Glows
function updateRoomGlows() {
  const rooms = ['living-room', 'kitchen', 'outdoor-garden', 'master-bedroom', 'home-office'];
  rooms.forEach(slug => {
    const glowElem = document.getElementById(`glow-${slug}`);
    if (!glowElem) return;

    // Check if any light in this room is ON
    const activeLights = document.querySelectorAll(`.device-pin[data-room="${slug}"][data-type="light"].is-on`);
    if (activeLights.length > 0) {
      glowElem.setAttribute('opacity', '1');
    } else {
      glowElem.setAttribute('opacity', '0');
    }
  });
}

// Synchronize all devices on both classic dashboard & floor plan
function syncAllDevicesUI(devices) {
  if (!devices || !Array.isArray(devices)) return;

  devices.forEach(dev => {
    // Card in dashboard
    const card = document.getElementById(`device-card-${dev.id}`);
    if (card) {
      const toggle = card.querySelector('input[type="checkbox"]');
      if (toggle) toggle.checked = dev.is_on;
      card.classList.toggle('is-on', dev.is_on);
      const statusText = card.querySelector('.status-text');
      if (statusText) statusText.textContent = dev.is_on ? 'Aktif' : 'Nonaktif';
      
      const tempVal = document.getElementById(`temp-val-${dev.id}`);
      if (tempVal) tempVal.textContent = dev.value;
      const slider = card.querySelector('.custom-range');
      if (slider) slider.value = dev.value;
      const valDisplay = document.getElementById(`val-display-${dev.id}`);
      if (valDisplay) {
        valDisplay.textContent = dev.type === 'light' ? `${dev.value}%` : dev.value;
      }
    }

    // Pin in floor plan
    const pin = document.getElementById(`floor-pin-${dev.id}`);
    if (pin) {
      pin.classList.toggle('is-on', dev.is_on);
      if (dev.type === 'lock') {
        const icon = pin.querySelector('i');
        if (icon) icon.className = `fa-solid ${dev.is_on ? 'fa-lock' : 'fa-lock-open'}`;
      }
    }
  });

  updateRoomGlows();
  if (window.CURRENT_SELECTED_ROOM) {
    renderInspectorDevices(window.CURRENT_SELECTED_ROOM);
  }
}

// ============================================================
// VOICE ASSISTANT (PERINTAH SUARA AI) CONTROLLER
// ============================================================

let speechRecognition = null;
let isListening = false;

function initVoiceAssistant() {
  const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRec) {
    speechRecognition = new SpeechRec();
    speechRecognition.continuous = false;
    speechRecognition.interimResults = true;
    speechRecognition.lang = 'id-ID'; // Indonesian primary recognition

    speechRecognition.onstart = () => {
      isListening = true;
      setVoiceListeningUI(true);
      audio.playVoicePing('start');
    };

    speechRecognition.onresult = (event) => {
      let interimTranscript = '';
      let finalTranscript = '';

      for (let i = event.resultIndex; i < event.results.length; ++i) {
        if (event.results[i].isFinal) {
          finalTranscript += event.results[i][0].transcript;
        } else {
          interimTranscript += event.results[i][0].transcript;
        }
      }

      const transcriptBox = document.getElementById('voice-transcript-box');
      if (transcriptBox) {
        transcriptBox.textContent = `"${finalTranscript || interimTranscript}"`;
      }

      if (finalTranscript.trim()) {
        executeVoiceCommandText(finalTranscript.trim());
      }
    };

    speechRecognition.onerror = (event) => {
      console.warn('Speech recognition notice/error:', event.error);
      setVoiceListeningUI(false);
      const transcriptBox = document.getElementById('voice-transcript-box');
      if (transcriptBox && event.error !== 'no-speech') {
        transcriptBox.textContent = 'Gunakan tombol saran atau ketik perintah di bawah.';
      }
    };

    speechRecognition.onend = () => {
      isListening = false;
      setVoiceListeningUI(false);
    };
  }

  // Keyboard shortcut listener: Pressing 'V' activates voice assistant
  window.addEventListener('keydown', (e) => {
    if (e.key === 'v' || e.key === 'V') {
      const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
      if (activeTag !== 'input' && activeTag !== 'textarea' && activeTag !== 'select') {
        e.preventDefault();
        openVoiceModal();
      }
    }
  });
}

function openVoiceModal() {
  audio.playVoicePing('start');
  const modal = document.getElementById('voice-modal-backdrop');
  if (modal) modal.classList.add('open');

  const responseBox = document.getElementById('voice-response-box');
  if (responseBox) responseBox.classList.remove('show');

  const transcriptBox = document.getElementById('voice-transcript-box');
  if (transcriptBox) transcriptBox.textContent = '"Katakan perintah... contoh: \'Nyalakan semua lampu\'"';

  // Auto-start listening if SpeechRecognition is available
  setTimeout(() => {
    if (speechRecognition && !isListening) {
      try {
        speechRecognition.start();
      } catch (err) {
        // Recognition might already be running
      }
    }
  }, 350);
}

function closeVoiceModal() {
  const modal = document.getElementById('voice-modal-backdrop');
  if (modal) modal.classList.remove('open');
  if (speechRecognition && isListening) {
    try { speechRecognition.stop(); } catch (e) {}
  }
  setVoiceListeningUI(false);
}

function handleVoiceBackdropClick(e) {
  if (e.target && e.target.id === 'voice-modal-backdrop') {
    closeVoiceModal();
  }
}

function toggleVoiceListening() {
  if (!speechRecognition) {
    showToast('Browser ini belum mendukung Web Speech Recognition. Gunakan tombol saran atau ketik perintah di bawah!', 'warning');
    const manualInput = document.getElementById('voice-manual-text');
    if (manualInput) manualInput.focus();
    return;
  }

  if (isListening) {
    try { speechRecognition.stop(); } catch (e) {}
    setVoiceListeningUI(false);
  } else {
    try { speechRecognition.start(); } catch (e) {}
  }
}

function setVoiceListeningUI(active) {
  const orb = document.getElementById('voice-orb-core');
  const bars = document.getElementById('voice-wave-bars');
  const statusText = document.getElementById('voice-status-text');
  const orbIcon = document.getElementById('voice-orb-icon');

  if (active) {
    if (orb) orb.classList.add('listening');
    if (bars) bars.classList.add('active');
    if (statusText) statusText.textContent = 'Mendengarkan suara Anda...';
    if (orbIcon) orbIcon.className = 'fa-solid fa-microphone-lines';
  } else {
    if (orb) orb.classList.remove('listening');
    if (bars) bars.classList.remove('active');
    if (statusText) statusText.textContent = 'Klik mikrofon untuk berbicara';
    if (orbIcon) orbIcon.className = 'fa-solid fa-microphone';
  }
}

// Process voice command via POST API
async function executeVoiceCommandText(commandText) {
  if (!commandText || !commandText.trim()) return;

  const transcriptBox = document.getElementById('voice-transcript-box');
  if (transcriptBox) transcriptBox.textContent = `"${commandText}"`;

  const statusText = document.getElementById('voice-status-text');
  if (statusText) statusText.textContent = 'Memproses perintah AI...';

  try {
    const res = await fetch('/api/voice-command', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify({ command: commandText }),
    });

    const data = await res.json();
    audio.playVoicePing('end');

    if (data.success) {
      // Display spoken response
      const responseBox = document.getElementById('voice-response-box');
      const responseText = document.getElementById('voice-response-text');
      if (responseBox && responseText) {
        responseText.textContent = data.spoken_response || data.message;
        responseBox.classList.add('show');
      }

      // Voice TTS feedback
      speakVoiceResponse(data.spoken_response || data.message);

      // Synchronize devices & metrics
      if (data.devices) syncAllDevicesUI(data.devices);
      if (data.metrics) updateMetricsUI(data.metrics);
      if (data.log) addLogEntryUI(data.log);

      showToast(data.message, 'success');

      // Auto-switch to floorplan view if requested
      if (data.action_type === 'open_floorplan') {
        switchViewMode('floorplan');
      } else if (data.action_type === 'close_floorplan') {
        switchViewMode('dashboard');
      }

      // If room focus returned, highlight it on floorplan
      if (data.focus_room) {
        if (data.focus_room === 'master-bedroom' || data.focus_room === 'home-office') {
          switchFloorLevel('floor-2');
        } else {
          switchFloorLevel('floor-1');
        }
        selectFloorRoom(data.focus_room);
      }
    } else {
      const responseBox = document.getElementById('voice-response-box');
      const responseText = document.getElementById('voice-response-text');
      if (responseBox && responseText) {
        responseText.textContent = data.spoken_response || data.message;
        responseBox.classList.add('show');
      }
      speakVoiceResponse(data.spoken_response || data.message);
      showToast(data.message, 'warning');
    }
  } catch (err) {
    console.error('Voice command error:', err);
    showToast('Terjadi kesalahan saat memproses perintah suara', 'warning');
  } finally {
    setVoiceListeningUI(false);
  }
}

// Speak Voice Assistant Response via Web Speech Synthesis (TTS)
function speakVoiceResponse(text) {
  if (!window.speechSynthesis || audio.muted) return;

  try {
    window.speechSynthesis.cancel(); // Stop any pending speech
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.rate = 1.05;
    utterance.pitch = 1.0;

    // Pick Indonesian voice if available
    const voices = window.speechSynthesis.getVoices();
    const idVoice = voices.find(v => v.lang === 'id-ID' || v.lang.includes('id') || v.name.includes('Indonesia'));
    if (idVoice) utterance.voice = idVoice;

    window.speechSynthesis.speak(utterance);
  } catch (e) {
    console.warn('Speech synthesis notice:', e);
  }
}

// Manual Form Input Submit in Voice Modal
function handleVoiceFormSubmit(e) {
  e.preventDefault();
  const input = document.getElementById('voice-manual-text');
  if (!input || !input.value.trim()) return;

  const text = input.value.trim();
  input.value = '';
  executeVoiceCommandText(text);
}
