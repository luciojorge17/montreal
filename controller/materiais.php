<?php
include_once '../config/database.php';
$action = $_POST['action'];
switch ($action) {
    case 'listar':
        $retorno = [];
        $contador = 0;
        $pesquisa = $_POST['pesquisaMaterial'];
        $select =
            "SELECT
            M.CD_MATERIAL,M.CD_IDENTIFICACAO,M.DS_MATERIAL,M.VL_VENDA,M.DS_OBS,M.DS_ABREVIATURA,M.DS_LOCALIZACAO_ESTOQUE,M.X_ATIVO,IT.DS_PRODUCAO
            FROM
            SEL_MATERIAIS AS M
            FULL OUTER JOIN
            SEL_MATERIAIS_INFORMACOES_TECNICAS AS IT
            ON
            M.CD_MATERIAL = IT.CD_MATERIAL
            WHERE
            M.DS_MATERIAL LIKE '%$pesquisa%' or IT.DS_PRODUCAO LIKE '%$pesquisa%'
            ORDER BY
            M.DS_MATERIAL ASC";
        //$select = "select * from sel_materiais where ds_material like '%$pesquisa%'";
        $consulta = odbc_exec($conexao, $select);
        if (odbc_num_rows($consulta) > 0) {
            $retorno['produtos'] = '
                    <table class="table table-sm table-hover">
                        <thead class="thead-dark text-light">
                            <tr>
                                <th scope="col">Descrição</th>
                                <th scope="col">Código</th>
                                <th scope="col">Código fabricante</th>
                                <th scope="col" class="text-center">UN</th>
                                <th scope="col">Localização</th>
                                <th scope="col" class="text-center">Ativo</th>
                                <th scope="col" class="text-right">Valor Venda</th>
                                <th scope="col">Informação complementar</th>
                            </tr>
                        </thead>
                        <tbody>
                ';
            while (odbc_fetch_row($consulta)) {
                $contador++;
                $nome_material = utf8_encode(odbc_result($consulta, 'ds_material'));
                $codigo = odbc_result($consulta, 'cd_material');
                $codigo_fabricante = odbc_result($consulta, 'cd_identificacao');
                $un = odbc_result($consulta, 'ds_abreviatura');
                $localizacao = utf8_encode(odbc_result($consulta, 'ds_localizacao_estoque'));
                $ativo = odbc_result($consulta, 'x_ativo');
                if ($ativo == 0) {
                    $ativo = '<i class="fas fa-times"></i>';
                } else {
                    $ativo = '<i class="fas fa-check"></i>';
                }
                $valor_venda = number_format(odbc_result($consulta, 'vl_venda'), 2, ",", ".");
                $observacoes = utf8_encode(odbc_result($consulta, 'ds_obs'));
                if ($contador == 1) {
                    $retorno['primeiraConsulta'] = $codigo;
                }
                $retorno['produtos'] .= '
                        <tr id="index-' . $contador . '" tabindex="' . $contador . '" onclick="listaDadosMaterial(this,' . $codigo . ')" style="cursor: pointer;">
                            <th scope="row">' . $nome_material   . '</th>
                            <td>' . $codigo . '</td>
                            <td>' . $codigo_fabricante . '</td>
                            <td class="text-center">' . $un . '</td>
                            <td>' . $localizacao . '</td>
                            <td class="text-center">' . $ativo . '</td>
                            <td class="text-right">' . $valor_venda . '</td>
                            <td>' . $observacoes . '</td>
                        </tr>
                    ';
            }
            $retorno['produtos'] .= '
                        </tbody>
                    </table>
                ';
        } else {
            $retorno['primeiraConsulta'] = 0;
            $retorno['produtos'] = '<p class="text-center">Nenhum produto encontrado com o termo "' . $pesquisa . '"</p>';
        }
        echo json_encode($retorno);
        break;
    case 'dadosMaterialById':
        $id = $_POST['id'];
        $informacao_tecnica = '';
        $marca = '';
        $grupo = '';
        $peso_bruto = '';
        $peso_liquido = '';
        $identificacao = '';
        $ncm = '';
        $cest = '';
        $estoque = '';
        $fotoProduto = '<img class="img-fluid" src="../assets/images/no-image.png" alt="" style="width: 300px"/>';
        $select_info_tecnica = "SELECT DS_PRODUCAO FROM SEL_MATERIAIS_INFORMACOES_TECNICAS WHERE CD_MATERIAL = $id";
        $consulta_info_tecnica = odbc_exec($conexao, $select_info_tecnica);
        if (odbc_num_rows($consulta_info_tecnica) > 0) {
            while (odbc_fetch_row($consulta_info_tecnica)) {
                $informacao_tecnica = utf8_encode(odbc_result($consulta_info_tecnica, 'ds_producao'));
            }
        }
        $select = "SELECT DS_MARCA, DS_SUBGRUPO, NR_PESO_BRUTO, NR_PESO_LIQUIDO, CD_IDENTIFICACAO, CD_NCM, CD_CEST, IM_FOTO FROM SEL_MATERIAIS WHERE CD_MATERIAL = $id";
        $consulta = odbc_exec($conexao, $select);
        if (odbc_num_rows($consulta) > 0) {
            while (odbc_fetch_row($consulta)) {
                $marca = utf8_encode(odbc_result($consulta, 'ds_marca'));
                $grupo = utf8_encode(odbc_result($consulta, 'ds_subgrupo'));
                $peso_bruto = number_format(odbc_result($consulta, 'nr_peso_bruto'), 4, ",", ".");
                $peso_liquido = number_format(odbc_result($consulta, 'nr_peso_liquido'), 4, ",", ".");
                $identificacao = odbc_result($consulta, 'cd_identificacao');
                $ncm = odbc_result($consulta, 'cd_ncm');
                $cest = odbc_result($consulta, 'cd_cest');
                $imagem = base64_encode(odbc_result($consulta, "im_foto"));
                if (!empty($imagem)) {
                    $fotoProduto = '<img class="img-fluid" src="data:image/jpeg;base64,' . $imagem . '" alt="" width="300px">';
                }
            }
        }
        $select_estoque = "SELECT DS_FILIAL, NR_ESTOQUE_DISPONIVEL, NR_ESTOQUE_RESERVADO FROM SEL_MATERIAIS_ESTOQUE WHERE CD_MATERIAL=$id ORDER BY CD_FILIAL ASC";
        $consulta_estoque = odbc_exec($conexao, $select_estoque);
        if (odbc_num_rows($consulta_estoque) > 0) {
            $total_fisico = 0;
            $total_reservado = 0;
            $total_disponivel = 0;
            $estoque = '
                <div class="row mt-1">
                    <div id="tabela-estoque" class="col-8">
                        <table class="table-sm table-hover table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">Estoque</th>
                                    <th scope="col" class="text-right">Físico</th>
                                    <th scope="col" class="text-right">Reservado</th>
                                    <th scope="col" class="text-right">Disponível</th>
                                </tr>
                            </thead>
                            <tbody>
            ';
            while (odbc_fetch_row($consulta_estoque)) {
                $filial = utf8_encode(odbc_result($consulta_estoque, 'ds_filial'));
                $estoque_fisico = number_format(odbc_result($consulta_estoque, "nr_estoque_disponivel"), 0, '', '');
                $estoque_reservado = number_format(odbc_result($consulta_estoque, "nr_estoque_reservado"), 0, '', '');
                $estoque_disponivel = $estoque_fisico - $estoque_reservado;
                $total_fisico += $estoque_fisico;
                $total_reservado += $estoque_reservado;
                $total_disponivel += $estoque_disponivel;
                $estoque .= '
                                <tr>
                                    <th scope="row">' . $filial . '</th>
                                    <td class="text-right">' . $estoque_fisico . '</td>
                                    <td class="text-right">' . $estoque_reservado . '</td>
                                    <td class="text-right">' . $estoque_disponivel . '</td>
                                </tr>
                ';
            }
            $estoque .= '
                                <tr>
                                    <td class="text-right" colspan="2"><strong>' . $total_fisico . '</strong></td>
                                    <td class="text-right"><strong>' . $total_reservado . '</strong></td>
                                    <td class="text-right"><strong>' . $total_disponivel . '</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            ';
            $retorno = '
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <label>Aplicação e especificações técnicas</label>
                            <textarea name="informacoesTecnicas" rows="3" class="form-control form-control-sm" disabled>' . $informacao_tecnica . '</textarea>
                        </div>
                        <div class="col-8">
                            <div class="row mt-1">
                                <div class="col-1 text-right">
                                    <label>Marca</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" name="marca" class="form-control form-control-sm" value="' . $marca . '" disabled>
                                </div>
                                <div class="col-3 col-lg-2 text-right">
                                    <label>Peso Bruto (Kg)</label>
                                </div>
                                <div class="col-2">
                                    <input type="text" name="pesoBruto" class="form-control form-control-sm" value="' . $peso_bruto . '" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-1 text-right">
                                    <label>Grupo</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" name="grupo" class="form-control form-control-sm" value="' . $grupo . '" disabled>
                                </div>
                                <div class="col-3 col-lg-2 text-right">
                                    <label>Peso Líquido (Kg)</label>
                                </div>
                                <div class="col-2">
                                    <input type="text" name="pesoLiquido" class="form-control form-control-sm" value="' . $peso_liquido . '" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-1 text-right">
                                    <label>Catal.</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" name="catalogo" class="form-control form-control-sm" value="' . $identificacao . '" disabled>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-1 text-right">
                                    <label>NCM</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" name="ncm" class="form-control form-control-sm" value="' . $ncm . '" disabled>
                                </div>
                                <div class="col-3 col-lg-2 text-right">
                                    <label>CEST</label>
                                </div>
                                <div class="col-2">
                                    <input type="text" name="cest" class="form-control form-control-sm" value="' . $cest . '" disabled>
                                </div>
                            </div>
                            ' .
                $estoque
                . '
                        </div>
                        <div class="col-4 text-center mt-3">
                            ' .
                $fotoProduto
                . '
                        </div>
                    </div>
                </div>
            ';
        }
        echo json_encode($retorno);
        break;
    default:
        echo 'Ação não encontrada';
        break;
}
