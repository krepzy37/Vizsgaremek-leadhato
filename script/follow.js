document.addEventListener('DOMContentLoaded', function() {
    const followButton = document.querySelector('.follow-button');

    if (followButton) {
        followButton.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const isFollowing = this.dataset.following === 'true';
            const action = isFollowing ? 'unfollow' : 'follow';

            fetch('php/follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `user_id=${userId}&action=${action}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.textContent = data.action === 'follow' ? 'Követés' : 'Követem';
                    this.dataset.following = data.action === 'unfollow' ? 'true' : 'false';
                } else {
                    console.error('Error:', data.error);
                    alert('Hiba történt a követés során.');
                }
            });
        });
    }
});