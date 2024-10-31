<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="icon" href="/SnD_Shoppe-main/Assets/sndlogo.png" type="logo">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>S&D Fabrics</title>
</head>
<body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="#">
                <img src="/SnD_Shoppe-main/Assets/sndlogo.png" width="70px" alt="Logo"/> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <div class="mx-auto d-flex justify-content-center flex-grow-1">
                    <form class="search-bar" role="search">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="bi bi-search search-icon"></i>
                            </span>
                            <input class="form-control" type="search" placeholder="Search..." aria-label="Search" aria-describedby="basic-addon1">
                        </div>
                    </form>
                </div>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-black active" aria-current="page" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/shopping_cart.svg" alt="cart">
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/notifications.svg" alt="notif">
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-link-black" href="#">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/inbox.svg" alt="inbox">
                        </a>
                    </li>

                    <!-- New Account Dropdown Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="#">Account & Security</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="sndLandingpage.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Category Card -->
    <div class="category-card p-4">
        <h2 class="text-center mb-4">Category</h2> <!-- Ensure the header is directly in the card -->
        <div class="row justify-content-center">
            <div class="category col-4 col-md-2 text-center">
                <a href="https://www.facebook.com/surrealsoreaaal">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/Laces.jpg" alt="Lace" class="rounded-circle">
                </a>
                <p>LACES</p>
            </div>
            <div class="category col-4 col-md-2 text-center">
                <a href="#">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/beaded lace.jpg" alt="Beaded Lace" class="rounded-circle">
                </a>
                <p>BEADED LACE</p>
            </div>
            <div class="category col-4 col-md-2 text-center">
                <a href="#">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/sequins.jpg" alt="Sequins" class="rounded-circle">
                </a>
                <p>SEQUINS</p>
            </div>
            <div class="category col-4 col-md-2 text-center">
                <a href="#">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/silk.jpg" alt="Silk" class="rounded-circle">
                </a>
                <p>SILK</p>
            </div>
            <div class="category col-4 col-md-2 text-center">
                <a href="#">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/velvet.jpg" alt="Velvet" class="rounded-circle">
                </a>
                <p>VELVET</p>
            </div>
            <div class="category col-4 col-md-2 text-center">
                <a href="#">
                    <img src="/SnD_Shoppe-main/Assets/fabrics/satin.png" alt="Satin" class="rounded-circle">
                </a>
                <p>SATIN</p>
            </div>
        </div>
    </div>
    
    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <button>All</button>
        <button>Newest</button>
        <button>Popular</button>
        <button>Basta 1</button>
        <button>Basta 2</button>
    </div>

    <!-- Fabric Items -->
    <div class="fabric-items">
        <div class="fabric-card">
            <div class="fabric-content">
                <a href="product.html"><img src="/SnD_Shoppe-main/Assets/fabrics/gingham-blue.jpg" alt="Fabric 1"></a>
                <p>Gingham Blue</p>
            </div>
        </div>
        <div class="fabric-card">
            <div class="fabric-content">
                <a href="#"><img src="/SnD_Shoppe-main/Assets/fabrics/gingham-orange.jpg" alt="Fabric 2"></a>
                <p>Gingham Orange</p>
            </div>
        </div>
        <div class="fabric-card">
            <div class="fabric-content">
                <a href="#"><img src="/SnD_Shoppe-main/Assets/fabrics/gingham-purple.jpg" alt="Fabric 3"></a>
                <p>Gingham Purple</p>
            </div>
        </div>
        <div class="fabric-card">
            <div class="fabric-content">
                <a href="#"><img src="/SnD_Shoppe-main/Assets/fabrics/gingham-pink.jpg" alt="Fabric 4"></a>
                <p>Gingham Pink</p>
            </div>
        </div>
        <div class="fabric-card">
            <div class="fabric-content">
                <a href="#"><img src="/SnD_Shoppe-main/Assets/fabrics/gingham-yellow.jpg" alt="Fabric 5"></a>
                <p>Gingham Yellow</p>
            </div>
        </div>
        <!-- Add more fabric cards as needed -->
    </div>
</body>
</html>