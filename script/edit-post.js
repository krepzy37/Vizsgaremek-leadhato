document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editModal");
    


    // Szerkesztés gombok eseménykezelése
    document.querySelectorAll(".edit-post-btn").forEach(button => {
        button.addEventListener("click", function() {
            let postId = this.getAttribute("data-id");
            let title = this.getAttribute("data-title");
            let body = this.getAttribute("data-body");
            let image = this.getAttribute("data-image");

            // Betöltjük az adatokat az űrlapba
            document.getElementById("edit_post_id").value = postId;
            document.getElementById("edit_title").value = title;
            document.getElementById("edit_body").value = body;

            let imgElem = document.getElementById("current_post_image");
            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
            } else {
                imgElem.style.display = "none";
            }

            modal.style.display = "block"; // Modal megjelenítése



            window.addEventListener("click", (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        });

    });



    // AJAX-al frissítjük a posztot
    document.getElementById("editPostForm").addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(this);
        formData.append('remove_image', document.getElementById('remove_image').checked ? 'true' : 'false');

        fetch("php/update-post.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: data.success ? 'success' : 'error',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#1e1e1e',
                    color: '#fff',
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

                if (data.success) {
                    setTimeout(() => location.reload(), 3100);
                }
            })
            .catch(error => {
                console.error("Hiba:", error);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Hiba történt a poszt frissítésekor.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#1e1e1e',
                    color: '#fff'
                });
            });
    });
});