const signupForm = document.querySelector(".signup form");
const continueBtn = signupForm.querySelector(".button input");
const errorText = signupForm.querySelector(".error-txt");

signupForm.onsubmit = (e) => {
    e.preventDefault();
};

continueBtn.onclick = () => {
    if (!validateForm()) {
        return;
    }

    const formData = new FormData(signupForm);

    fetch("php/signupProcess.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            Swal.fire({
                icon: 'success',
                title: 'Sikeres regisztráció!',
                text: data.message,
                confirmButtonColor: '#4CAF50',
                background: '#1e1e1e',
                color: '#fff'
            }).then(() => {
                location.href = "index.php";
            });
        } else {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: data.message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1e1e1e',
                color: '#fff'
            });
        }
    })
    .catch(error => {
        console.error("Hiba történt:", error);
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Hiba történt a kérés feldolgozása közben.',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#1e1e1e',
            color: '#fff'
        });
    });
};

function validateForm() {
    const usernameField = signupForm.querySelector("input[name='username']");
    const emailField = signupForm.querySelector("input[name='email']");
    const passwordField = signupForm.querySelector("input[name='password']");
    const aszfCheckbox = signupForm.querySelector("input[name='aszf_elfogadva']");
    const adatkezelesCheckbox = signupForm.querySelector("input[name='adatkezeles_elfogadva']");

    if (!usernameField || !emailField || !passwordField || !aszfCheckbox || !adatkezelesCheckbox) {
        console.error("Hiba: Hiányzó input mezők!");
        return false;
    }

    const username = usernameField.value.trim();
    const email = emailField.value.trim();
    const password = passwordField.value.trim();

    if (!username || !email || !password) {
        errorText.style.display = "block";
        errorText.textContent = "Minden mezőt ki kell tölteni!";
        return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errorText.style.display = "block";
        errorText.textContent = "Érvényes e-mail címet adjon meg!";
        return false;
    }

    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        errorText.style.display = "block";
        errorText.textContent = "A jelszónak legalább 8 karakter hosszúnak kell lennie, és tartalmaznia kell kis- és nagybetűt, számot és speciális karaktert!";
        return false;
    }

    if (!aszfCheckbox.checked || !adatkezelesCheckbox.checked) {
        errorText.style.display = "block";
        errorText.textContent = "Kérjük, fogadja el az Általános Szerződési Feltételeket és az Adatkezelési Tájékoztatót!";
        return false;
    }

    errorText.style.display = "none";
    return true;
}