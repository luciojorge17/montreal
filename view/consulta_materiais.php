<?php
session_start();
if (!isset($_SESSION['nome_usuario'])) {
    header('Location:login.php');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<?php
$title = 'Consulta de materiais';
$menu[0] = 'active';
$menu[1] = '';
include_once '../layout/head.php';
?>

<body>

    <?php include_once '../layout/cabecalho.php'; ?>

    <div class="container mt-2 mb-1">
        <form id="form-pesquisa-material" action="#">
            <div class="form-group row">
                <div class="col-2 text-right">
                    <label for="pesquinaMaterial">Pesquisar por:</label>
                </div>
                <div class="col-6">
                    <input type="hidden" name="action" value="listar">
                    <input type="text" name="pesquisaMaterial" class="form-control form-control-sm" required autofocus>
                </div>
                <div class="col-2">
                    <button class="btn btn-primary btn-sm">Buscar produtos</button>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div id="tabela-materiais" class="col-12">
            </div>
        </div>
    </div>

    <section id="material-detalhes">
    </section>

    <?php include_once '../layout/script.php'; ?>

    <script>
        $('#form-pesquisa-material').on('submit', (e) => {
            e.preventDefault();
            $('#material-detalhes').empty();
            let data = $('#form-pesquisa-material').serialize();
            $.ajax({
                url: '../controller/materiais.php',
                type: 'post',
                data: data,
                beforeSend: () => $('#tabela-materiais').html(`<p class="text-center">Aguarde, pesquisando materiais...</p>`)
            }).then((data) => {
                let response = JSON.parse(data);
                $('#tabela-materiais').html(response.produtos);
                if (response.primeiraConsulta != 0) {
                    listaDadosMaterial(e,response.primeiraConsulta);
                    focarElemento('index-1');
                }
            }).fail((jqXHR, textStatus, errorThrown) => {
                let message = `Ocorreu um erro, tente novamente!`;
                setTimeout(() => {
                    $('#tabela-materiais').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops...</strong> ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`);
                }, 3000);
            });
        });

        const focarElemento = (id) => {
            $(`#tabela-materiais table tbody tr`).removeClass('active-row');
            $(`#${id}`).addClass('active-row');
            document.getElementById(id).scrollIntoView(true);
        }

        const listaDadosMaterial = (event, id) => {
            if(event.id != undefined){
                focarElemento(event.id);
            }
            let action = 'dadosMaterialById';
            $.ajax({
                url: '../controller/materiais.php',
                type: 'post',
                data: {
                    id,
                    action
                },
                beforeSend: () => $('#material-detalhes').html(`<div class="container"><div class="row"><div class="col-12"><p class="text-center">Aguarde, pesquisando dados...</p></div></div></div>`)
            }).then((data) => {
                let response = JSON.parse(data);
                $('#material-detalhes').html(response);
            }).fail((jqXHR, textStatus, errorThrown) => {
                let message = `Ocorreu um erro, tente novamente!`;
                setTimeout(() => {
                    $('#material-detalhes').html(`
                    <div class="container"><div class="row"><div class="col-12"><div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops...</strong> ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div></div></div></div>`);
                }, 3000);
            });
        }
    </script>

</body>

</html>