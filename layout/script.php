<?php

/** Arquivo com os scripts js, deve ser incluído em todas as views */ ?>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="../assets/js/bootstrap.min.js"></script>
<script src="../assets/js/maskMoney.js"></script>
<script src="https://kit.fontawesome.com/35d31905e0.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>

<script>
    const logout = () => {
        window.location.replace('../controller/logout.php');
    }

    const showError = (text) => {
        $.alert({
            icon: 'fas fa-times',
            title: 'Ops',
            content: text,
            theme: 'modern',
            closeIcon: true,
            animation: 'scale',
            type: 'red',
            escapeKey: true,
            buttons: {
                ok: {
                    text: 'Ok',
                    keys: ['enter'],
                    btnClass: 'btn-danger'
                }
            }
        });
    }

    const verificaPermissaoDesconto = () => {
        let action = 'verificaPermissaoDesconto';
        $.ajax({
            url: '../controller/usuarios.php',
            type: 'post',
            data: {
                action
            }
        }).then((data) => {
            let response = JSON.parse(data);
            if (response == 0) {
                showError('Você não tem permissão para acessar essa página!');
            } else{
                window.location.replace('pedidos.php');
            }
        }).fail((jqXHR, textStatus, errorThrown) => {
            showError('Erro no servidor, tente novamente!');
        });
    }

    const showSuccess = (text) => {
        $.alert({
            icon: 'fas fa-check',
            title: 'Sucesso',
            content: text,
            theme: 'modern',
            closeIcon: true,
            animation: 'scale',
            type: 'green',
            escapeKey: true,
            buttons: {
                ok: {
                    text: 'Ok',
                    keys: ['enter'],
                    btnClass: 'btn-success'
                }
            }
        });
    }

    function formatMoney(amount, decimalCount = 2, decimal = ",", thousands = ".") {
        try {
            decimalCount = Math.abs(decimalCount);
            decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

            const negativeSign = amount < 0 ? "-" : "";

            let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
            let j = (i.length > 3) ? i.length % 3 : 0;

            return negativeSign + (j ? i.substr(0, j) + thousands : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands) + (decimalCount ? decimal + Math.abs(amount - i).toFixed(decimalCount).slice(2) : "");
        } catch (e) {
            console.log(e)
        }
    };
</script>