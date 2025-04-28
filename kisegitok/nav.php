<body ng-app="carApp" ng-controller="CarController">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="index.php"><img src="php/img/logoCT.png" alt="Logo" style="max-width: 65px;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div style="padding-left: 20px;" class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
  <li class="nav-item responsive-wrapper">
    <a href="index.php" class="btn-shine main-page-btn nav-link">Főoldal</a>

    <div class="search-container" style="position: relative; margin-bottom: 5px;">
      <div class="group">
        <svg viewBox="0 0 24 24" aria-hidden="true" class="search-icon">
          <g>
            <path d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"></path>
          </g>
        </svg>
        <input autocomplete="off" type="text" id="query" name="query" placeholder="Felhasználónév" class="inputS" onkeyup="searchUsers(this.value)">
      </div>
      <div id="searchResults"></div>
    </div>
  </li>
</ul>

    <!-- Keresési sáv -->
    <div class="d-flex flex-column" style="gap: 10px;">
        <?php if (isset($_SESSION['id'])):
            include_once "php/connect.php";
            $id = $_SESSION['id'];
            $query = mysqli_query($dbconn, "SELECT username, role, profile_picture_url FROM users WHERE id = $id");
            $loggedInUser = mysqli_fetch_assoc($query);

            if (isset($_GET['query'])) {
                $query = mysqli_real_escape_string($dbconn, $_GET['query']);
                $result = mysqli_query($dbconn, "SELECT id, username, profile_picture_url FROM users WHERE username LIKE '%$query%' LIMIT 5");

                if (mysqli_num_rows($result) > 0) {
                    echo '<ul>';
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<li>
                            <a href="profile.php?user_id=' . $row['id'] . '">
                                <img src="php/img/' . htmlspecialchars($row['profile_picture_url']) . '" alt="Profilkép">
                                ' . htmlspecialchars($row['username']) . '
                            </a>
                          </li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p style="padding: 10px;">Nincs találat</p>';
                }
            }
        ?>
            <?php if ($loggedInUser['role'] == 2): ?>
                <a href="admin/moderator.php" class="btn-shine main-page-btn nav-link me-5" id="modPanel">Moderátori Panel</a>
            <?php endif; ?>
            <div class="user-actions-wrapper">
  <a href="php/logoutProcess.php" class="btn-shine main-page-btn nav-link me-5" style="padding: 5px 0;">
    Kilépés
  </a>
  <a href="profile.php?user_id=<?php echo $id; ?>" class="btn-shine main-page-btn nav-link">
    <img src="php/img/<?php echo htmlspecialchars($loggedInUser['profile_picture_url']); ?>" alt="Profilkép" style="width:30px; height:30px; border-radius:50%; object-fit: cover;">
    <?php echo htmlspecialchars($loggedInUser['username']); ?>
  </a>
</div>

        <?php else:
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page !== 'login.php' && $current_page !== 'signup.php') {
                echo '<a href="login.php" class="btn-shine main-page-btn nav-link" id="belep">Belépés</a>';
            }
        endif; ?>
    </div>
