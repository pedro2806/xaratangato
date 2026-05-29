<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Xaratangato - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container { height: 100vh; display: flex; flex-direction: column; justify-content: center; padding: 30px; }
        .form-control-pro { border: none; background: white; border-radius: 15px; padding: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); margin-bottom: 15px; }
    </style>
</head>
<body style="background-color: var(--bg-color);">

<div class="auth-container">
    <div class="text-center mb-5">
        <h2 class="app-logo" style="font-size: 2.5rem;">Xaratangato</h2>
        <p class="text-muted">Tu aventura comienza aquí</p>
    </div>

    <div id="login-section">
        <form id="form-login">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" class="form-control-pro w-100" placeholder="Correo electrónico" required>
            <input type="password" name="password" class="form-control-pro w-100" placeholder="Contraseña" required>
            <button type="submit" class="btn btn-primary w-100 rounded-pill btn-lg shadow-sm mt-3">Entrar</button>
        </form>
        <p class="text-center mt-4 small text-muted">¿No tienes cuenta? <a href="#" onclick="$('#login-section').hide(); $('#register-section').show();">Regístrate</a></p>
    </div>

    <div id="register-section" style="display: none;">
        <form id="form-register">
            <input type="hidden" name="action" value="register">
            <input type="text" name="nombre" class="form-control-pro w-100" placeholder="Nombre completo" required>
            <input type="email" name="email" class="form-control-pro w-100" placeholder="Correo electrónico" required>
            <input type="password" name="password" class="form-control-pro w-100" placeholder="Crea una contraseña" required>
            <button type="submit" class="btn btn-primary w-100 rounded-pill btn-lg shadow-sm mt-3">Crear cuenta</button>
        </form>
        <p class="text-center mt-4 small text-muted">¿Ya tienes cuenta? <a href="#" onclick="$('#register-section').hide(); $('#login-section').show();">Inicia sesión</a></p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $('#form-login, #form-register').on('submit', function(e){
        e.preventDefault();
        const data = $(this).serialize();
        
        $.post('auth.php', data, function(res){
            if(res.success){
                window.location.href = 'index.php';
            } else {
                //alert(res.mensaje);
                swal.fire({
                    title: 'Error',
                    text: "Error: " + res.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    });
</script>

</body>
</html>