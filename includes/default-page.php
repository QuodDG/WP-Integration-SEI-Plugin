<?php
$map = qdo_isei_get_map_options();
$filtered_post = filter_input_array(INPUT_POST);

if (is_array($filtered_post) && count($filtered_post)) :
    $r = qdo_isei_save_options($map);

    if ($r) :
        ?>
        <div class="alert alert-success">
            <i class="fas fa-info-circle"></i> Tudo ocorreu como planejado!
        </div>
    <?php else : ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Nem todos os campos foram atualziados!
        </div>
    <?php
    endif;
endif;
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css" integrity="sha384-3AB7yXWz4OeoZcPbieVW64vVXEwADiYyAEhwilzWsLw+9FgqpyjjStpPnpBO8o8S" crossorigin="anonymous">
<style>
    .qdo-isei-content-message{
        display: none;
        background: #FFF;
        border: #999 solid 1px;
        border-left-style: solid;
        border-left-width: 6px;
        padding: 5px;
    }

    .qdo-isei-content-message-success{
        border-color: #00A553;
        color: #00A553;
    }

    .qdo-isei-content-message-error{
        border-color: #AA0000;
        color: #AA0000;
    }
</style>
<div class="wrap">
    <div class="row justify-content-center">
        <div class="col-lx-8 col-lg-8 col-md-8 col-sm-12 col-12">
            <div class="qdo-isei-content-message" id="i__qdo-isei-content-message"></div>
            <p>[isei_form proc_type=vestibular-tradicional ano=2022 semestre=1 campanha=vestibular-tradicional2022.1]</p>
            <p>[isei_form proc_type=vestibular-online ano=2022 semestre=1 campanha=vestibular-online2022.1]</p>
            <p>[isei_form proc_type=enem ano=2022 semestre=1 campanha=vestibular-simplificado2022.1]</p>
        </div>
        <div class="col-lx-8 col-lg-8 col-md-8 col-sm-12 col-12">
            <h2>Autenticação com o RD Station</h2>
            <?php
            $rd_integration_stage = qdo_isei_get_rd_integration_stage();
            if ($rd_integration_stage === QDO_RD_INTEGRATION_OFF) :
                ?>
                <a class="btn btn-outline-primary" 
                   href="https://api.rd.services/auth/dialog?client_id=<?= get_option(QDO_RD_CLIENT_ID) ?>&redirect_uri=<?= urlencode(qdo_isei_get_map_option(QDO_RD_REDIRECT_URI)) ?>">
                    Autenticar
                </a>
            <?php elseif ($rd_integration_stage === QDO_RD_INTEGRATION_ON) : ?>
                <a class="btn btn-outline-danger"
                   id="i__qdo-isei-rd-lougout-btn"
                   href="#"
                   data-rdcode="<?= get_option(QDO_RD_CODE) ?>" 
                   data-rdtoken="<?= get_option(QDO_RD_TOKEN) ?>" 
                   data-rdrefreshtoken="<?= get_option(QDO_RD_REFRESH_TOKEN) ?>"
                   data-rdexpirein="<?= get_option(QDO_RD_TOKEN_EXPIRE_IN) ?>"
                   data-now="<?= time() ?>"
                   data-hk="<?= QDO_ISEI_PRIVATE_HASH ?>">
                    Desconectar
                </a>
            <?php else: ?>
                <p>Configurações para inegração com o RD Station Marketing inexistenes ou incompletas.</p>
            <?php endif; ?>

            <hr>

            <h2>Configurações</h2>

            <form action="#" method="POST">
                <div class="row">

                    <?php foreach ($map as $section => $options) : ?>

                        <div class="col-12">
                            <h4><?= $section ?></h4>
                        </div>

                        <?php foreach ($options as $opt) :
                            if(qdo_isei_has_depend($opt) && !qdo_isei_check_depend_on($options, $opt)){
                                continue;
                            }
                            ?>

                            <div class="<?= $opt['classes'] ?>">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><?= $opt['label'] ?></span>
                                    </div>
                                    <?php if ($opt['field_type'] === 'select') : ?>
                                        <select name="<?= $opt['name'] ?>" class="form-control<?= $opt['status_classes'] ?>" title="<?= $opt['name'] ?>">
                                            <?php
                                            $options = call_user_func($opt['options'], $opt['value']);
                                            foreach ($options as $option) :
                                                ?>
                                                <option value="<?= $option['value'] ?>" <?= $option['selected'] ?>><?= $option['label'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php elseif ($opt['field_type'] === 'input') : ?>
                                        <input type="<?= $opt['type'] ?>" name="<?= $opt['name'] ?>" class="form-control<?= $opt['status_classes'] ?>" value="<?= $opt['value'] ?>" title="<?= $opt['name'] ?>" placeholder="<?= $opt['place_holder'] ?>">
                                    <?php endif; ?>
                                </div>
                            </div>

                        <?php endforeach; ?>

                        <div class="col-12"><hr></div>

                    <?php endforeach; ?>

                    <div class="col-12 d-flex justify-content-center">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
add_filter('admin_footer_text', function() {
    echo '<span>Desenvolvido por <a href="https://github.com/QuodDG" target="_blank">QuodDG</a><span>';
});
?>