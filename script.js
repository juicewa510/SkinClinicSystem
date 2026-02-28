function showForm(formId) {
    document.querySelectorAll('.form-box').forEach(form => {form.classList.remove('active');});
    document.getElementById(formId).classList.add('active');
}

// Highlight active menu item
const navLinks = document.querySelectorAll('.menu a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
    });
});

// Sidebar toggle for mobile
const sidebar = document.querySelector('.sidebar');
const toggleBtn = document.createElement('button');
toggleBtn.innerHTML = '☰';
toggleBtn.classList.add('toggle-btn');
document.body.prepend(toggleBtn);

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
});

// Auto update date
function updateDate() {
    const dateBox = document.getElementById('dateBox');
    const today = new Date();
    const formatted = today.toISOString().split('T')[0];
    dateBox.textContent = formatted;
}
setInterval(updateDate, 60000);

function openPopup() {
  document.getElementById('loginPopup').style.display = 'flex';
}

function closePopup() {
  document.getElementById('loginPopup').style.display = 'none';
}