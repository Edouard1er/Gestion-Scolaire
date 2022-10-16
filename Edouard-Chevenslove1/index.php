<?php
  require_once ("./action/login.php")
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./library/css/all.min.css" rel="stylesheet"/>
    <link href="./library/css/mdb.min.css" rel="stylesheet"/>
    <link href="./css/style.css" rel="stylesheet"/>
    <link rel="stylesheet" href="./library/css/bootstrap.min.css">
    <title>Connection</title>
</head>
<body>
    <div>
      <section class="vh-100" style="background-color: #9A616D;">
          <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
              <div class="col col-xl-6">
                <div class="card" style="border-radius: 1rem;">
                  <div class="row g-0 d-flex justify-content-center align-items-center">
                    <div class="col-md-6 col-lg-10 d-flex align-items-center">
                      <div class="card-body p-4 p-lg-5 text-black">
                        <form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post">
                          <h5 class="fw-normal mb-3 pb-3" style="letter-spacing: 1px;">Connectez-vous</h5>
                          <?= !empty($loginError) ? ("<div class='alert alert-danger'>" . $loginError . "</div>") : ""?>
                          <div class="form-outline mb-4">
                            <input required name="username" type="text" id="username" class="form-control form-control-lg" />
                            <label class="form-label" for="username">Username</label>
                            <span class="invalid-feedback">gjghjcgjhgjhgj<?php echo $usernameError; ?></span>
                          </div>
                          <div class="form-outline mb-4">
                            <input required name="password" type="password" id="password" class="form-control form-control-lg" />
                            <label class="form-label" for="password">Password</label>
                            <span class="invalid-feedback">gghghjgdjhj<?php echo $passwordError; ?></span>
                          </div>
                          <div class="pt-1 mb-4">
                            <button class="btn btn-dark btn-lg btn-block" type="submit">Connecter</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
      </section>
    </div>
<script type="text/javascript" src="./library/js/mdb.min.js"></script>
<script type="text/javascript" src="./js/function.js" ></script>
</body>
</html>