</div>

        </div>
    </nav>
    <div class="search-container" style="position: relative;">

        <div id="searchResults"></div>
        <div id="searchResults" class="position-absolute w-100 " style="top: 70px; z-index: 9998;"></div>
        <div class="menu-btn">
            <div class="menu-btn__burger">
                <i class="fas fa-car"></i>

            </div>
        </div>
        <style>

        </style>
        <!-- Oldalsó Menü -->
        <div class="side-menu">
            <div class="offcanvas-body">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li style="margin-top: 20px; margin-left: 10px; color: #4CAF50; margin-bottom:10px">
                        <h2>Márkák</h2>
                    </li>
                    <li ng-repeat="brand in brands track by brand.brand_id" class="nav-item" ng-class="{'active': selectedBrand === brand.brand_id}">
                        <a href="#{{brand.brand_id}}Submenu" data-bs-toggle="collapse" class="nav-link text-light" aria-expanded="false" ng-click="selectBrand(brand.brand_id)">
                            <img ng-src="php/img/carlogos/{{brand.logo_url}}" alt="{{brand.name}} logo" class="me-2" style="height: 24px;">
                            {{brand.name}}
                        </a>
                        <ul class="collapse list-unstyled ps-3" id="{{brand.brand_id}}Submenu">
                            <li ng-repeat="model in brand.models" class="nav-item">
                                <a href="car.php?brand={{brand.name}}&model={{model.name}}" class="nav-link text-light">▶ {{model.name}}</a>
                            </li>
                        </ul>
                        <hr>
                    </li>
                </ul>
            </div>
        </div>
        <div class=" right-menu">
            <div class="offcanvas-body">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li style="margin-top: 20px; margin-left: 10px; color: #4CAF50; margin-bottom: 10px;">
                        <h2>Barátok</h2>
                    </li>
                    <?php if (isset($_SESSION['id'])): ?>
                        <?php
                        $user_id = $_SESSION['id'];
                        $mutual_followers_query = "SELECT u.id, u.username, u.profile_picture_url, u.status FROM users u JOIN follows f1 ON u.id = f1.following_user_id JOIN follows f2 ON u.id = f2.followed_user_id WHERE f1.followed_user_id = ? AND f2.following_user_id = ?";
                        $mutual_followers_stmt = $dbconn->prepare($mutual_followers_query);
                        $mutual_followers_stmt->bind_param("ii", $user_id, $user_id);
                        $mutual_followers_stmt->execute();
                        $mutual_followers_result = $mutual_followers_stmt->get_result();
                        while ($mutual_follower = $mutual_followers_result->fetch_assoc()):
                        ?>
                            <li class="nav-item" style="position: relative;">
                                <a href="#" class="nav-link text-light d-flex align-items-center user-dropdown-toggle" data-user-id="<?php echo $mutual_follower['id']; ?>">
                                    <img src="php/img/<?php echo htmlspecialchars($mutual_follower['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($mutual_follower['username']); ?> profilképe" class="me-2" style="height: 40px; width: 40px; border-radius: 50%; object-fit:cover">
                                    <?php echo htmlspecialchars($mutual_follower['username']); ?>
                                    <div class="status-dot <?php echo ($mutual_follower['status'] === 1) ? 'online' : 'offline'; ?>"></div>
                                </a>
                                <div class="user-dropdown" id="user-dropdown-<?php echo $mutual_follower['id']; ?>" style="display: none; background-color: #333; padding: 5px; margin-left: 50px; margin-top: 5px; border-radius: 5px;">
                                    <a href="profile.php?user_id=<?php echo $mutual_follower['id']; ?>" class="text-light" style="display: inline-block; padding: 5px;"><i class="fas  fa-user
