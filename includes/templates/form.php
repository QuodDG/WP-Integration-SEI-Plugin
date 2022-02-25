<div class="qdo-isei-form">
    <div class="qdo-isei-steps-container" id="i__qdo-isei-steps-container">
        <p class="qdo-isei-steps">
            Etapa <span class="qdo-isei-current-step" id="i__qdo-isei-current-step">1</span><span class="qdo-isei-max-steps">/3</span>
        </p>
    </div>

    <form 
        action="#" 
        method="POST" 
        enctype="multipart/form-data" 
        autocomplete="off" 
        class="qdo-isei-form_form" 
        id="i__qdo-isei-form" 
        data-p0="<?= $rsk ?>" 
        data-p1="<?= $proc_type ?>" 
        data-p2="<?= $ano ?>" 
        data-p3="<?= $semestre ?>" 
        data-p4="<?= $campanha ?>">
        <!-- Step 1 -->
        <div class="qdo-isei-form_row qdo-isei-form_step" id="i__qdo-isei-form_step-1">
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Seu nome completo*</label>
                <input type="text" name="isei_ps_nome" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Seu e-mail*</label>
                <input type="email" name="isei_ps_email" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Confirme seu e-mail*</label>
                <input type="email" name="isei_ps_email_2" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Seu telefone*</label>
                <input type="tel" name="isei_ps_celular" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <hr class="qdo-isei-form_divider">
                <span class="qdo-isei-legend">Ao clicar em <strong>Continuar</strong>, você concorda em receber comunicações da FADAT relacionadas a sua inscrição e aos Processos Seletivos da FADAT.</span>
                <a href="#" class="qdo-isei-form_button g-recaptcha" id="i__qdo-isei-form_button-step-1">Continuar</a>
            </div>
        </div>
        <!-- End Step 1 -->
        <!-- Step 2 -->
        <div class="qdo-isei-form_row qdo-isei-form_step qdo-isei-form_step-hide" id="i__qdo-isei-form_step-2">
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Seu CPF*</label>
                <input type="text" name="isei_ps_cpf" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Sua data de nascimento*</label>
                <input type="date" name="isei_ps_dt_nascimento" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Sexo*</label>
                <select name="isei_ps_sexo" class="qdo-isei-form_select" required>
                    <option value="">Escolha uma opção</option>
                    <option value="F">Feminino</option>
                    <option value="M">Masculino</option>
                    <option value="P">Prefiro não responder</option>
                </select>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <hr class="qdo-isei-form_divider">
                <a href="#" class="qdo-isei-form_button qdo-isei-form_button-back" id="i__qdo-isei-form_button-back-step-2">Voltar</a>
                <a href="#" class="qdo-isei-form_button g-recaptcha" id="i__qdo-isei-form_button-step-2">Continuar</a>
            </div>
        </div>
        <!-- End Step 2 -->
        <!-- Step 3 -->
        <div class="qdo-isei-form_row qdo-isei-form_step qdo-isei-form_step-hide" id="i__qdo-isei-form_step-3">
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Processo Seletivo*</label>
                <select name="isei_ps_ps" class="qdo-isei-form_select qdo-isei-select-ps qdo-isei-prop-disabled" id="i__qdo-isei-select-ps" disabled required>
                    <option value="">Escolha uma opção</option>
                </select>
                <p class="qdo-isei-select-ps_alter" id="i__qdo-isei-select-ps_alter"></p>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <label class="qdo-isei-form_label">Curso*</label>
                <select name="isei_ps_uni_ens_curso" class="qdo-isei-form_select qdo-isei-select-curso qdo-isei-prop-disabled" id="i__qdo-isei-select-curso" disabled required>
                    <option value="">Escolha uma opção</option>
                </select>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100 qdo-isei_compr_enem" id="i__qdo-isei_compr_enem">
                <label class="qdo-isei-form_label">
                    Comprovante de Notas do ENEM*
                    <span class="qdo-isei-form_label_legend">(Somente arquivos em PDF)</span>
                </label>
                <input type="file" name="isei_ps_compr_enem" class="qdo-isei-form_input" required>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <hr class="qdo-isei-form_divider">
                <span class="qdo-isei-legend">Ao clicar em <strong>Finalizar a inscrição</strong>, você confirma que as suas informações pessoais, acima prestadas, são verdadeiras e assume a inteira responsabilidade pelas mesmas.</span>
                <a href="#" class="qdo-isei-form_button qdo-isei-form_button-back" id="i__qdo-isei-form_button-back-step-3">Voltar</a>
                <a href="#" class="qdo-isei-form_button g-recaptcha" id="i__qdo-isei-form_button-step-3">Finalizar a inscrição</a>
            </div>
        </div>
        <!-- End Step 3 -->
        <!-- Step 4 -->
        <div class="qdo-isei-form_row qdo-isei-form_step qdo-isei-form_step-hide" id="i__qdo-isei-form_step-4">
            <div class="qdo-isei-inscr-container">
                <h3 class="qdo-isei-inscr-title" id="i__qdo-isei-inscr-title"></h3>
                <p class="qdo-isei-inscr-text" id="i__qdo-isei-inscr-text1"></p>
                <ul class="qdo-isei-inscr-data" id="i__qdo-isei-inscr-data">
                    <li class="qdo-isei-inscr-data_item">Inscrição Nº: <span class="qdo-isei-inscr-data_item_value"></span></li>
                    <li class="qdo-isei-inscr-data_item">Processo Seletivo: <span class="qdo-isei-inscr-data_item_value"></span></li>
                    <li class="qdo-isei-inscr-data_item">Curso: <span class="qdo-isei-inscr-data_item_value"></span></li>
                </ul>
                <p class="qdo-isei-inscr-text" id="i__qdo-isei-inscr-text2"></p>
            </div>
            <div class="qdo-isei-form_col qdo-isei-form_col-100">
                <hr class="qdo-isei-form_divider">
                <a href="#" class="qdo-isei-form_button g-recaptcha" id="i__qdo-isei-form_button-step-4">Fazer outra inscrição</a>
            </div>
        </div>
        <!-- End Step 4 -->
    </form>
    <div class="qdo-isei-content-message" id="i__qdo-isei-content-message"></div>
</div>