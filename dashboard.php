<?php
require_once 'db/db_connection.php';
session_start();
$username = $_SESSION['username'];
$query = "SELECT name,image_url,dob,email,phone,gender,address,country FROM regforms WHERE username='$username'";
$result = pg_query($conn, $query);
if ($result) {
  $row = pg_fetch_assoc($result);
  $img = $row['image_url'];
}
$query2 = "SELECT * FROM regforms";
$result2 = pg_query($conn, $query2);
if ($result2) {
  $num = pg_num_rows($result2);
}
$session_timeout = 5 * 60; // 5 minutes in seconds

// Check if session variable last_activity exists
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
  // Session expired, destroy session
  session_unset();
  session_destroy();

  // Redirect to error page
  header("Location: error_page.php");
  exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="css/adminlte.css">
  <link rel="stylesheet" href="css/adminlte.min.css">
  <style>
    /* General styles */
    body {
      background-color: #001f3f;
      /* Dark background color */
      color: #fff;
      /* White text color */
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    main,
    footer {
      padding: 10px;
    }

    a {
      text-decoration: none;
      color: #fff;
    }

    /* Main content styles */
    main {
      padding-top: 20px;
      /* Added to adjust content position */
    }


    .profile-pic {
      width: 10vh;
      height: 10vh;
      border-radius: 50%;
      object-fit: cover;
      margin-left: 100px;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .navbar {
      animation: fadeInDown 1s ease;
    }

    @keyframes fadeInDown {
      0% {
        opacity: 0;
        transform: translateY(-50px);
      }

      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }


    footer a {
      color: #fff;
    }

    nav {
      background-color: #001f2e;
      /* Dark background color */
      color: #fff;
    }

    .navbar-toggler-icon {
      /* background-color: #fff; Toggler color */
      color: #fff;
      align-items: center;
    }

    /* Add this CSS to your existing styles */
    .main-content {
      padding-bottom: 70px;
      /* Adjust this value to match the height of your footer */
    }

    footer {
      background-color: #001f2e;
      color: #fff;
      padding: 10px;
      text-align: center;
      position: fixed;
      bottom: 0;
      width: 100%;
    }

    /* Add this media query for smaller screens */
    @media (max-width: 768px) {
      .profile-pic {
        margin-left: 10px;
        /* Adjust as needed */
      }
    }

    .profilepic {
      width: 30%;
      height: 30%;
      border-radius: 50%;
      object-fit: cover;
  
    }

    .text {
      text-align: center;
      color: #000;
    }

    .text h2 {
      margin-top: 10px;
      font-size: 24px;
    }

    .text p {
      margin: 5px 0;
      font-size: 16px;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg ">
    <a class="navbar-brand" href="#">
      <h2>
        <?php echo $row['name']; ?>
      </h2>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"><i class="fa-solid fa-bars"></i></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item active">
          <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Profile</a>
        </li>
        <li class="nav-item dropdown"> <!-- Added 'dropdown' class -->
          <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false"><?php echo $row['name']; ?></a>
          <div class="dropdown-menu" aria-labelledby="profileDropdown" style="left: inherit; right: 0px;">
            <!-- Dropdown menu items here -->
            <?php echo ' <img src="/myproject/uploads/' . $row['image_url'] . '" class="profile-pic" alt="Profile Picture">'; ?>

            <a class="dropdown-item" href="#">Name :
              <?php echo $row['name']; ?>
            </a>
            <a class="dropdown-item" href="#">Email :
              <?php echo $row['email']; ?>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" id="logout" href="logout.php"><button
                class="btn btn-block btn-danger btn-sm">Logout <i
                  class="fa-solid fa-right-from-bracket fa-fw"></i></button></a>

          </div>
        </li>

      </ul>
    </div>
  </nav>
  <main class="container-fluid">
    <section class="content mt-3">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>
                  <?php echo $num; ?>
                </h3>

                <p>Total Registrations</p>
              </div>
              <div class="icon">
                <i class="fa-solid fa-user"></i>
              </div>
              <a href="index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>0<sup style="font-size: 20px">%</sup></h3>

                <p>Pending Registration</p>
              </div>
              <div class="icon">
                <i class="fa-solid fa-hourglass-half"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>
                  <?php echo $num; ?>
                </h3>

                <p>Active Users</p>
              </div>
              <div class="icon">
                <i class="fa-solid fa-users"></i>
              </div>
              <a href="index.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>0</h3>

                <p>Total deleted users</p>
              </div>
              <div class="icon">
                <i class="fa-solid fa-user-xmark"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <section>
      <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch flex-column">
        <div class="card bg-light d-flex flex-fill">
          <div class="card-header text-muted border-bottom-0">
            Digital Strategist
          </div>
          <div class="card-body pt-0">
            <div class="row">
              <div class="col-7">
                <h2 class="lead"><b><?php echo $row['name']; ?></b></h2>

                <ul class="ml-4 mb-0 fa-ul text-muted">
                  <li class="small"><span class="fa-li"><i class="fas fa-lg fa-building"></i></span> Address:
                    <?php echo $row['address']; ?></li>
                  <li class="small"><span class="fa-li"><i class="fas fa-lg fa-phone"></i></span> Phone #:
                    <?php echo $row['phone']; ?></li>
                </ul>
              </div>
              <div class="col-5 text-center">
                <?php echo '<img src="/myproject/uploads/' . $row['image_url'] . '" class="img-circle img-fluid" alt="Profile Picture">'; ?>

              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="text-right">
              <a href="#" class="btn btn-sm bg-teal">
                <i class="fas fa-comments"></i>
              </a>
              <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                <i class="fas fa-user"></i> View Profile
              </a>

              <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h1 class="modal-title fs-5" id="staticBackdropLabel">Profile</h1>
                    </div>
                    <div class="modal-body text-center">
                        <?php echo ' <img src="/myproject/uploads/' . $row['image_url'] . '" class="profilepic" alt="Profile Picture">'; ?>
                        <div class="text">
                          <h2>Name: <?php echo $row['name']; ?></h2>
                          <p>Gender :<?php echo $row['gender']; ?></p>
                          <p>Phone: <?php echo $row['phone']; ?></p>
                          <p>Email: <?php echo $row['email']; ?></p>
                          <p>Date of Birth :<?php echo $row['dob']; ?></p>
                          <p>Address: <?php echo $row['address']; ?></p>
                          <p>Country: <?php echo $row['country']; ?></p>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>


  </main>
  <footer class="mb-0 py-0 px-0" style="position: fixed; bottom: 0; width: 100%;">
    <div class="container-fluid">
      <div class="row">
        <div class="col">&copy; Copyright reserved 2024
          <?php echo $row['name']; ?>
        </div>
        <div class="col text-right">
          <a href="#">Terms of Service</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Contact</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>

</body>

</html>