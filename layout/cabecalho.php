<?php

/** Menu principal da aplicação, deve ser incluído em todas as páginas */ ?>
<nav class="navbar navbar-expand-lg navbar-dark text-light bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="../assets/images/logo-branco.png" alt="" height="48px">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item <?php echo $menu[0];?>">
                    <a class="nav-link" href="consulta_materiais.php">Consulta de materiais</a>
                </li>
                <li class="nav-item <?php echo $menu[1];?>">
                    <a class="nav-link" href="javascript:void(0)" onclick="verificaPermissaoDesconto()">Pedidos</a>
                </li>
            </ul>
            <div class="ml-auto">
                <span><i class="far fa-user"></i> <?php echo $_SESSION['nome_usuario'];?></span>
                <button class="btn btn-danger" onclick="logout()">Sair</button>
            </div>
        </div>
    </div>
</nav>