// Elemek kiválasztása a DOM-ból
const form = document.querySelector(".signup form");
const continueBtn = form.querySelector(".button input");
const errorText = document.querySelector(".error-txt");

form.onsubmit = (e) => {
    e.preventDefault();
}

continueBtn.onclick = () => {
    // Jelszó ellenőrzése a kliens oldalon (opcionális)
    let password = form.querySelector("input[name='password']").value;
    if (password) {
        let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password)) {
            errorText.textContent = "A jelszó nem felel meg a követelményeknek!";
            errorText.style.display = "block";
            return;
        }
    }

    // Ajax
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "php/updateProcess.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                let data = xhr.response;
                if (data == "success") {
                    let userId = form.getAttribute("data-user-id");
                    location.href = "profile.php?user_id=" + userId;
                } else {
                    errorText.textContent = data;
                    errorText.style.display = "block";
                }
            }
        }
    }

    // Az adatokat el kell küldenünk az ajaxon keresztül a PHP-nek
    let formData = new FormData(form);
    xhr.send(formData);
}