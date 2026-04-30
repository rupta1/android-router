<?php
session_start();

$creds = file_get_contents("/data/data/com.termux/files/home/router/public/admin_db.txt");
list($u,$p) = explode(":", trim($creds));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['user'] === $u && $_POST['pass'] === $p) {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    } else {
        $err = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login</title>

<link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">

<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:'Segoe UI',system-ui;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: radial-gradient(circle at top,#1e293b,#020617);
    color:white;
}

/* ===== LOGIN BOX ===== */
.login-box{
    width:340px;
    padding:30px 25px;

    background: rgba(17,24,39,0.75);
    backdrop-filter: blur(25px);

    border-radius:16px;
    border:1px solid rgba(255,255,255,0.08);

    box-shadow:0 20px 60px rgba(0,0,0,0.7);

    animation:fadeIn 0.6s ease;
}

/* ===== LOGO ===== */
.logo{
    text-align:center;
    font-size:32px;
    margin-bottom:10px;
    color:#3b82f6;
}

/* ===== TITLE ===== */
h2{
    text-align:center;
    margin-bottom:20px;
    font-weight:600;
    letter-spacing:0.5px;
}

/* ===== INPUT GROUP ===== */
.input-group{
    position:relative;
    margin-bottom:15px;
}

.input-group i{
    position:absolute;
    top:50%;
    left:12px;
    transform:translateY(-50%);
    color:#64748b;
}

/* inputs */
input{
    width:100%;
    padding:12px 12px 12px 38px;

    border-radius:10px;
    border:1px solid rgba(255,255,255,0.1);

    background:#020617;
    color:white;

    transition:0.3s;
}

input:focus{
    border-color:#3b82f6;
    box-shadow:0 0 10px rgba(59,130,246,0.3);
}

/* toggle */
.toggle{
    font-size:12px;
    color:#60a5fa;
    margin-top:5px;
    cursor:pointer;
    display:inline-block;
}

/* ===== BUTTON ===== */
button{
    width:100%;
    padding:12px;
    margin-top:10px;

    border:none;
    border-radius:10px;

    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:white;

    font-size:15px;
    font-weight:600;
    cursor:pointer;

    transition:0.3s;
}

button:hover{
    transform:scale(1.05);
    box-shadow:0 10px 25px rgba(37,99,235,0.5);
}

/* ===== ERROR ===== */
.error{
    color:#ef4444;
    text-align:center;
    margin-top:12px;
    font-size:14px;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px)}
    to{opacity:1;transform:translateY(0)}
}
</style>

<body>

<div class="login-box">

<div class="logo">
<i class="fas fa-user-shield"></i>
</div>

<h2>Admin Login</h2>

<form method="post" onsubmit="return validateForm()">

<div class="input-group">
<i class="fas fa-user"></i>
<input type="text" name="user" id="user" placeholder="Username" required>
</div>

<div class="input-group">
<i class="fas fa-lock"></i>
<input type="password" name="pass" id="pass" placeholder="Password" required>
</div>

<span class="toggle" onclick="togglePassword()">
<i class="fas fa-eye"></i> Show Password
</span>

<button id="btn">
<i class="fas fa-sign-in-alt"></i> Login
</button>

</form>

<?php if(isset($err)): ?>
<div class="error"><?= $err ?></div>
<?php endif; ?>

</div>

<script>

// toggle password
function togglePassword(){
    let p=document.getElementById("pass")
    p.type = (p.type==="password") ? "text" : "password"
}

// loading effect
function validateForm(){
    let btn=document.getElementById("btn")
    btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Logging in...'
    btn.disabled=true
    return true
}

</script>

</body>
</html>
