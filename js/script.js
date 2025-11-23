function togglePassword() {
  var passwordField = document.getElementById("password");
  var toggleText = document.querySelector(".toggle-password");
  if (passwordField.type === "password") {
    passwordField.type = "text";
    toggleText.textContent = "Hide";
  } else {
    passwordField.type = "password";
    toggleText.textContent = "Show";
  }
}

// Modal/Dialog functionality for all pages

document.querySelectorAll("[data-open-modal]").forEach(button => {
  button.addEventListener("click", () => {
    const dialog = button.nextElementSibling; 
    dialog.showModal();
  });
});

document.querySelectorAll("[data-close-modal]").forEach(button => {
  button.addEventListener("click", () => {
    const dialog = button.closest("dialog");
    dialog.close();
  });
});

// Close modal when clicking outside the dialog (on the backdrop)
document.querySelectorAll("[data-modal]").forEach(dialog => {
  dialog.addEventListener("click", e => {
    const rect = dialog.getBoundingClientRect();
    if (
      e.clientX < rect.left ||
      e.clientX > rect.right ||
      e.clientY < rect.top ||
      e.clientY > rect.bottom
    ) {
      dialog.close();
    }
  });
});



// Tab switching functionality
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        // Remove active class from all tabs and contents
        tabs.forEach(t => t.classList.remove('active'));
        tabContents.forEach(tc => tc.classList.remove('active'));

        // Add active class to clicked tab
        tab.classList.add('active');

        // Show corresponding content
        const tabName = tab.getAttribute('data-tab');
        document.getElementById(tabName).classList.add('active');
    });
});



// This is a function that is used in the settings, records page
function toggleRows(section, btn) {
    const rows = document.querySelectorAll('.hidden-rows-' + section);
    if (rows.length === 0) return;
    
    const isCurrentlyHidden = rows[0].classList.contains('hidden-rows');
    const count = btn.getAttribute('data-count');
    
    rows.forEach(row => {
        if (isCurrentlyHidden) {
            row.classList.remove('hidden-rows');
        } else {
            row.classList.add('hidden-rows');
        }
    });
    
    btn.textContent = isCurrentlyHidden ? 'Show Less' : `Show More (${count} more)`;
}
