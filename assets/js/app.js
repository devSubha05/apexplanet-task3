// Bootstrap client-side validation (server-side validation is still the source of truth)
document.querySelectorAll('.needs-validation').forEach(form => {
  form.addEventListener('submit', e => {
    const pw = form.querySelector('#password'), cf = form.querySelector('#confirm');
    if (pw && cf) cf.setCustomValidity(pw.value !== cf.value ? 'Passwords do not match' : '');
    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    form.classList.add('was-validated');
  });
});

// Show / hide password
document.querySelectorAll('.toggle-pw').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = btn.parentElement.querySelector('input');
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.firstElementChild.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
});

// Delete confirmation popup
const modalEl = document.getElementById('deleteModal');
if (modalEl) {
  const modal = new bootstrap.Modal(modalEl);
  document.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', () => {
    document.getElementById('delete-id').value = b.dataset.id;
    document.getElementById('delete-name').textContent = b.dataset.name;
    modal.show();
  }));
}

// Profile picture: size/type check + live preview
const avatar = document.getElementById('avatar');
if (avatar) {
  avatar.addEventListener('change', () => {
    const f = avatar.files[0], err = document.getElementById('avatar-error');
    avatar.classList.remove('is-invalid');
    if (!f) return;
    const okType = ['image/jpeg', 'image/png', 'image/webp'].includes(f.type);
    if (!okType || f.size > Number(avatar.dataset.max)) {
      err.textContent = !okType ? 'Only JPG, PNG or WEBP images are allowed.' : 'Image must be 2 MB or smaller.';
      avatar.classList.add('is-invalid'); avatar.value = ''; return;
    }
    const wrap = document.getElementById('avatar-preview-wrap');
    wrap.innerHTML = '';
    const img = document.createElement('img');
    img.className = 'avatar'; img.width = 140; img.height = 140; img.alt = 'Preview';
    img.src = URL.createObjectURL(f);
    wrap.appendChild(img);
  });
}
