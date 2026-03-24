// ─────────────────────────────────────────
//  Page navigation labels
// ─────────────────────────────────────────
const pageLabels = {
  dashboard:   'Supervisor Dashboard',
  profiles:    'Sales Assistant Profiles',
  analytics:   'Analytics Dashboard',
  performance: 'Performance Report & Training',
  settings:    'Settings'
};

// ─────────────────────────────────────────
//  Navigate between sidebar pages
// ─────────────────────────────────────────
function navigate(el) {
  // Remove active state from all nav items
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');

  // Hide all content pages
  const page = el.dataset.page;
  document.querySelectorAll('.content').forEach(c => c.classList.remove('active'));

  // Show and animate the target page
  const target = document.getElementById('page-' + page);
  if (target) {
    target.classList.add('active');
    void target.offsetWidth;                      // force reflow to re-trigger animation
    target.style.animation = 'none';
    requestAnimationFrame(() => {
      target.style.animation = '';
      target.classList.add('fade-in');
    });
  }

  // Update topbar heading
  document.getElementById('topbar-heading').textContent = pageLabels[page] || page;
}

// ─────────────────────────────────────────
//  Delete a department row
// ─────────────────────────────────────────
function deleteRow(btn) {
  if (confirm('Remove this department?')) {
    btn.closest('tr').remove();
  }
}

// ─────────────────────────────────────────
//  Add a new department row
// ─────────────────────────────────────────
function addDepartment() {
  const name = prompt('Department name:');
  if (!name) return;

  const manager = prompt('Manager name:');
  if (!manager) return;

  const count = prompt('Staff count:');
  if (count === null) return;

  const tbody = document.getElementById('dept-tbody');
  const tr = document.createElement('tr');

  tr.innerHTML = `
    <td class="td-dept">${name}</td>
    <td class="td-mgr">${manager}</td>
    <td class="td-cnt">${parseInt(count) || 0}</td>
    <td>
      <button class="btn-del" onclick="deleteRow(this)" title="Delete">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14H6L5 6"/>
          <path d="M10 11v6M14 11v6"/>
          <path d="M9 6V4h6v2"/>
        </svg>
      </button>
    </td>`;

  tbody.appendChild(tr);
}

// ─────────────────────────────────────────
//  File input handler
// ─────────────────────────────────────────
function handleFile(e) {
  const file = e.target.files[0];
  if (!file) return;
  alert(`File "${file.name}" selected!\nConnect this to your PHP backend to process the import.`);
}

// ─────────────────────────────────────────
//  Logout handler
// ─────────────────────────────────────────
function handleLogout() {
  if (confirm('Are you sure you want to logout?')) {
    alert('Logged out. Redirect to your login page here.');
  }
}

// ─────────────────────────────────────────
//  Drag-and-drop on upload zone
// ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const zone = document.getElementById('upload-zone');
  if (!zone) return;

  zone.addEventListener('dragover', e => {
    e.preventDefault();
    zone.style.borderColor = 'var(--accent)';
  });

  zone.addEventListener('dragleave', () => {
    zone.style.borderColor = '';
  });

  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.style.borderColor = '';
    const file = e.dataTransfer.files[0];
    if (file) alert(`Dropped: "${file.name}"`);
  });
});
