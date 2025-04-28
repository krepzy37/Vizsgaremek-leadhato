document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("editCommentModal");
    let editForm = document.getElementById("editCommentForm");

    document.querySelectorAll(".edit-comment-btn").forEach(button => {
        button.addEventListener("click", function() {
            let commentId = this.getAttribute("data-id");
            let text = this.getAttribute("data-text");
            let image = this.getAttribute("data-image");

            document.getElementById("edit_comment_id").value = commentId;
            document.getElementById("edit_comment_text").value = text;

            let imgElem = document.getElementById("current_comment_image");
            let deleteImageContainer = document.getElementById("delete_image_container");
            let deleteImageCheckbox = document.getElementById("delete_comment_image");

            if (image) {
                imgElem.src = image;
                imgElem.style.display = "block";
                deleteImageContainer.style.display = "block"; // Megjeleníti a törlés checkboxot
            } else {
                imgElem.style.display = "none";
                deleteImageContainer.style.display = "none"; // Elrejti a törlés checkboxot
            }

            deleteImageCheckbox.checked = false; // Alapértelmezett érték

            modal.style.display = "block";

            window.addEventListener("click", (event) => {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        });
    });

    

    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // AJAX beküldés
    editForm.addEventListener("submit", function(event) {
        event.preventDefault();

        let formData = new FormData(editForm);
        fetch("php/update-comment.php", {
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
                    modal.style.display = "none";
                    setTimeout(() => location.reload(), 3100);
                }
            })
            .catch(error => {
                console.error("Hiba:", error);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Hiba történt a komment frissítésekor.',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#1e1e1e',
                    color: '#fff'
                });
            });
    });
});








document.addEventListener("DOMContentLoaded", function () {
    const voteButtons = document.querySelectorAll(".upvote, .downvote");

    voteButtons.forEach(button => {
        button.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const type = this.getAttribute("data-type");
            const voteType = this.classList.contains("upvote") ? "upvote" : "downvote";

            fetch("php/vote.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${id}&vote_type=${voteType}&type=${type}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateVoteButtons(id, type, voteType);
                    updateScore(id, type); // Azonnal frissíti a szavazatok számát
                }
            })
            .catch(error => console.error("Hálózati hiba: ", error));
        });
    });

    // Betöltéskor frissítjük a szavazatokat
    fetch("php/get-user-vote.php")
    .then(response => response.json())
    .then(data => {
        if (data && data.success) {
            // Feldolgozzuk a post_votes tömböt
            if (Array.isArray(data.post_votes)) {
                data.post_votes.forEach(vote => {
                    let id = vote.post_id;
                    let type = "post";
                    let voteType = vote.vote_type;

                    let button = document.querySelector(`.${voteType}[data-id='${id}'][data-type='${type}']`);
                    if (button) {
                        button.classList.add("voted");
                    }
                });
            }

            // Feldolgozzuk a comment_votes tömböt
            if (Array.isArray(data.comment_votes)) {
                data.comment_votes.forEach(vote => {
                    let id = vote.comment_id;
                    let type = "comment";
                    let voteType = vote.vote_type;

                    let button = document.querySelector(`.${voteType}[data-id='${id}'][data-type='${type}']`);
                    if (button) {
                        button.classList.add("voted");
                    }
                });
            }
        } else {
            console.error("Érvénytelen válasz a get-user-vote.php-tól:", data);
        }
    })
    .catch(error => console.error("Hálózati hiba:", error));
});


function updateVoteButtons(id, type, voteType) {
    const upvoteButton = document.querySelector(`.upvote[data-id='${id}'][data-type='${type}']`);
    const downvoteButton = document.querySelector(`.downvote[data-id='${id}'][data-type='${type}']`);

    upvoteButton.classList.remove("voted");
    downvoteButton.classList.remove("voted");

    if (voteType === "upvote") {
        upvoteButton.classList.add("voted");
    } else {
        downvoteButton.classList.add("voted");
    }
}

//  Frissíti a szavazatok számát az oldalon
function updateScore(id, type) {
    fetch("php/get-vote_score.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&type=${type}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const scoreElement = document.getElementById(type === "post" ? `post-score-${id}` : `comment-score-${id}`);
            scoreElement.textContent = data.score; // Frissíti azonnal a pontszámot
        }
    })
    .catch(error => console.error("Hálózati hiba: ", error));
}
