<?php
session_start();
if (!isset($_SESSION['nome_usuario'])) {
    header('Location:login.php');
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<?php
$title = 'Pedidos';
$menu[0] = '';
$menu[1] = 'active';
include_once '../layout/head.php';
?>

<body>

    <?php include_once '../layout/cabecalho.php'; ?>

    <div class="container mt-2 mb-1">
        <form id="form-pesquisa-pedido" action="#">
            <div class="form-group row">
                <div class="col-2 text-right">
                    <label for="codigoPedido">Código do pedido:</label>
                </div>
                <div class="col-2">
                    <input type="text" name="codigoPedido" class="form-control form-control-sm" autofocus>
                </div>
                <div class="col-2">
                    <label for="identificacaoCliente">Nome ou CPF/CNPJ</label>
                </div>
                <div class="col-4">
                    <input type="text" name="identificacaoCliente" class="form-control form-control-sm">
                </div>
                <div class="col-2">
                    <button class="btn btn-primary btn-sm">Buscar pedido</button>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div id="tabela-pedido" class="col-12">
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row mt-2">
            <div id="tabela-pedido-produtos" class="col-12">
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row mt-2">
            <div id="div-footer" class="col-12 text-center">
            </div>
        </div>
    </div>


    <?php include_once '../layout/script.php'; ?>

    <script>
        $('#form-pesquisa-pedido').on('submit', (e) => {
            e.preventDefault();
            $('#tabela-pedido').empty();
            $('#tabela-pedido-produtos').empty();
            $('#div-footer').empty();
            let codigoPedido = $('input[name="codigoPedido"]').val();
            let identificacaoCliente = $('input[name="identificacaoCliente"]').val();
            let action = '';
            if (codigoPedido === '' && identificacaoCliente === '') {
                showError('Informe o código do pedido ou a identificação do cliente');
            } else if (codigoPedido != '') {
                abrePedido(codigoPedido);
            } else if (codigoPedido === '' && identificacaoCliente != '') {
                action = 'buscaClientes';
                $.ajax({
                    url: '../controller/pedidos.php',
                    type: 'post',
                    data: {
                        action,
                        identificacaoCliente
                    },
                    beforeSend: () => $('#tabela-pedido').html(`<p class="text-center">Aguarde, pesquisando cliente...</p>`)
                }).then((data) => {
                    let response = JSON.parse(data);
                    $('#tabela-pedido').empty();
                    if (response.status == 1) {
                        $.alert({
                            title: 'Selecione o cliente',
                            content: response.clientes,
                            columnClass: 'large',
                            theme: 'modern',
                            closeIcon: true,
                            animation: 'scale',
                            type: 'blue',
                            escapeKey: true,
                            buttons: {
                                cancel: {
                                    text: 'Cancelar',
                                    keys: ['esc'],
                                    btnClass: 'btn-danger'
                                }
                            }
                        });
                    } else {
                        showError(response.mensagem);
                    }
                }).fail((jqXHR, textStatus, errorThrown) => {
                    let message = `Ocorreu um erro, tente novamente!`;
                    setTimeout(() => {
                        $('#tabela-pedidos').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops...</strong> ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`);
                    }, 3000);
                });
            }
        });

        const alteraValor = (valor, quantidade, item) => {
            valor = valor.replace('.', '');
            valor = valor.replace(',', '.');
            valor = parseFloat(valor).toFixed(2);
            let novoValor = parseFloat(valor) * quantidade;
            novoValor = formatMoney(novoValor);
            $(`#total-item-${item}`).html(novoValor);
            atualizaTotal();
        }

        const atualizaTotal = () => {
            let valorAtualizado = 0.00;
            let itens = $('.valorTotalItem');
            for (let i = 0; i < itens.length; i++) {
                let valor = itens[i];
                valor = $(`#${valor.id}`).html();
                valor = valor.replace('.', '');
                valor = valor.replace(',', '.');
                valorAtualizado = parseFloat(valorAtualizado) + parseFloat(valor);
            }
            $('.valorTotalPedido').html(formatMoney(valorAtualizado));
        }

        const abrePedido = (codigoPedido) => {
            $('.jconfirm').remove();
            action = 'buscaPedidoByCodigo';
            $.ajax({
                url: '../controller/pedidos.php',
                type: 'post',
                data: {
                    action,
                    codigoPedido
                },
                beforeSend: () => $('#tabela-pedido').html(`<p class="text-center">Aguarde, pesquisando pedido...</p>`)
            }).then((data) => {
                let response = JSON.parse(data);
                $('#tabela-pedido').html(response.cabecalho);
                $('#tabela-pedido-produtos').html(response.itens);
                $('#div-footer').html(response.botao);
                $(".valorAlteracao").maskMoney({
                    allowNegative: false,
                    thousands: '.',
                    decimal: ',',
                    affixesStay: false
                });
            }).fail((jqXHR, textStatus, errorThrown) => {
                let message = `Ocorreu um erro, tente novamente!`;
                setTimeout(() => {
                    $('#tabela-pedidos').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops...</strong> ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`);
                }, 3000);
            });
        }

        const buscaPedidos = (idCliente) => {
            action = 'buscaPedidosByCliente';
            $.ajax({
                url: '../controller/pedidos.php',
                type: 'post',
                data: {
                    action,
                    idCliente
                },
                beforeSend: () => $('#tabela-pedido').html(`<p class="text-center">Aguarde, pesquisando pedidos...</p>`)
            }).then((data) => {
                let response = JSON.parse(data);
                $('#tabela-pedido').empty();
                if (response.status == 1) {
                    $.alert({
                        title: 'Selecione o pedido',
                        content: response.pedidos,
                        columnClass: 'large',
                        theme: 'modern',
                        closeIcon: true,
                        animation: 'scale',
                        type: 'blue',
                        escapeKey: true,
                        buttons: {
                            cancel: {
                                text: 'Cancelar',
                                keys: ['esc'],
                                btnClass: 'btn-danger'
                            }
                        }
                    });
                } else {
                    showError(response.mensagem);
                }
            }).fail((jqXHR, textStatus, errorThrown) => {
                let message = `Ocorreu um erro, tente novamente!`;
                setTimeout(() => {
                    $('#tabela-pedidos').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Ops...</strong> ${message}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>`);
                }, 3000);
            });
        }

        const confirmacao = () => {
            $.confirm({
                title: 'Atenção',
                content: `
                <div class="col-12">
                    <label>Digite o motivo da alteração (obrigatório)</label>
                    <textarea name="motivoAlteracao" class="form-control" rows="3"></textarea>
                </div>`,
                theme: 'modern',
                closeIcon: true,
                animation: 'scale',
                type: 'blue',
                escapeKey: true,
                buttons: {
                    cancel: {
                        text: 'Cancelar',
                        keys: ['esc'],
                        btnClass: 'btn-danger'
                    },
                    ok: {
                        text: 'Enviar',
                        keys: ['enter'],
                        btnClass: 'btn-primary',
                        action: () => {
                            let motivo = $('textarea[name="motivoAlteracao"]').val();
                            if (motivo != '') {
                                enviarDadosQuestor(motivo);
                            }
                        }
                    }
                }
            });
        }

        const enviarDadosQuestor = (motivo) => {
            let codigoPedido = $('#codigo-pedido-integracao').html();
            let dados = [];
            let action = 'enviarDadosQuestor';
            let valores = $('.valorAlteracao');
            for (let i = 0; i < valores.length; i++) {
                let name = valores[i].name;
                let value = valores[i].value;
                let c = `${name}|${value}`;
                dados.push(c);
            }
            $.ajax({
                url: '../controller/pedidos.php',
                type: 'post',
                data: {
                    action,
                    codigoPedido,
                    dados,
                    motivo
                }
            }).then((data) => {
                let response = JSON.parse(data);
                if (response.status == 1) {
                    showSuccess(response.mensagem);
                } else {
                    showError(response.mensagem);
                }
            }).fail((jqXHR, textStatus, errorThrown) => {
                let message = `Ocorreu um erro, tente novamente!`;
                showError(message);
            });
        }
    </script>

</body>

</html>