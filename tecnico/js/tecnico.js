// ====== ESTADO GLOBAL ======
const page = location.pathname.split('/').pop() || 'indextec.html';
let isLight = localStorage.getItem('eva-theme') === 'light';
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); });
}
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}
document.querySelectorAll('.sidebar nav li[data-toggle]').forEach(li => {
 li.addEventListener('click', () => {
  li.classList.toggle('open');
  const sub = li.nextElementSibling;
  if (sub && sub.classList.contains('sub-menu')) sub.classList.toggle('open');
 });
});
function drawDonut() {
 const svg = document.getElementById('donutSvg');
 if (!svg) return;
 const data = [
  { pct: 75, color: '#4caf50', label: 'Activos', val: 18, pctText: '75%' },
  { pct: 17, color: '#ff9800', label: 'Advertencias', val: 4, pctText: '17%' },
  { pct: 8, color: '#f44336', label: 'Inactivos', val: 2, pctText: '8%' }
 ];
 const r = 60, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="80" cy="80" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const gap = c - len;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '80');
  circle.setAttribute('cy', '80');
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
if (page === 'indextec.html') drawDonut();
