<?php
require_once 'includes/auth.php';

requireLogout(); // Redirect if already logged in

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginSupervisor($email, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Invalid credentials. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SAKMS – Supervisor Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <style>
        :root {
            --bg-deep:        #0d1117;
            --bg-sidebar:     #111827;
            --border:         #243047;
            --accent:         #3b82f6;
            --accent-2:       #06b6d4;
            --danger:         #ef4444;
            --text-primary:   #f0f4ff;
            --text-secondary: #8b9bbf;
            --border-color:   #243047;
            --card-bg:        #1a2233;
        }

        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Sora', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            margin: 0 auto 16px;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .login-header p {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            background: #141c2b;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group input::placeholder {
            color: var(--text-secondary);
        }

        .alert {
            padding: 12px 14px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            border-radius: 8px;
            color: var(--danger);
            font-size: 12px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--accent), #2563eb);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .demo-credentials {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-secondary);
        }

        .demo-credentials strong {
            color: var(--text-primary);
        }

        .demo-field {
            background: #141c2b;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 6px;
            font-family: monospace;
            color: var(--accent);
        }

        .demo-field::selection {
            background: var(--accent);
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">S</div>
                <h1>SAKMS</h1>
                <p>Sales Assistant KPI Monitoring System</p>
            </div>

            <form method="POST" class="login-form">
                <div class="alert <?php echo $error ? 'show' : ''; ?>">
                    <?php echo htmlspecialchars($error); ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="supervisor@sakms.com"
                        required 
                        autofocus
                    />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                    />
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="demo-credentials">
                <strong>Demo Credentials:</strong>
                <div>Email:</div>
                <div class="demo-field">supervisor@sakms.com</div>
                <div style="margin-top: 10px;">Password:</div>
                <div class="demo-field">supervisor123</div>
            </div>
        </div>
    </div>
</body>
</html>
