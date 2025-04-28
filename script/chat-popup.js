document.addEventListener('DOMContentLoaded', function() {
    let activeChatUserId = null; // Aktív chat felhasználó ID-jának tárolása

    const chatBtns = document.querySelectorAll('.chat-btn');
    chatBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            const chatPopup = document.getElementById(`chat-popup-${userId}`);

            // Ha van aktív chat ablak, és az nem az aktuális, zárjuk be
            if (activeChatUserId && activeChatUserId !== userId) {
                const activeChatPopup = document.getElementById(`chat-popup-${activeChatUserId}`);
                if (activeChatPopup) {
                    activeChatPopup.style.display = 'none';
                }
            }

            chatPopup.style.display = 'block';
            loadChatContent(userId, true);
            activeChatUserId = userId; // Frissítjük az aktív chat felhasználó ID-ját
        });
    });

    const closeChatBtns = document.querySelectorAll('.close-chat-btn');
    closeChatBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.closest('.chat-popup').style.display = 'none';
            activeChatUserId = null; // Töröljük az aktív chat felhasználó ID-ját
        });
    });

    function loadChatContent(userId, initialLoad = false) {
        const chatBox = document.getElementById(`chat-box-${userId}`);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/get-chat.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status === 200) {
                const isScrolledToBottom = chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight;
                chatBox.innerHTML = this.responseText;
                if (initialLoad || isScrolledToBottom) {
                    scrollToBottom(chatBox);
                }
            }
        };
        xhr.send(`incoming_id=${userId}`);
    }

    function sendMessage(form, userId) {
        const formData = new FormData(form);
        formData.append('incoming_id', userId);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'php/insert-chat.php', true);
        xhr.onload = function() {
            if (this.status === 200) {
                form.reset();
                loadChatContent(userId);
            }
        };
        xhr.send(formData);
    }

    const typingAreas = document.querySelectorAll('.typing-area');
    typingAreas.forEach(area => {
        area.addEventListener('submit', function(e) {
            e.preventDefault();
            const userId = this.getAttribute('data-user-id');
            sendMessage(this, userId);
        });
    });

    function scrollToBottom(element) {
        element.scrollTop = element.scrollHeight;
    }

    setInterval(function() {
        typingAreas.forEach(area => {
            const userId = area.getAttribute('data-user-id');
            loadChatContent(userId);
        });
    }, 500);
});