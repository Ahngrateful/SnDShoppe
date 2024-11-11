<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/cart.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="icon" href="\SnD_Shoppe-main\PIC\sndlogo.png" type="logo">
    <title>Shopping Cart</title>
</head>

<body class="vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #f1e8d9; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <a class="navbar-brand fs-4" href="homepage.php">
                <img src="\SnD_Shoppe-main\PIC\sndlogo.png" width="70px" alt="Logo" />
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo01"
                aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
                        <a class="nav-link nav-link-black dropdown-toggle" href="#" id="accountDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="/SnD_Shoppe-main/Assets/svg(icons)/account_circle.svg" alt="account">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountDropdown">
                            <li>
                                <a class="dropdown-item" href="accountSettings.php">My Account</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="haveacc.php">Logout</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Cart Content -->

    <div class="header-container">
        <div class="card text-center">
            <div class="card-body">
                <h1 class="mb-0 custom-padding">SHOPPING CART</h1>
            </div>
        </div>
    </div>
    <div class="container my-5">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 col-md-12">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">
                                <input type="checkbox" id="select-all" />
                            </th>
                            <th scope="col">Product</th>
                            <th scope="col">Unit Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Total Price</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="checkbox" class="item-checkbox" />
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="/SnD_Shoppe-main/Assets/fabrics/gingham-blue.jpg" alt="Product Image" width="80" class="rounded me-3">
                                    <span>Gingham Blue</span>
                                </div>
                            </td>
                            <td class="unit-price">₱650.00</td>
                            <td>
                                <input type="number" class="form-control quantity-select" value="1" min="1" max = "9">
                            </td>
                            <td class="item-total-price"></td>
                            <td>
                                <button class="btn btn-danger btn-sm remove-item">Remove</button>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" class="item-checkbox" />
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="/SnD_Shoppe-main/Assets/fabrics/gingham-orange.jpg" alt="Product Image" width="80" class="rounded me-3">
                                    <span>Gingham Orange</span>
                                </div>
                            </td>
                            <td class="unit-price">₱650.00</td>
                            <td>
                                <input type="number" class="form-control quantity-select" value="2" min="1" max="9">
                            </td>
                            <td class="item-total-price"></td>
                            <td>
                                <button class="btn btn-danger btn-sm remove-item">Remove</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button class="btn btn-danger btn-sm" id="remove-selected">Remove Selected</button>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4 col-md-12">
                <div class="p-4 border rounded shadow-sm bg-light">
                    <h4 class="mb-4">Order Summary</h4>
                    <div class="d-flex justify-content-between">
                        <p>Subtotal</p>
                        <p class="subtotal fw-bold">₱0.00</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Shipping</p>
                        <p class="fw-bold">₱40.00</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Tax</p>
                        <p class="fw-bold">₱4.00</p>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5>Total</h5>
                        <h5 class="total-price fw-bold">₱0.00</h5>
                    </div>
                    <button class="btn btn-success btn-lg w-100 mt-4">Proceed to Checkout</button>
                    <button class="btn return-button w-100 mt-3" onclick="window.location.href='homepage.php'">Return</button>
                </div>
            </div>
        </div>
    </div>

    <script src="\SnD_Shoppe-main\product.js"></script>

</body>

</html>
