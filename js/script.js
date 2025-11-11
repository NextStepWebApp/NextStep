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
