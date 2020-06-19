<?php
session_start();
if (isset($_SESSION['nome_usuario'])) {
    header('Location:consulta_materiais.php');
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<?php
$title = 'Login';
include_once '../layout/head.php';
?>

<body>

    <style>
        body {
            background: #3282b8;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        form {
            width: 300px;
            background: #fff;
            padding: 10px;
            border-radius: 5px;
        }

        form label {
            font-size: 12px;
            font-weight: 600;
        }
    </style>

    <form action="#" class="form-signin">
        <div id="logo" class="text-center mb-3">
            <img src="../assets/images/logo-preto.png" alt="" style="height: 60px;">
        </div>
        <label for="login">Nome de usuário</label>
        <input type="text" name="login" class="form-control form-control-sm" required autofocus>
        <label for="senha">Senha</label>
        <input type="password" name="senha" class="form-control form-control-sm" required>
        <button class="btn btn-primary btn-block mt-3 mb-3">Entrar</button>
    </form>

    <?php include_once '../layout/script.php'; ?>

    <script>
        $('.form-signin').on('submit', (e) => {
            e.preventDefault();
            $('.form-signin .alert').remove();
            let data = $('.form-signin').serialize();
            $.ajax({
                url: '../controller/login.php',
                type: 'post',
                data: data,
                beforeSend: () => $('.form-signin button[type="submit"]').html('Verificando, aguarde...')
            }).then((data) => {
                $('.form-signin button[type="submit"]').html('Entrar');
                let response = JSON.parse(data);
                if (response.autorizado == 1) {
                    //window.location.replace('consulta_materiais.php');
					window.location.reload();
                } else {
                    $('input[name="login"]').focus();
                    $('.form-signin').append(`
                            <div class="alert alert-danger alert-dismissible fade show mt-2 mb-2" role="alert">
                            <small><strong>Ops...</strong> ${response.mensagem}</small>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            </div>`);
                    setTimeout(() => {
                        $('.form-signin .alert').remove();
                    }, 2000);
                }
            }).fail((jqXHR, textStatus, errorThrown) => {
                $('input[name="login"]').focus();
                $('.form-signin button[type="submit"]').html('Entrar');
                let message = `Ocorreu um erro, tente novamente!`;
                $('.form-signin').append(`
                            <div class="alert alert-danger alert-dismissible fade show mt-2 mb-2" role="alert">
                            <small><strong>Ops...</strong> ${response.mensagem}</small>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            </div>`);
                setTimeout(() => {
                    $('.form-signin .alert').remove();
                }, 2000);
            });
        });
    </script>

</body>

</html>