"></i> Profil</a> |
                                    <a href="#" class="chat-btn text-light" data-user-id="<?php echo $mutual_follower['id']; ?>" style="display: inline-block; padding: 5px;"><i class="fa-solid fa-comments"></i> Üzenetek</a>
                                </div>
                                <div id="chat-popup-<?php echo $mutual_follower['id']; ?>" class="chat-popup">
                                    <div class="chat-popup-header">
                                        <span><img src="php/img/<?php echo htmlspecialchars($mutual_follower['profile_picture_url']); ?>"
                                                alt="Profilkép" width="40" height="40" style="border-radius: 50%; object-fit:cover">
                                            <?php echo htmlspecialchars($mutual_follower['username']); ?></span>
                                        <button type="button" class="btn btn-outline-danger close-chat-btn">X</button>
                                    </div>
                                    <div class="chat-popup-body" id="chat-box-<?php echo $mutual_follower['id']; ?>">
                                    </div>
                                    <div class="chat-popup-footer">
                                        <form class="typing-area row align-items-center" data-user-id="<?php echo $mutual_follower['id']; ?>">
                                            <div class="col">
                                                <div class="input-group">
                                                    <input type="text" autocomplete="off" name="message" class="form-control" placeholder="Írj üzenetet...">
                                                    <label for="file-upload-<?php echo $mutual_follower['id']; ?>" class="input-group-text photo-icon-label">
                                                        <i class="fas fa-camera"></i>
                                                    </label>
                                                    <input type="file" name="image" id="file-upload-<?php echo $mutual_follower['id']; ?>" style="display: none;">
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <button type="submit" class="btn btn-success send-btn">
                                                    <i class="fab fa-telegram-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </li>
                            <hr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-light">Jelentkezz be a barátok megtekintéséhez!</a>
                        </li>
                    <?php endif; ?>

                    <li style="margin-top: 20px; margin-left: 10px; color: #4CAF50; margin-bottom: 10px;">
                        <h2>Követések</h2>
                    </li>
                    <?php if (isset($_SESSION['id'])): ?>
                        <?php
                        $user_id = $_SESSION['id'];
                        $followed_users_query = "SELECT u.id, u.username, u.profile_picture_url, u.status FROM users u JOIN follows f1 ON u.id = f1.followed_user_id WHERE f1.following_user_id = ? AND u.id NOT IN (SELECT f2.following_user_id FROM follows f2 WHERE f2.followed_user_id = ?)";
                        $followed_users_stmt = $dbconn->prepare($followed_users_query);
                        $followed_users_stmt->bind_param("ii", $user_id, $user_id);
                        $followed_users_stmt->execute();
                        $followed_users_result = $followed_users_stmt->get_result();
                        while ($followed_user = $followed_users_result->fetch_assoc()):
                        ?>
                            <li class="nav-item" style="position: relative;">
                                <a href="profile.php?user_id=<?php echo $followed_user['id']; ?>" class="nav-link text-light d-flex align-items-center">
                                    <img src="php/img/<?php echo htmlspecialchars($followed_user['profile_picture_url']); ?>" alt="<?php echo htmlspecialchars($followed_user['username']); ?> profilképe" class="me-2" style="height: 40px; width:40px; border-radius: 50%; object-fit:cover">
                                    <?php echo htmlspecialchars($followed_user['username']); ?>
                                    <div class="status-dot <?php echo ($followed_user['status'] === 1) ? 'online' : 'offline'; ?>"></div>
                                </a>
                            </li>
                            <hr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-light">Jelentkezz be a követett felhasználók megtekintéséhez!</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="right-menu-btn">
            <div class="menu-btn__burger">
                <i class="fas fa-users"></i>
            </div>
        </div>


        <style>
            .chat-popup {
                display: none;
                /* Alapértelmezetten rejtett */
                position: fixed;
                bottom: 5px;
                right: 15px;
                border: 1px solid #ccc;
                background: #333;
                width: 420px;
                height: 410px;
                z-index: 1000;
                border-radius: 8px;
            }

            .chat-popup-header {
                background: #1a1a1a;
                padding: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: #fff;
                border-radius: 8px;
            }

            .chat-popup-body {
                height: 300px;
                overflow-y: auto;
                padding: 10px;
            }

            .chat-popup-footer {
                padding: 20px 10px;
                height: 200px;
                margin-top: -22px;

            }

            .chat.incoming {
                display: flex;
                /* Flexbox használata */
                align-items: flex-start;
                /* Felülre igazítás */
            }

            .profile-container {
                margin-right: 10px;
                /* Távolság az üzenettől */
            }

            .profile-picture_dm {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                margin-right: 2px;
                object-fit: cover;
            }

            .chat {
                margin-bottom: 10px;
                padding: 10px;
                border-radius: 8px;
                width: fit-content;
                max-width: 70%;
            }

            .chat.outgoing {
                background-color: #dcf8c6;
                /* Világoszöld háttér a saját üzeneteknek */
                align-self: flex-end;
                /* Jobb oldali igazítás */
                margin-left: auto;
                /* Jobb oldali igazítás */
                border: 1px solid #c6e4b5;
                /* Keret hozzáadása */
            }



            .chat-box {
                display: flex;
                flex-direction: column;
                
            }

            .user-info_dm {
                display: flex;
                align-items: center;
                margin-bottom: 5px;
            }

            .chat.incoming {
                display: flex;
                /* Flexbox használata */
                align-items: flex-start;
                /* Felülre igazítás */
                background-color: #333;
            }

            .profile-container_dm {
                margin-right: 10px;
                /* Távolság az üzenettől */
            }

            .profile-picture_dm {
                width: 30px;
                /* Példa méret */
                height: 30px;
                /* Példa méret */
                border-radius: 50%;
                /* Kör alakú profilkép */
            }

            .chat.incoming .details {
                /* Keret csak a details osztályra */
                border: 1px solid #e0e0e0;
                /* Keret hozzáadása */
                padding: 10px;
                border-radius: 8px;
                background-color: #f0f0f0;
                /* Világosszürke háttér a bejövő üzeneteknek */
            }

            .chat-popup-footer .typing-area {
                display: flex;
                align-items: center;
                padding: 8px;

            }

            .chat-popup-footer .input-container {
                display: flex;
                flex-grow: 1;
                border: 1px solid #ccc;
                border-radius: 5px;
                
                margin-right: 8px;
                align-items: center;
            }

            .chat-popup-footer .input-field {
                border: none;
                outline: none;
                padding: 8px;
                flex-grow: 1;
            }

            .chat-popup-footer .photo-icon {
                cursor: pointer;
                margin-left: 2px;
                color: #777;
            }

            .chat-popup-footer .send-btn {
                background-color: #4CAF50;
                /* Példa szín */
                color: white;
                border: none;
                padding: 8px 12px;
                border-radius: 5px;
                cursor: pointer;
                outline: none;
            }

            .chat-popup-footer .send-btn:hover {
                background-color: darkgreen;
                /* Sötétebb szín hover-re */
            }

            @media (max-width: 768px) {
        .chat-popup {
            bottom: 0;
            left: 0;
            right: 0;
            top: 0;
            width: 100%;
            height: 100%;
            border: none; /* Eltávolítjuk a bordert teljes képernyőn */
            border-radius: 0; /* Eltávolítjuk a lekerekített sarkokat */
        }

        .chat-popup-body {
            height: calc(100% - 60px - 56px); /* Teljes magasság - header magasság - footer magasság */
        }
        .chat-popup-header{
            position: relative;
            top: 78px;
            margin-bottom: 100px;
        }
        .dmKep{
            max-width: 180px;
        }
        .chat-popup-footer{
            position: relative;
            top:-50px;
        }
        .chat-popup-body{
            position: relative;
            max-height: 80%;
            bottom: 20px;
        }
        
    }
        </style>
        <style>
  .responsive-wrapper {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
  }

  @media (max-width: 768px) {
    .responsive-wrapper {
      flex-direction: column;
      align-items: flex-start;
    }

    .responsive-wrapper .main-page-btn,
    .responsive-wrapper .search-container {
      width: 100%;
    }
  }
  .user-actions-wrapper {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  @media (max-width: 768px) {
    .user-actions-wrapper {
      flex-direction: column;
      align-items: flex-start;
    }

    .user-actions-wrapper a {
      width: 100%;
    }
  }
</style>

        <main class="flex-grow-1 p-3">


            <script>
                let app = angular.module('carApp', []);

                app.controller('CarController', function($scope, $http) {
                    // Az API lekérése, hogy betöltsük a márkákat és modelleket
                    $http.get('php/getBrands.php').then(function(response) {

                        // Az adatokat hozzárendeljük az $scope.brands változóhoz
                        $scope.brands = response.data;
                    }, function(error) {
                        console.error('Hiba történt a márkák lekérésekor:', error);
                    });

                });



                document.addEventListener('DOMContentLoaded', function() {
    const menuBtn = document.querySelector('.menu-btn');
    const sideMenu = document.querySelector('.side-menu');
    const rightMenu = document.querySelector('.right-menu');
    const rightMenuBtn = document.querySelector('.right-menu-btn');
    const chatPopups = document.querySelectorAll('.chat-popup');
    let overlay = document.createElement('div');
    overlay.className = 'overlay';
    document.body.appendChild(overlay);

    function updateMenuButtonVisibility() {
        const isMobile = window.innerWidth <= 768;
        let isChatVisible = false;
        chatPopups.forEach(popup => {
            if (getComputedStyle(popup).display !== 'none') {
                isChatVisible = true;
            }
        });

        if (isMobile && isChatVisible) {
            if (menuBtn) menuBtn.style.display = 'none';
            if (rightMenuBtn) rightMenuBtn.style.display = 'none';
        } else {
            if (menuBtn) menuBtn.style.display = 'block';
            if (rightMenuBtn) rightMenuBtn.style.display = 'block';
        }
    }

    // Kezdeti láthatóság beállítása
    updateMenuButtonVisibility();

    // Ablak átméretezésének figyelése
    window.addEventListener('resize', updateMenuButtonVisibility);

    // Chat ablakok megnyitásának figyelése (ha dinamikusan nyílnak)
    const chatButtons = document.querySelectorAll('.chat-btn');
    chatButtons.forEach(button => {
        button.addEventListener('click', updateMenuButtonVisibility);
    });

    // Chat ablakok bezárásának figyelése (ha van bezáró gomb)
    const closeChatButtons = document.querySelectorAll('.close-chat-btn');
    closeChatButtons.forEach(button => {
        button.addEventListener('click', updateMenuButtonVisibility);
    });

    // Bal oldali menü eseménykezelői
    if (menuBtn) {
        menuBtn.addEventListener('click', function() {
            if (rightMenu.classList.contains('active') && window.innerWidth <= 768) {
                rightMenu.classList.remove('active');
                if (rightMenuBtn) rightMenuBtn.classList.remove('active');
            }
            sideMenu.classList.toggle('active');
            menuBtn.classList.toggle('active');
            overlay.classList.toggle('active');
            updateMenuButtonVisibility();
        });
    }

    // Jobb oldali menü eseménykezelői
    if (rightMenuBtn) {
        rightMenuBtn.addEventListener('click', function() {
            if (sideMenu.classList.contains('active') && window.innerWidth <= 768) {
                sideMenu.classList.remove('active');
                if (menuBtn) menuBtn.classList.remove('active');
            }
            rightMenu.classList.toggle('active');
            rightMenuBtn.classList.toggle('active');
            overlay.classList.toggle('active');
            updateMenuButtonVisibility();
        });

        const rightMenuLinks = document.querySelectorAll('.right-menu nav ul li a');
        rightMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                rightMenu.classList.remove('active');
                rightMenuBtn.classList.remove('active');
                overlay.classList.remove('active');
                updateMenuButtonVisibility();
            });
        });
    }

    overlay.addEventListener('click', function() {
        if (sideMenu) {
            sideMenu.classList.remove('active');
            if (menuBtn) menuBtn.classList.remove('active');
        }
        if (rightMenu) {
            rightMenu.classList.remove('active');
            if (rightMenuBtn) rightMenuBtn.classList.remove('active');
        }
        overlay.classList.remove('active');
        updateMenuButtonVisibility();
    });

    if (menuBtn) {
        const menuLinks = document.querySelectorAll('.side-menu nav ul li a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                sideMenu.classList.remove('active');
                menuBtn.classList.remove('active');
                overlay.classList.remove('active');
                updateMenuButtonVisibility();
            });
        });
    }

    const userDropdownToggles = document.querySelectorAll('.user-dropdown-toggle');
    userDropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();

            const userId = this.getAttribute('data-user-id');
            const dropdown = document.getElementById(`user-dropdown-${userId}`);

            document.querySelectorAll('.user-dropdown').forEach(otherDropdown => {
                if (otherDropdown.id !== `user-dropdown-${userId}`) {
                    otherDropdown.style.display = 'none';
                }
            });

            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            updateMenuButtonVisibility();
        });
    });

    document.addEventListener('click', function(event) {
        if (!event.target.classList.contains('user-dropdown-toggle')) {
            document.querySelectorAll('.user-dropdown').forEach(dropdown => {
                if (dropdown.style.display === 'block') {
                    dropdown.style.display = 'none';
                }
            });
        }
        updateMenuButtonVisibility();
    });
});

function searchUsers(query) {
    if (query.length === 0) {
        document.getElementById("searchResults").innerHTML = "";
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("searchResults").innerHTML = this.responseText;
        }
    };
    xhr.open("GET", "search_users.php?query=" + query, true);
    xhr.send();
}
            </script>
            <script src="script/chat-popup.js"></script>