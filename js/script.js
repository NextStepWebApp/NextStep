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
