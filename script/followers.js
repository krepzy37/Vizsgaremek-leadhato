document.addEventListener('DOMContentLoaded', function () {
    const followersLink = document.querySelector('.followers-link');
    const followingLink = document.querySelector('.following-link');
    const followersModal = document.getElementById('followers-modal');
    const followingModal = document.getElementById('following-modal');
    const followersList = document.getElementById('followers-list');
    const followingList = document.getElementById('following-list');
   

    followersLink.addEventListener('click', function () {
        showFollowList(this.dataset.userId, 'followers');
    });

    followingLink.addEventListener('click', function () {
        showFollowList(this.dataset.userId, 'following');
    });

    // Modal bezárása, ha a háttérre kattintanak
    window.addEventListener("click", (event) => {
        if (event.target === followersModal) {
            followersModal.style.display = "none";
        }
    });


    // Modal megjelenítése
    function showFollowList(userId, type) {
        fetch(`php/followers.php?user_id=${userId}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById("followers-list");
                list.innerHTML = '';
                data.forEach(user => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `<a href="profile.php?user_id=${user.id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                        <img src="php/img/${user.profile_picture_url}" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
                                        ${user.username}
                                      </a>`;
                    list.appendChild(listItem);
                });
                followersModal.style.display = 'block';
            });
    }



});