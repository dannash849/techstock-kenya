function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.addEventListener('click', e => { if(e.target.classList.contains('modal-overlay')) e.target.classList.remove('open'); });
document.querySelectorAll('.alert').forEach(a => { setTimeout(() => { a.style.opacity='0'; a.style.transition='0.5s'; setTimeout(()=>a.remove(),500); }, 5000); });
document.querySelectorAll('.confirm-btn').forEach(b => { b.addEventListener('click', e => { if(!confirm(b.dataset.confirm||'Are you sure?')) e.preventDefault(); }); });
