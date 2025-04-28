let images = document.getElementsByClassName('fullscreen-image');

        Array.from(images).forEach(img => {
            img.addEventListener('click', function () {
                if (!document.fullscreenElement) {
                    if (this.requestFullscreen) {
                        this.requestFullscreen();
                    } else if (this.mozRequestFullScreen) {
                        this.mozRequestFullScreen();
                    } else if (this.webkitRequestFullscreen) {
                        this.webkitRequestFullscreen();
                    } else if (this.msRequestFullscreen) {
                        this.msRequestFullscreen();
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen(); document.getElementById
                    }
                }
            });
        });