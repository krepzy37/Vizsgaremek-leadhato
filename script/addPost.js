document.getElementById("postForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);

    fetch("php/add-post.php", {
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
                title: 'Hálózati hiba',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#1e1e1e',
                color: '#fff'
            });
        });
});