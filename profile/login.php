<!-- Description: Login/Register page, Author: Ramesh Singh, Copyright © 2024 PASA -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="../m7g/m7g-styles.css">
    <script src="../m7g/claimProcessor.js"></script>

    <style>
        .form-container {
            display: none;
        }
        .form-container.active {
            display: block;
        }
    </style>
    <script>
        function toggleForm(formId) {
            document.getElementById('login').classList.remove('active');
            document.getElementById('register').classList.remove('active');
            document.getElementById(formId).classList.add('active');
        }
        function validateLoginForm() {
            var email = document.forms["loginForm"]["email"].value;
            var password = document.forms["loginForm"]["password"].value;
            if (email == "" || password == "") {
                alert("Both fields are required");
                return false;
            }
            return true;
        }
        function validateRegisterForm() {
            var username = document.forms["registerForm"]["username"].value;
            var email = document.forms["registerForm"]["email"].value;
            var password = document.forms["registerForm"]["password"].value;
            var confirmPassword = document.forms["registerForm"]["confirmPassword"].value;
            if (username == "" || email == "" || password == "" || confirmPassword == "") {
                alert("All fields must be filled out"); return false;
            }
            if (password !== confirmPassword) {
                alert("Passwords do not match!"); return false;
            } return true;
        }
    </script>
</head>
<body>
    <header class="header">
        <div class="navbar">
            <a href="../m7g/index.php">Dashboard</a>
            <a href="../m7g/claim.php">Analysis</a>
            <a href="#" class="active"><b>Profile</b></a>
            <?php echo date('H:i:s'); //echo file_get_contents("/app/curr.txt");?>
        </div>
    </header>

    <div class="container">
        <main class="content">    
            <!-- Login Form -->
            <div id="login" class="form-container active">
                <h2 id="formTitle">Login</h2>
                <!-- Display login error if exists -->
                <?php if (isset($_SESSION['login_error'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['login_error']; ?></p>
                    <?php unset($_SESSION['login_error']); ?>
                <?php endif; ?>
                <form action="../m7g/requestHandler.php" method="POST" onsubmit="return validateLoginForm();">
                    <input type="hidden" name="opCode" value="login">
                    <label>Email:</label>
                    <input type="email" name="email" required><br>
                    <label>Password:</label>
                    <input type="password" name="password" required><br><br>
                    <input type="submit" value="Login">
                </form>
                <p>Don't have an account? <a href="#" onclick="toggleForm('register')">Register here</a></p>
            </div>
            <!-- Register Form -->
            <div id="register" class="form-container">
                <h2 id="formTitle">Register</h2>
                <!-- Display register error if exists -->
                <?php if (isset($_SESSION['register_error'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['register_error']; ?></p>
                <?php endif; ?> 
                <form action="../m7g/requestHandler.php" method="POST" onsubmit="return validateRegisterForm();">
                    <input type="hidden" name="opCode" value="register">
                    <label>Username:</label>
                    <input type="text" name="username" required><br>
                    <label>Email:</label>
                    <input type="email" name="email" required><br>
                    <label>Password:</label>
                    <input type="password" name="password" required><br>
                    <label>Confirm Password:</label>
                    <input type="password" name="confirm_password" required><br><br>
                    <input type="submit" value="Register">
                </form>
                <p>Already have an account? <a href="#" onclick="toggleForm('login')">Login here</a></p>
            </div>
            <?php if (isset($_SESSION['register_error'])): ?>
                <script> toggleForm('register'); </script>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?> 
            </main>
        <!--<aside class="sidebar"> </aside>-->
    </div>

    <footer class="footer">
        <p>© 2024 Panasonic Automotive Systems</p>
    </footer>
</body>
</html>
