// TEMA
const themeToggle = document.getElementById('themeToggle');
let isLight = localStorage.getItem('eva-theme') === 'light';
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); });
}

// DROPDOWN USUARIO
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}

// SIDEBAR SUBMENUS
document.querySelectorAll('.sidebar nav li[data-toggle]').forEach(li => {
 li.addEventListener('click', () => {
  li.classList.toggle('open');
  const sub = li.nextElementSibling;
  if (sub && sub.classList.contains('sub-menu')) sub.classList.toggle('open');
 });
});

// DONUT CHART
function drawDonut() {
 const svg = document.getElementById('donutSvg');
 if (!svg) return;
 const data = [
  { pct: 71, color: '#4caf50', label: 'Activos', val: 30, pctText: '71%' },
  { pct: 19, color: '#ff9800', label: 'Advertencias', val: 8, pctText: '19%' },
  { pct: 4, color: '#f44336', label: 'Inactivos', val: 4, pctText: '4%' }
 ];
 const r = 50, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="70" cy="70" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const gap = c - len;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '70');
  circle.setAttribute('cy', '70');
  circle.setAttribute('r', String(r));
  circle.setAttribute('fill', 'none');
  circle.setAttribute('stroke', d.color);
  circle.setAttribute('stroke-width', '12');
  circle.setAttribute('stroke-linecap', 'round');
  circle.setAttribute('stroke-dasharray', `0 ${c}`);
  circle.setAttribute('stroke-dashoffset', String(-offset));
  circle.style.transition = `stroke-dasharray 1s ${i * 0.2}s cubic-bezier(.4,0,.2,1)`;
  svg.appendChild(circle);
  setTimeout(() => { circle.setAttribute('stroke-dasharray', `${len} ${gap}`); }, 100);
  offset += len;
 });
}

// LINE CHART
function drawLineChart() {
 const svg = document.getElementById('lineChart');
 if (!svg) return;
 const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
 const values = [60, 50, 70, 65, 90, 70, 85];
 const maxVal = 100;
 const w = 320, h = 160, padL = 30, padR = 10, padT = 10, padB = 30;
 const chartW = w - padL - padR, chartH = h - padT - padB;

 const isDark = !document.body.classList.contains('light-theme');
 const lineColor = isDark ? '#4fc3f7' : '#1565c0';
 const dotColor = isDark ? '#4fc3f7' : '#1565c0';
 const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.08)';
 const textColor = isDark ? '#7a829a' : '#6b7280';

 let html = '';

 for (let i = 0; i <= 4; i++) {
  const y = padT + (chartH / 4) * i;
  html += `<line x1="${padL}" y1="${y}" x2="${w - padR}" y2="${y}" stroke="${gridColor}" stroke-width="1"/>`;
 }

 const points = values.map((v, i) => {
  const x = padL + (chartW / (values.length - 1)) * i;
  const y = padT + chartH - (v / maxVal) * chartH;
  return { x, y };
 });

 const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ');
 html += `<path d="${pathD}" fill="none" stroke="${lineColor}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>`;

 const areaD = pathD + ` L${points[points.length - 1].x},${padT + chartH} L${points[0].x},${padT + chartH} Z`;
 html += `<path d="${areaD}" fill="url(#grad)" opacity="0"/>`;
 html += `<defs><linearGradient id="grad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="${lineColor}" stop-opacity="0.2"/><stop offset="100%" stop-color="${lineColor}" stop-opacity="0.02"/></linearGradient></defs>`;

 points.forEach((p, i) => {
  html += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="${dotColor}" stroke="${isDark ? '#111c30' : '#fff'}" stroke-width="2" opacity="0"/>`;
  html += `<text x="${p.x}" y="${h - 8}" text-anchor="middle" fill="${textColor}" font-size="10" font-family="Inter,sans-serif">${days[i]}</text>`;
 });

 svg.innerHTML = html;

 setTimeout(() => {
  svg.querySelector('path[d^="M"]')?.setAttribute('opacity', '1');
  svg.querySelector('path[d^="M"] + path')?.setAttribute('opacity', '1');
  svg.querySelectorAll('circle[r="4"]').forEach(c => c.setAttribute('opacity', '1'));
 }, 100);
}

drawDonut();
drawLineChart();

// Redibujar gráfico de líneas al cambiar tema
if (themeToggle) {
 themeToggle.addEventListener('click', () => { setTimeout(drawLineChart, 100); });
}
