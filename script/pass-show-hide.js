const pswrField = document.getElementById("pass");
const toggleBtn = document.getElementById("togglePassword");

if (pswrField.type === "password") {
    toggleBtn.classList.add("fa-eye");
} else {
    toggleBtn.classList.add("fa-eye-slash");
}

toggleBtn.onclick = () => {
    if (pswrField.type === "password") {
        pswrField.type = "text";
        toggleBtn.classList.remove("fa-eye");
        toggleBtn.classList.add("fa-eye-slash");
    } else {
        pswrField.type = "password";
        toggleBtn.classList.remove("fa-eye-slash");
        toggleBtn.classList.add("fa-eye");
    }
};