(function ($) {
    let qdo_isei_form = $('#i__qdo-isei-form');
    if (qdo_isei_form.length) {
        //Step containers
        let qdo_isei_form_step_1 = $('#i__qdo-isei-form_step-1');
        let qdo_isei_form_step_2 = $('#i__qdo-isei-form_step-2');
        let qdo_isei_form_step_3 = $('#i__qdo-isei-form_step-3');
        let qdo_isei_form_step_4 = $('#i__qdo-isei-form_step-4');
        let qdo_isei_select_ps_alter = $('#i__qdo-isei-select-ps_alter');
        //Next buttons
        let qdo_isei_form_button_step_1 = $('#i__qdo-isei-form_button-step-1');
        let qdo_isei_form_button_step_2 = $('#i__qdo-isei-form_button-step-2');
        let qdo_isei_form_button_step_3 = $('#i__qdo-isei-form_button-step-3');
        let qdo_isei_form_button_step_4 = $('#i__qdo-isei-form_button-step-4');
        //Previous buttons
        let qdo_isei_form_button_back_step_2 = $('#i__qdo-isei-form_button-back-step-2');
        let qdo_isei_form_button_back_step_3 = $('#i__qdo-isei-form_button-back-step-3');
        //Step indicator
        let qdo_isei_steps_container = $('#i__qdo-isei-steps-container');
        let qdo_isei_current_step = $('#i__qdo-isei-current-step');
        //Finish step data
        let qdo_isei_inscr_title = $('#i__qdo-isei-inscr-title');
        let qdo_isei_inscr_text1 = $('#i__qdo-isei-inscr-text1');
        let qdo_isei_inscr_text2 = $('#i__qdo-isei-inscr-text2');
        let qdo_isei_inscr_data = $('#i__qdo-isei-inscr-data');
        //Configurations
        const rsk = qdo_isei_form.attr('data-p0');
        const proc_type = qdo_isei_form.attr('data-p1');
        const ano = qdo_isei_form.attr('data-p2');
        const semestre = qdo_isei_form.attr('data-p3');
        const campanha = qdo_isei_form.attr('data-p4');
        //Remover os atributos
        qdo_isei_form
                .removeAttr('data-p0')
                .removeAttr('data-p1')
                .removeAttr('data-p2')
                .removeAttr('data-p3')
                .removeAttr('data-p4')
                .on('submit', function (e) {
                    e.preventDefault();
                    return false;
                });

        qdo_isei_form_step_1.find('input').bind('paste', function (e) {
            e.preventDefault();
        });

        //First Step
        //Next button
        qdo_isei_form_button_step_1.on('click', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled')) {
                return;
            }
            grecaptcha.ready(function () {
                grecaptcha.execute(rsk, {action: 'advanced_nocaptcha_recaptcha'}).then(function (response) {
                    //console.log(response);
                    isei_request_step_1(response);
                });
            });
        });

        //Second Step
        //Next button
        qdo_isei_form_button_step_2.on('click', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled')) {
                return;
            }
            grecaptcha.ready(function () {
                grecaptcha.execute(rsk, {action: 'advanced_nocaptcha_recaptcha'}).then(function (response) {
                    //console.log(response);
                    isei_request_step_2(response);
                });
            });
        });
        //Previous button
        qdo_isei_form_button_back_step_2.on('click', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled')) {
                return;
            }
            isei_back_step(2);
        });

        //Third Step
        //Next button
        qdo_isei_form_button_step_3.on('click', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled')) {
                return;
            }
            grecaptcha.ready(function () {
                grecaptcha.execute(rsk, {action: 'advanced_nocaptcha_recaptcha'}).then(function (response) {
                    //console.log(response);
                    isei_request_step_3(response);
                });
            });
        });
        //Previous button
        qdo_isei_form_button_back_step_3.on('click', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled')) {
                return;
            }
            isei_back_step(3);
        });
        //Fourth Step
        //Finish button
        qdo_isei_form_button_step_4.on('click', function (e) {
            e.preventDefault();
            $(this).prop('disabled', true);
            location.reload();
        });

        //Listeners
        listener_ps_select();

        console.log('QdoIntegrationSei initialized');

        /* == Steps - Send ==*/

        function isei_request_step_1(recaptcha) {
            //Valida os dados do formulário
            if (!isei_valida_form(1)) {
                isei_show_message('Há campos obrigatórios a serem preenchidos ou preenchidos incorretamente', 'error');
                return;
            }

            //Monta o formulário
            let form = new FormData();
            form.append('isei_ps_nome', qdo_isei_form.find('input[name=isei_ps_nome]').val());
            form.append('isei_ps_email', qdo_isei_form.find('input[name=isei_ps_email]').val());
            form.append('isei_ps_email_2', qdo_isei_form.find('input[name=isei_ps_email_2]').val());
            form.append('isei_ps_celular', qdo_isei_form.find('input[name=isei_ps_celular]').val());
            form.append('isei_ps_proc_type', proc_type);
            form.append('isei_ps_ano', ano);
            form.append('isei_ps_semestre', semestre);
            form.append('isei_ps_campanha', campanha);
            form.append('isei_ps_recaptcha', recaptcha);

            //Requisição POST
            isei_request({
                url: `${location.origin}/wp-json/isei/v2/ps/step-1`,
                callback: isei_response_step_1,
                btn: qdo_isei_form_button_step_1,
                method: 'POST',
                data: form,
                contentType: false,
                processData: false,
            });
        }

        function isei_request_step_2(recaptcha) {
            //Valida os dados do formulário
            if (!isei_valida_form(2)) {
                isei_show_message('Há campos obrigatórios a serem preenchidos ou preenchidos incorretamente', 'error');
                return;
            }

            //Monta o formulário
            let form = new FormData();
            form.append('isei_ps_proc_type', proc_type);
            form.append('isei_ps_recaptcha', recaptcha);

            //Requisição POST
            isei_request({
                url: `${location.origin}/wp-json/isei/v2/ps/step-2`,
                callback: isei_response_step_2,
                btn: qdo_isei_form_button_step_2,
                btn_back: qdo_isei_form_button_back_step_2,
                method: 'POST',
                data: form,
                contentType: false,
                processData: false,
            });
        }

        function isei_request_step_3(recaptcha) {
            //Valida os dados do formulário
            if (!isei_valida_form(3)) {
                isei_show_message('Há campos obrigatórios a serem preenchidos ou preenchidos incorretamente', 'error');
                return;
            }

            //Monta o formulário
            let form = new FormData(qdo_isei_form[0]);
            form.append('isei_ps_proc_type', proc_type);
            form.append('isei_ps_ano', ano);
            form.append('isei_ps_semestre', semestre);
            form.append('isei_ps_campanha', campanha);
            form.append('isei_ps_recaptcha', recaptcha);

            //Requisição POST
            isei_request({
                url: `${location.origin}/wp-json/isei/v2/ps/step-3`,
                callback: isei_response_step_3,
                btn: qdo_isei_form_button_step_3,
                btn_back: qdo_isei_form_button_back_step_3,
                method: 'POST',
                data: form,
                contentType: false,
                processData: false,
            });
        }

        /*== Steps - Results ==*/

        function isei_response_step_1(response) {
            //Verifica se houve erro na requisição
            if (response.error) {
                console.log(response);
                isei_show_message(response.message, 'error');
                return;
            }

            //Avança para a próxima etapa
            isei_change_step(1);
        }

        function isei_response_step_2(response) {
            //Verifica se houve erro na requisição
            if (response.error) {
                isei_show_message(response.message, 'error');
                return;
            }

            //Captura a tag de Processos Seletivos
            let ps_select_tag = $('#i__qdo-isei-select-ps');
            //Limpa a tag de Processos Seletivos
            isei_clear_select(ps_select_tag);

            //Preenche o campo de Processos Seletivos
            response.data.procSeletivo.forEach(function (ps) {
                ps_select_tag.append(
                        isei_c__option(
                                ps.descricao,
                                ps.codigo,
                                ps.listaTipoIngressoProcSeletivo.chave
                                )
                        );
            });

            if (response.data.procSeletivo.length === 1) {
                ps_select_tag.find('option:eq(1)').prop('selected', true);
                ps_select_tag.change();
                ps_select_tag.hide();
                qdo_isei_select_ps_alter.text(
                        ps_select_tag.find('option:selected').text()
                        ).show();
            } else {
                ps_select_tag.find('option:eq(0)').prop('selected', true);
                ps_select_tag.show();
                qdo_isei_select_ps_alter.hide().text('');
            }

            //Avança para a próxima etapa
            isei_change_step(1);
        }

        function isei_response_step_3(response) {
            //Verifica se houve erro na requisição
            if (response.error) {
                isei_show_message(response.message, 'error');
                return;
            }

            //Montando a tela final
            qdo_isei_inscr_title.text(response.data.title);
            qdo_isei_inscr_text1.text(response.data.text_1);
            qdo_isei_inscr_text2.text(response.data.text_2);
            qdo_isei_inscr_data.find('li:eq(0) span').text(response.data.inscr_number);
            qdo_isei_inscr_data.find('li:eq(1) span').text(response.data.ps);
            qdo_isei_inscr_data.find('li:eq(2) span').text(response.data.curso);

            //Avança para a próxima etapa
            isei_change_step(1);
        }

        function isei_render_curso_select(response) {
            //Verifica se houve erro na requisição
            if (response.error) {
                //Captura a tag de Processos Seletivos
                let ps_select_tag = $('#i__qdo-isei-select-ps');
                //Rederine a seleção do campo de processos seletivos
                ps_select_tag.find('option:eq(0)').prop('selected', true);
                isei_show_message(response.message, 'error');
                return;
            }

            //Captura a tag de Cursos
            let cursos_select_tag = $('#i__qdo-isei-select-curso');
            //Limpa a tag de Cursos
            isei_clear_select(cursos_select_tag);

            //Preenche o campo de Cursos
            response.data.procSeletivoCurso.forEach(function (curso) {
                //let label = `${curso.unidadeEnsinoCurso.curso.nome} - ${curso.unidadeEnsinoCurso.turno.nome}`;
                let label = curso.unidadeEnsinoCurso.curso.nome;
                cursos_select_tag.append(
                        isei_c__option(
                                label,
                                curso.unidadeEnsinoCurso.codigo,
                                curso.unidadeEnsinoCurso.codigo
                                )
                        );
            });

            //Habilita o campo de cursos
            isei_toggle_field(cursos_select_tag);
        }

        /*== Listeners ==*/

        function listener_ps_select() {
            //Captura a tag de Processos Seletivos
            let ps_select_tag = $('#i__qdo-isei-select-ps');
            //.i__isei-select-ps
            ps_select_tag.on('change', function (e) {
                let ps_code = $(this).val();
                //captura o tipo do processo seletivo selecionado
                let ps_type = $(this).find('option:selected').attr('data-ref');
                //Captura a tag de Cursos
                let cursos_select_tag = $('#i__qdo-isei-select-curso');
                //Captura o bloco do campo de upload
                let compr_enem_block = $('#i__qdo-isei_compr_enem');
                //Captura o campo de upload
                let input_compr_enem = compr_enem_block.find('input[name=isei_ps_compr_enem]');

                if (ps_code === '') {
                    //Limpa a tag de Cursos
                    isei_clear_select(cursos_select_tag);
                    //Esconde o bloco de upload de arquivo
                    compr_enem_block.hide();
                    //Remove o sinalizador de campo obrigatório 
                    //do upload de arquivo
                    input_compr_enem.prop('required', false);
                    return;
                }

                //Verifica se o tipo de processo seletivo requer upload de arquivos
                //para mostrar ou esconder o campo
                if (ps_type === 'EN') {
                    compr_enem_block.show();
                    input_compr_enem.prop('required', true);
                } else {
                    compr_enem_block.hide();
                    input_compr_enem.prop('required', false);
                }

                //Desabilita o campo de cursos
                isei_toggle_field(cursos_select_tag, false, true);
                //Limpa a tag de Cursos
                isei_clear_select(cursos_select_tag);

                isei_request({
                    url: `${location.origin}/wp-json/isei/v2/ps/step-21/${ps_code}`,
                    callback: isei_render_curso_select
                });
            });
        }

        /*== Componentes ==*/

        function isei_c__option(label, value, data_ref) {
            let option = $('<option></option>');
            option.attr('value', value).attr('data-ref', data_ref).text(label);
            return option;
        }

        /*== Auxiliares ==*/

        function isei_toggle_field(field, disabled, readonly) {
            if (disabled || readonly) {
                field.addClass('qdo-isei-prop-disabled');
            } else {
                field.removeClass('qdo-isei-prop-disabled');
            }
            field.prop('disabled', (disabled !== undefined && disabled ? disabled : false));
            field.prop('readonly', (readonly !== undefined && readonly ? readonly : false));
        }

        function isei_back_step(current_step) {
            if (current_step > 1 || current_step < 4) {
                //Volta para a etapa anterior
                isei_change_step(-1);
            }
        }

        function isei_change_step(modifier) {
            if (modifier !== -1 && modifier !== 1) {
                return;
            }

            //Captura o número da etapa atual
            let current_step = parseInt(qdo_isei_current_step.text());
            //Modifica o número da etapa atual
            let new_step = current_step + modifier;
            //Captura a tag de Processos Seletivos
            let ps_select_tag = $('#i__qdo-isei-select-ps');

            if (modifier === 1) {
                if (current_step === 1) {
                    //Esconde a etapa atual
                    qdo_isei_form_step_1.hide();
                    //Desabilita os campos da etapada atual
                    isei_toggle_field(qdo_isei_form_step_1.find('input, select'), false, true);
                    //Mostra a próxima etapa
                    qdo_isei_form_step_2.show();
                } else if (current_step === 2) {
                    //Esconde a etapa atual
                    qdo_isei_form_step_2.hide();
                    //Desabilita os campos da etapada atual
                    isei_toggle_field(qdo_isei_form_step_2.find('input, select'), false, true);
                    //Habilita o campo de processos seletivos
                    isei_toggle_field(ps_select_tag);
                    //Mostra a próxima etapa
                    qdo_isei_form_step_3.show();
                } else if (current_step === 3) {
                    //Esconde a etapa atual
                    //qdo_isei_form_step_3.hide();
                    //Destroi as etapas anteriores
                    qdo_isei_form_step_1.remove();
                    qdo_isei_form_step_2.remove();
                    qdo_isei_form_step_3.remove();
                    //Esconde o indicador de etapa
                    qdo_isei_steps_container.hide();
                    //Desabilita os campos da etapada atual
                    qdo_isei_form_step_3.find('input, select').prop('readonly', true);
                    //Mostra a próxima etapa
                    qdo_isei_form_step_4.show();
                }
            } else if (modifier === -1) {
                if (current_step === 3) {
                    //Esconde a etapa atual
                    qdo_isei_form_step_3.hide();
                    //Habilita os campos da etapada anterior
                    isei_toggle_field(qdo_isei_form_step_2.find('input, select'));
                    //Desabilita o campo de processos seletivos
                    isei_toggle_field(ps_select_tag, true);
                    //Mostra a próxima etapa
                    qdo_isei_form_step_2.show();
                } else if (current_step === 2) {
                    //Esconde a etapa atual
                    qdo_isei_form_step_2.hide();
                    //Habilita os campos da etapada anterior
                    isei_toggle_field(qdo_isei_form_step_1.find('input, select'));
                    //Mostra a próxima etapa
                    qdo_isei_form_step_1.show();
                }
            }

            qdo_isei_current_step.text(new_step);
        }

        function isei_clear_select(select_tag) {
            let opt_default = select_tag.find('option').eq(0);
            select_tag.find('option').remove();
            select_tag.append(opt_default);
        }

        function isei_show_message(text, type) {
            var message = $('#i__qdo-isei-content-message');
            if (type === 'success') {
                message.addClass('qdo-isei-content-message-success');
            } else if (type === 'error') {
                message.addClass('qdo-isei-content-message-error');
            }
            message.show().html(text);
        }

        function isei_clear_message() {
            var message = $('#i__qdo-isei-content-message');
            message
                    .hide()
                    .removeClass('qdo-isei-content-message-success')
                    .removeClass('qdo-isei-content-message-error')
                    .html('');
        }

        /**
         * 
         * @param {type} options [url, data, method, btn, btn_back, contentType, processData, callback]
         * @returns {undefined}
         */
        function isei_request(options) {
            let btn_label = '';
            if (options.btn !== undefined) {
                btn_label = $(options.btn).text();
                $(options.btn).prop('disabled', true).addClass('qdo-isei-prop-disabled').text('...');
            }
            if (options.btn_back !== undefined) {
                $(options.btn_back).prop('disabled', true).addClass('qdo-isei-prop-disabled');
            }
            let ajax_options = {
                data: options.data,
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', qdoIseiSettings.nonce);
                },
                error: function (jqXHR, textStatus) {
                    //console.log(jqXHR);
                    isei_show_message('Não foi possível completar a requisição.', 'error');
                    //alert('Não foi possível completar a requisição.');
                    if (options.btn !== undefined) {
                        $(options.btn).prop('disabled', false).removeClass('qdo-isei-prop-disabled').text(btn_label);
                    }
                    if (options.btn_back !== undefined) {
                        $(options.btn_back).prop('disabled', false).removeClass('qdo-isei-prop-disabled');
                    }
                },
                method: options.method === undefined ? 'GET' : options.method,
                success: function (response) {
                    if (options.btn !== undefined) {
                        $(options.btn).prop('disabled', false).removeClass('qdo-isei-prop-disabled').text(btn_label);
                    }
                    if (options.btn_back !== undefined) {
                        $(options.btn_back).prop('disabled', false).removeClass('qdo-isei-prop-disabled');
                    }
                    options.callback(response);
                }
            };
            if (options.contentType !== undefined) {
                ajax_options.contentType = options.contentType;
            }
            if (options.processData !== undefined) {
                ajax_options.processData = options.processData;
            }
            isei_clear_message();
            $.ajax(options.url, ajax_options);
        }

        function isei_valida_form(step) {
            let result = true;
            //Monta o formulário
            let form = new FormData(qdo_isei_form[0]);
            isei_clear_validation_aparence_form();

            //Valida a etapa 1
            if (step >= 1) {
                let qt_nomes = form.get('isei_ps_nome').split(' ');
                if (isei_is_field_empy(form.get('isei_ps_nome')) || qt_nomes.length <= 1) {
                    qdo_isei_form.find('input[name=isei_ps_nome]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
                if (isei_is_field_empy(form.get('isei_ps_email'))) {
                    qdo_isei_form.find('input[name=isei_ps_email]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
                if (form.get('isei_ps_email') !== form.get('isei_ps_email_2')) {
                    qdo_isei_form.find('input[name=isei_ps_email_2]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
                if (isei_is_field_empy(form.get('isei_ps_celular'))) {
                    qdo_isei_form.find('input[name=isei_ps_celular]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
            }

            //Valida a etapa 2
            if (step >= 2) {
                if (!isei_valida_cpf(form.get('isei_ps_cpf'))) {
                    qdo_isei_form.find('input[name=isei_ps_cpf]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
                if (isei_is_field_empy(form.get('isei_ps_dt_nascimento'))) {
                    qdo_isei_form.find('input[name=isei_ps_dt_nascimento]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
                if (isei_is_field_empy(form.get('isei_ps_sexo'))) {
                    qdo_isei_form.find('select[name=isei_ps_sexo]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
            }

            //Valida a etapa 3
            if (step >= 3) {
                if (isei_is_field_empy(form.get('isei_ps_ps'))) {
                    qdo_isei_form.find('select[name=isei_ps_ps]').addClass('qdo-isei-form_field-error');
                    result = false;
                }

                if (isei_is_field_empy(form.get('isei_ps_uni_ens_curso'))) {
                    qdo_isei_form.find('select[name=isei_ps_uni_ens_curso]').addClass('qdo-isei-form_field-error');
                    result = false;
                }

                //Captura a tag de Processos Seletivos
                let ps_select_tag = $('#i__qdo-isei-select-ps');
                //Captura o identificador do processo seletivo selecionado
                let ps_type = ps_select_tag.find('option:selected').attr('data-ref');

                if (ps_type === 'EN' && (form.get('isei_ps_compr_enem').size === 0 || form.get('isei_ps_compr_enem').type !== 'application/pdf')) {
                    qdo_isei_form.find('input[name=isei_ps_compr_enem]').addClass('qdo-isei-form_field-error');
                    result = false;
                }
            }

            return result;
        }

        function isei_is_field_empy(field) {
            return field === '' || field === null;
        }

        function isei_clear_validation_aparence_form() {
            qdo_isei_form.find('input, select').removeClass('qdo-isei-form_field-error');
        }

        //https://www.devmedia.com.br/validar-cpf-com-javascript/23916
        function isei_valida_cpf(str_cpf) {
            var Soma;
            var Resto;
            Soma = 0;
            str_cpf = str_cpf.replace(/\D/g, '').substr(0, 11);
            if (str_cpf === "00000000000" ||
                    str_cpf === "11111111111" ||
                    str_cpf === "22222222222" ||
                    str_cpf === "33333333333" ||
                    str_cpf === "44444444444" ||
                    str_cpf === "55555555555" ||
                    str_cpf === "66666666666" ||
                    str_cpf === "77777777777" ||
                    str_cpf === "88888888888" ||
                    str_cpf === "99999999999" ||
                    str_cpf.length !== 11)
                return false;
            for (i = 1; i <= 9; i++)
                Soma = Soma + parseInt(str_cpf.substring(i - 1, i)) * (11 - i);
            Resto = (Soma * 10) % 11;
            if ((Resto === 10) || (Resto === 11))
                Resto = 0;
            if (Resto !== parseInt(str_cpf.substring(9, 10)))
                return false;
            Soma = 0;
            for (i = 1; i <= 10; i++)
                Soma = Soma + parseInt(str_cpf.substring(i - 1, i)) * (12 - i);
            Resto = (Soma * 10) % 11;
            if ((Resto === 10) || (Resto === 11))
                Resto = 0;
            if (Resto !== parseInt(str_cpf.substring(10, 11)))
                return false;
            return true;
        }
    }
})(jQuery);