<?php
include_once '../config/database.php';
$action = $_POST['action'];
switch ($action) {
    case 'buscaPedidoByCodigo':
        $retorno['cabecalho'] = "";
        $retorno['produtos'] = "";
        $retorno['botao'] = "";
        $pesquisa = $_POST['codigoPedido'];
        $select =
            "SELECT TOP(1) CD_PEDIDO, CD_CLIENTE, DS_CLIENTE, CD_CARTEIRA, DS_CARTEIRA, CD_FORMA_PAGAMENTO, DS_FORMA_PAGAMENTO,VL_TOTAL_LIQUIDO, DS_STATUS, DT_EMISSAO FROM SEL_PEDIDOS WHERE CD_PEDIDO=$pesquisa AND CD_STATUS=1";
        $consulta = odbc_exec($conexao, $select);
        if (odbc_num_rows($consulta) > 0) {
            $retorno['cabecalho'] = '
                    <table class="table table-sm table-hover">
                        <thead class="thead-dark text-light">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Cliente</th>
                                <th scope="col" class="text-center">Emissão</th>
                                <th scope="col" class="text-center">Status</th>
                                <th scope="col">Carteira</th>
                                <th scope="col">Forma de Pagto</th>
                                <th scope="col" class="text-right">Valor Líquido</th>
                            </tr>
                        </thead>
                        <tbody>
                ';
            while (odbc_fetch_row($consulta)) {
                $codigo = odbc_result($consulta, 'cd_pedido');
                $cliente = odbc_result($consulta, 'cd_cliente') . " - " . utf8_encode(odbc_result($consulta, 'ds_cliente'));
                $carteira = odbc_result($consulta, 'cd_carteira') . " - " . utf8_encode(odbc_result($consulta, 'ds_carteira'));
                $formaPagto = odbc_result($consulta, 'cd_forma_pagamento') . " - " . utf8_encode(odbc_result($consulta, 'ds_forma_pagamento'));
                $emissao = date('d/m/Y', strtotime(odbc_result($consulta, "dt_emissao")));
                $status = odbc_result($consulta, 'ds_status');
                $valor_liquido = number_format(odbc_result($consulta, 'vl_total_liquido'), 2, ",", ".");
                $retorno['cabecalho'] .= '
                        <tr>
                            <th id="codigo-pedido-integracao" scope="row">' . $codigo . '</th>
                            <td>' . $cliente . '</td>
                            <td class="text-center">' . $emissao . '</td>
                            <td class="text-center">' . $status . '</td>
                            <td>' . $carteira . '</td>
                            <td>' . $formaPagto . '</td>
                            <td class="text-right valorTotalPedido">' . $valor_liquido . '</td>
                        </tr>
                    ';
            }
            $retorno['cabecalho'] .= '
                        </tbody>
                    </table>
                ';

            /** Itens do pedido */
            $select_itens =
                "SELECT CD_ITEM, CD_MATERIAL,CD_IDENTIFICACAO, DS_MATERIAL, NR_QUANTIDADE, VL_UNITARIO, PR_DESCONTO, VL_DESCONTO,VL_TOTAL_LIQUIDO FROM SEL_PEDIDOS_ITENS WHERE CD_PEDIDO = $pesquisa";
            $consulta_itens = odbc_exec($conexao, $select_itens);
            if (odbc_num_rows($consulta_itens) > 0) {
                $retorno['itens'] = '
                <table class="table table-sm table-hover">
                    <thead class="thead-dark text-light">
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col">Código</th>
                            <th scope="col">Identificação</th>
                            <th scope="col">Material</th>
                            <th scope="col" class="text-center">Quantidade</th>
                            <th scope="col" class="text-right">Vl. Unitário</th>
                            <th scope="col" class="text-right">Desconto (%)</th>
                            <th scope="col" class="text-right">Desconto (R$)</th>
                            <th scope="col" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
            ';
                while (odbc_fetch_row($consulta_itens)) {
                    $item = odbc_result($consulta_itens, 'cd_item');
                    $codigo = odbc_result($consulta_itens, 'cd_material');
                    $identificacao = odbc_result($consulta_itens, 'cd_identificacao');
                    $material = utf8_encode(odbc_result($consulta_itens, 'ds_material'));
                    $quantidade = number_format(odbc_result($consulta_itens, 'nr_quantidade'), 0, "", "");
                    $valor_unitario = number_format(odbc_result($consulta_itens, 'vl_unitario'), 2, ",", ".");
                    $valor_desconto = number_format(odbc_result($consulta_itens, 'vl_desconto'), 2, ",", ".");
                    $percentual_desconto = number_format(odbc_result($consulta_itens, 'pr_desconto'), 2, ".", "");
                    $valor_total = number_format(odbc_result($consulta_itens, 'vl_total_liquido'), 2, ",", ".");
                    $retorno['itens'] .= '
                    <tr>
                        <th scope="row">' . $item . '</th>
                        <td>' . $codigo . '</td>
                        <td>' . $identificacao . '</td>
                        <td>' . $material . '</td>
                        <td class="text-center">' . $quantidade . '</td>
                        <td class="text-right">
                            <input type="text" name="unitario-item-' . $item . '" class="form-control form-control-sm text-right valorAlteracao" value="' . $valor_unitario . '" onchange="alteraValor(this.value, ' . $quantidade . ', ' . $item . ');">
                        </td>
                        <td class="text-right">
                            <input type="text" name="pr-desconto-item-' . $item . '" class="form-control form-control-sm text-right percentualDescontoAlteracao" value="' . $percentual_desconto . '" onchange="alteraPercentualDesconto(this.value, '.odbc_result($consulta_itens,"vl_unitario").', ' . $quantidade . ', ' . $item . ');">
                        </td>
                        <td class="text-right">
                            <input type="text" name="vl-desconto-item-' . $item . '" class="form-control form-control-sm text-right valorDescontoAlteracao" value="' . $valor_desconto . '" onchange="alteraValorDesconto(this.value, ' . $quantidade . ', ' . $item . ');">
                        </td>
                        <td id="total-item-' . $item . '" class="text-right valorTotalItem">' . $valor_total . '</td>
                    </tr>
                ';
                }
                $retorno['itens'] .= '
                    </tbody>
                </table>
                ';
                $retorno['botao'] = '
                    <button id="btn-salvar-alteracoes" onclick="confirmacao()"class="btn btn-primary btn-lg mt-4 mb-3">
                        Salvar alterações
                    </button>
                ';
            }
        } else {
            $retorno['cabecalho'] = '<p class="text-center">Nenhum pedido pendente encontrado com o código "' . $pesquisa . '"</p>';
        }

        echo json_encode($retorno);
        break;
    case 'buscaClientes':
        $identificacao = $_POST['identificacaoCliente'];
        if (is_numeric($identificacao)) {
            if (strlen($identificacao) == 11) {
                $identificacao = substr_replace($identificacao, '.', 3, 0);
                $identificacao = substr_replace($identificacao, '.', 7, 0);
                $identificacao = substr_replace($identificacao, '-', 11, 0);
            } else if (strlen($identificacao) == 14) {
                $identificacao = substr_replace($identificacao, '.', 2, 0);
                $identificacao = substr_replace($identificacao, '.', 6, 0);
                $identificacao = substr_replace($identificacao, '/', 10, 0);
                $identificacao = substr_replace($identificacao, '-', 15, 0);
            }
        }
        $retorno = [];
        $select = "SELECT CD_ENTIDADE, DS_ENTIDADE, DS_FANTASIA, NR_CPFCNPJ FROM SEL_ENTIDADES WHERE X_CLIENTE=1 AND X_ATIVO=1 AND DS_ENTIDADE LIKE '%$identificacao%' OR DS_FANTASIA LIKE '%$identificacao%' OR NR_CPFCNPJ='$identificacao' ORDER BY DS_ENTIDADE ASC";
        $consulta = odbc_exec($conexao, $select);
        if (odbc_num_rows($consulta) > 0) {
            $retorno['status'] = 1;
            $retorno['clientes'] = '
                <div id="tabela-clientes" class="col-12">
                    <table class="table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col" class="text-left">Código</th>
                                <th scope="col" class="text-left">Entidade</th>
                                <th scope="col" class="text-left">Fantasia</th>
                                <th scope="col" class="text-left">CPF/CNPJ</th>
                            </tr>
                        </thead>
                        <tbody>
            ';
            while (odbc_fetch_row($consulta)) {
                $codigo = odbc_result($consulta, "cd_entidade");
                $cnpj = odbc_result($consulta, "nr_cpfcnpj");
                $entidade = utf8_encode(odbc_result($consulta, "ds_entidade"));
                $fantasia = utf8_encode(odbc_result($consulta, "ds_fantasia"));
                $retorno['clientes'] .= '
                            <tr onclick="buscaPedidos(' . $codigo . ')" style="cursor: pointer">
                                <th scope="row" class="text-left">' . $codigo . '</th>
                                <td class="text-left">' . $entidade . '</td>
                                <td class="text-left">' . $fantasia . '</td>
                                <td class="text-left">' . $cnpj . '</td>
                            </tr>
                ';
            }
            $retorno['clientes'] .= '
                        </tbody>
                    </table>
                </div>
            ';
        } else {
            $retorno['status'] = 0;
            $retorno['mensagem'] = 'Nenhum cliente encontrado com o termo "' . $identificacao . '"';
        }
        echo json_encode($retorno);
        break;
    case 'buscaPedidosByCliente':
        $idCliente = $_POST['idCliente'];
        $retorno = [];
        $select = "SELECT CD_PEDIDO, DS_STATUS, DT_EMISSAO, VL_TOTAL_LIQUIDO, CD_CARTEIRA, DS_CARTEIRA, CD_FORMA_PAGAMENTO, DS_FORMA_PAGAMENTO FROM SEL_PEDIDOS WHERE CD_STATUS=1 AND CD_CLIENTE=$idCliente ORDER BY CD_PEDIDO ASC";
        $consulta = odbc_exec($conexao, $select);
        if (odbc_num_rows($consulta) > 0) {
            $retorno['status'] = 1;
            $retorno['pedidos'] = '
                <div id="tabela-pedidos-cliente" class="col-12">
                    <table class="table-sm table-hover table-striped">
                        <thead>
                            <tr>
                                <th scope="col" class="text-left">Código</th>
                                <th scope="col" class="text-left">Carteira</th>
                                <th scope="col" class="text-left">Forma de pagto</th>
                                <th scope="col" class="text-left">Status</th>
                                <th scope="col" class="text-center">Emissao</th>
                                <th scope="col" class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
            ';
            while (odbc_fetch_row($consulta)) {
                $codigo = odbc_result($consulta, "cd_pedido");
                $status = utf8_encode(odbc_result($consulta, "ds_status"));
                $emissao = date('d/m/Y', strtotime(odbc_result($consulta, "dt_emissao")));
                $total = number_format(odbc_result($consulta, "vl_total_liquido"), 2, ',', '.');
                $carteira = odbc_result($consulta, "cd_carteira") . ' - ' . utf8_encode(odbc_result($consulta, "ds_carteira"));
                $formaPagto = odbc_result($consulta, "cd_forma_pagamento") . ' - ' . utf8_encode(odbc_result($consulta, "ds_forma_pagamento"));
                $retorno['pedidos'] .= '
                            <tr onclick="abrePedido(' . $codigo . ')" style="cursor: pointer">
                                <th scope="row" class="text-left">' . $codigo . '</th>
                                <th scope="row" class="text-left">' . $carteira . '</th>
                                <th scope="row" class="text-left">' . $formaPagto . '</th>
                                <td class="text-left">' . $status . '</td>
                                <td class="text-center">' . $emissao . '</td>
                                <td class="text-right">' . $total . '</td>
                            </tr>
                ';
            }
            $retorno['pedidos'] .= '
                        </tbody>
                    </table>
                </div>
            ';
        } else {
            $retorno['status'] = 0;
            $retorno['mensagem'] = 'Nenhum pedido encontrado para este cliente';
        }
        echo json_encode($retorno);
        break;
    case 'enviarDadosQuestor':
        session_start();
        $dados = $_POST['dados'];
        $codigo = $_POST['codigoPedido'];
        $motivo = $_POST['motivo'];
        $codigoUsuario = $_SESSION['id_usuario'];
        $nomeUsuario = $_SESSION['nome_usuario'];
        $total_bruto = 0;
        $total_liquido = 0;
        $desconto_itens = 0;
        foreach ($dados as $d) {
            $temp = explode('|',$d);
            $arr = $temp[0];
            $tmp = explode('-', $arr);
            $cd_item = $tmp[2];
            $quantidade = 0;
            $c = "select nr_quantidade,cd_material,cd_cme from tbl_pedidos_itens where cd_pedido=$codigo and cd_item=$cd_item";
            $e = odbc_exec($conexao, $c);
            while (odbc_fetch_row($e)) {
                $quantidade = number_format(odbc_result($e, "nr_quantidade"), 0, '', '');
                $cme = odbc_result($e, "cd_cme");
                $cd_material = odbc_result($e, "cd_material");
            }
            $valor_unitario = $temp[1];
            $valor_unitario = str_replace(".","",$valor_unitario);
            $valor_unitario = str_replace(",",".",$valor_unitario);
            $valor_desconto = $temp[2];
            $valor_desconto = str_replace(".","",$valor_desconto);
            $valor_desconto = str_replace(",",".",$valor_desconto);
            $percentual_desconto = $temp[3];
            $valor_total_bruto = ($valor_unitario * $quantidade);
            $valor_total_liquido = ($valor_unitario * $quantidade) - $valor_desconto;
            $total_bruto += $valor_total_bruto;
            $total_liquido += $valor_total_liquido;
            $desconto_itens += $valor_desconto;
            $data = date('Y-d-m H:i:s').".000";
            $update = "UPDATE TBL_PEDIDOS_ITENS SET CD_USUARIOAT=$codigoUsuario,CD_USUARIO_AUTORIZOU_DESCONTO=$codigoUsuario,DT_ATUALIZACAO='$data',VL_UNITARIO=$valor_unitario,VL_DESCONTO=$valor_desconto,VL_DESCONTO_ITEM=$valor_desconto,PR_DESCONTO=$percentual_desconto,VL_TOTAL=$valor_total_bruto,DS_MOTIVO_VL_UNITARIO='$motivo',VL_UNITARIO_MOEDA=$valor_unitario,VL_TOTAL_MOEDA=$valor_total_bruto WHERE CD_PEDIDO=$codigo AND CD_ITEM=$cd_item";
            odbc_exec($conexao, $update);
            $procedure = "EXEC SP_CALCULA_IMPOSTOS_PEDIDOS $codigo, $cd_item, $cme, $cd_material";
            odbc_exec($conexao, $procedure);
        }
        $data = date('Y-d-m H:i:s').".000";
        $updatePedido = "UPDATE TBL_PEDIDOS SET CD_USUARIOAT=$codigoUsuario,DT_ATUALIZACAO='$data',VL_TOTAL=$total_bruto,VL_DESCONTO_ITENS=$desconto_itens WHERE CD_PEDIDO=$codigo";
        odbc_exec($conexao, $updatePedido);

        $vencimentos = [];
        $selectVencimentos = "SELECT NR_PARCELA, DT_VENCIMENTO FROM TBL_PEDIDOS_PARCELAS WHERE CD_PEDIDO = $codigo";
        $pv = odbc_exec($conexao, $selectVencimentos);
        while (odbc_fetch_row($pv)) {
            $numeroParcela = odbc_result($pv, "nr_parcela");
            $vencimentos[$numeroParcela] = odbc_result($pv, "dt_vencimento");
        }

        $selectNumeroParcelas = "SELECT COUNT(*) AS CONTADOR FROM TBL_PEDIDOS_PARCELAS WHERE CD_PEDIDO=$codigo";
        $p = odbc_exec($conexao, $selectNumeroParcelas);
        $numeroParcelas = 0;
        if (odbc_num_rows($p) > 0) {
            while (odbc_fetch_row($p)) {
                $numeroParcelas = odbc_result($p, "contador");
            }

            $delete = "ALTER TABLE TBL_PEDIDOS_PARCELAS DISABLE TRIGGER ALL;DELETE FROM TBL_PEDIDOS_PARCELAS WHERE CD_PEDIDO=$codigo;ALTER TABLE TBL_PEDIDOS_PARCELAS ENABLE TRIGGER ALL";
            odbc_exec($conexao, $delete);
            $restoParcelas = $total_liquido - number_format(($total_liquido / $numeroParcelas), 2, '.', '') * $numeroParcelas;

            for ($n = 1; $n <= $numeroParcelas; $n++) {
                $valor_parcela = number_format($total_liquido / $numeroParcelas, 2, '.', '');
                if($n == $numeroParcelas){
                    $valor_parcela += $restoParcelas;
                }
                $vencimento = date('Y-d-m H:i:s', strtotime($vencimentos[$n])).".000";

                $insertParcelas = "INSERT INTO TBL_PEDIDOS_PARCELAS (CD_PEDIDO, CD_USUARIO, CD_USUARIOAT, NR_PARCELA, DT_ATUALIZACAO, DT_VENCIMENTO, VL_PARCELA, VL_PARCELA_MOEDA)
                VALUES ($codigo, 0, $codigoUsuario, $n, '$data', '$vencimento', $valor_parcela, 0)";
                odbc_exec($conexao, $insertParcelas);
            }
        }

        $retorno['status'] = 1;
        $retorno['mensagem'] = 'Alteração realizada com sucesso';
        echo json_encode($retorno);
        break;
    default:
        echo 'Ação não encontrada';
        break;
}
