// Hozzászólások megjelenítése/elrejtése gombbal
document.querySelectorAll('.toggle-comments').forEach(button => {
    let originalText = button.querySelector('.comment-count').textContent;
    button.addEventListener('click', function() {
        let postId = this.getAttribute('data-post-id');
        let commentsContainer = document.getElementById('comments-' + postId);
        let commentCountSpan = this.querySelector('.comment-count');
        let icon = this.querySelector('i'); 

        if (commentsContainer.style.display === 'none') {
            commentsContainer.style.display = 'block';
            icon.classList.remove('fa-comment');
            icon.classList.add('fa-chevron-up');
            commentCountSpan.textContent = "";
        } else {
            commentsContainer.style.display = 'none';
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-comment');
            commentCountSpan.textContent = originalText;
        }
    });
});

// Hozzászólás hozzáadása AJAX segítségével
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        let postId = this.getAttribute('data-post-id');
        let commentText = this.querySelector('textarea').value;
        let commentImage = this.querySelector('input[name="comment_image"]').files[0];

        let formData = new FormData();
        formData.append('comment_text', commentText);
        formData.append('post_id', postId);
        if (commentImage) formData.append('comment_image', commentImage);

        fetch("php/add-comment.php", {
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
                    title: 'Hálózati hiba a komment küldésekor.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#1e1e1e',
                    color: '#fff'
                });
            });
    });
});

