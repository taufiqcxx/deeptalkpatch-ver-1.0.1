<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Deeptalk - Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fc;
      margin: 0;
      padding: 0;
    }

    /* Navbar dengan gradasi */
    .navbar {
      padding: 12px 40px;
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .navbar-brand {
      display: flex;
      align-items: center;
      font-weight: bold;
      color: #fff !important;
      font-size: 22px;
    }
    .navbar-brand img {
      height: 36px;
      margin-left: 10px; /* pindah ke kanan tulisan */
    }
    .navbar-nav .nav-link {
      color: #fff !important;
      margin-left: 20px;
      font-weight: 500;
      transition: 0.3s;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link.active {
      color: #F9AA50 !important; /* aksen oranye saat hover/aktif */
    }

    /* Hero Section */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 100px 8% 60px;
      background: #f8f9fc;
    }
    .hero-text h5 {
      color: #0474BA;
      font-weight: 600;
    }
    .hero-text h1 {
      font-size: 40px;
      font-weight: 700;
      color: #1b2838;
      margin: 20px 0;
    }
    .hero-text p {
      color: #555;
      font-size: 16px;
      margin-bottom: 25px;
    }
    .hero-text .btn-primary {
      padding: 12px 30px;
      font-size: 16px;
      border-radius: 30px;
      background: linear-gradient(135deg, #00A7E1, #0474BA);
      border: none;
      transition: 0.3s;
    }
    .hero-text .btn-primary:hover {
      background: #F77D0E;
    }
    .hero img {
      max-width: 500px;
      width: 100%;
    }
    @media (max-width: 992px) {
      .hero {
        flex-direction: column;
        text-align: center;
        padding: 80px 5% 40px;
      }
      .hero img {
        margin-top: 30px;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <a class="navbar-brand" href="index.php">
      Deeptalk
      <img src="assets/logo_home.png" alt="Deeptalk Logo">
    </a>
    <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Registrasi</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-text">
      <h5>Hai, Selamat datang di Deeptalk</h5>
      <h1>Teman yang akan selalu mendengarkan ceritamu.</h1>
      <p>Kami adalah tim konselor profesional siap membantu Anda mengatasi masalah akademik, sosial, maupun pribadi.</p>
      <a href="login.php" class="btn btn-primary">Get Started</a>
    </div>
    <div class="hero-image">
      <img src="assets/main.png" alt="Counseling Team">
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
