<?php
include "db.php";
if(isset($_POST['register'])){
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users(name, email, password) VALUES(?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);
    
    if($stmt->execute()){
        $success = "Account created successfully";
    } else {
        $error = "Error creating account: " . $stmt->error;
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Events - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="nexuslogo.png" type="image/x-icon">
    <style>
        :root {
            --primary-color: #ffffff;
            --secondary-color: #cbd5e1;
            --bg-gradient: linear-gradient(135deg, #ffa652 0%, #ffcd90 100%);
            --card-bg: linear-gradient(135deg, #be9d60f3 0%, #a08750e8 100%);
            --card-border: rgba(255, 255, 255, 0.2);
            --text-color: #ffffff;
            --muted-text: #94a3b8;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --success-color: #34d399;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .bg-shapes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: linear-gradient(135deg, #ffb76b 0%, #ff8d21 100%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 10%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 360px;
            padding: 0 20px;
        }

        .register-card {
            background: linear-gradient(135deg, #b19d79 0%, #837351e8 100%);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.1),
                0 8px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .register-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            color: var(--text-color);
            padding: 1.2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
         .register-logo{
            width: 70px;
            height:70px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom:20px;
            margin-top:15px;
        }

        .register-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.2; }
            100% { transform: scale(0.8); opacity: 0.5; }
        }

        .logo {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .logo i {
            font-size: 20px;
            color: var(--text-color);
        }

        .register-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.35rem 0;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .register-header p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0;
            font-weight: 500;
        }

        .register-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 0.3rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-wrapper {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid rgba(148, 163, 184, 0.3);
            transition: all 0.3s ease;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(10px);
        }

        .input-wrapper:focus-within {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
            background: rgba(15, 23, 42, 0.6);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: none;
            font-size: 0.9rem;
            color: var(--text-color);
            background: transparent;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: var(--muted-text);
            font-weight: 500;
        }

        .input-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .btn-register {
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            padding: 0.85rem;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-color);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        .btn-register:active::before {
            width: 300px;
            height: 300px;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.2rem 0;
            color: #94a3b8;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .login-link {
            text-align: center;
            margin-top: 1rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            transition: color 0.3s ease;
            position: relative;
        }

        .login-link a:hover {
            color: #1d4ed8;
            text-decoration: none;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: width 0.3s ease;
        }

        .login-link a:hover::after {
            width: 100%;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #ecfdf5;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .register-body {
                padding: 2rem 1.5rem;
            }
            
            .register-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="logo">
                    <img src="nexuslogo.png" alt="logo" class="register-logo">
                </div>
                <h1>Nexus Events</h1>
                <p>Join us today! Create your account</p>
            </div>
            
            <div class="register-body">
                <?php if(isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" class="form-input" id="name" name="name" placeholder="Enter your full name" required>
                            <i class="bi bi-person input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <input type="email" class="form-input" id="email" name="email" placeholder="Enter your email" required>
                            <i class="bi bi-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-input" id="password" name="password" placeholder="Create a strong password" required>
                            <i class="bi bi-lock input-icon"></i>
                        </div>
                    </div>

                    <button class="btn-register" name="register" type="submit">
                        <i class="bi bi-person-plus me-2"></i>
                        Create Account
                    </button>
                </form>

                <div class="divider">Already have an account?</div>
                
                <div class="login-link">
                    <a href="login.php">
                        <i class="bi bi-box-arrow-in-left me-2"></i>
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>