<style>
    .newCsr {
        display: none;
    }
</style>
<div>
    <div class="col-md-6">
        <h2>{WHMCS\Module\Addon\domenytv\Lang::t('generate_csr_title')}</h2>
        <div class="panel panel-danger">
            <div class="panel-body">
                {WHMCS\Module\Addon\domenytv\Lang::t('generate_csr_warning')}

            </div>
        </div>

        <table class="form" width="100%" cellspacing="2" cellpadding="3" border="0">
            <tbody>
                <tr>
                    <td class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('domain_name')}</td>
                    <td class="fieldarea">
                        <input name="csr_domain" class="form-control input-300" maxlength="63" type="text">({WHMCS\Module\Addon\domenytv\Lang::t('domain_name_description')})
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('email')}</td>
                    <td class="fieldarea">
                        <input name="csr_email" class="form-control input-300" type="text">
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('organization')}</td>
                    <td class="fieldarea">
                        <input name="csr_organization" class="form-control input-300" type="text">({WHMCS\Module\Addon\domenytv\Lang::t('organization_description')})
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('unit')}</td>
                    <td class="fieldarea">
                        <input name="csr_unit" class="form-control input-300" type="text">({WHMCS\Module\Addon\domenytv\Lang::t('unit_description')})
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('city')}</td>
                    <td class="fieldarea">
                        <input name="csr_city" class="form-control input-300" type="text">
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('district')}</td>
                    <td class="fieldarea">
                        <input name="csr_state" class="form-control input-300" type="text">
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('key_strength')}</td>
                    <td class="fieldarea">
                        <select name="csr_key" class="form-control select-inline">
                            <option value="0">2048 - {WHMCS\Module\Addon\domenytv\Lang::t('key_strength_recommended_value')}</option>
                            <option value="1">4096 - {WHMCS\Module\Addon\domenytv\Lang::t('key_strength_highest')}</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('country')}</td>
                    <td>
                        <select name="csr_country" class="form-control select-inline">
                            {foreach from=$countries key=value item=name}
                                <option {if $value == 'PL'}selected{/if} value="{$value}">{$name}</option>
                            {/foreach}
                        </select>
                    </td>
                </tr>
                <tr class="newCsr">
                    <td  class="fieldlabel">CSR</td>
                    <td class="fieldarea">
                        <textarea name="new_csr" rows="15" class="form-control" tabindex="29" readonly></textarea>
                    </td>
                </tr>
                <tr class="newCsr">
                    <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('private_key')}</td>
                    <td class="fieldarea">
                        <textarea name="new_private_key" rows="15" class="form-control" tabindex="29" readonly></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="btn-container">
            <input value="{WHMCS\Module\Addon\domenytv\Lang::t('button_generate_csr')}" tabindex="31" class="btn btn-primary newCsr-button" type="submit">
        </div>
    </div>
    <div class="col-md-6"> 
        <h2>{WHMCS\Module\Addon\domenytv\Lang::t('ssl_order_form_title')}</h2> 
        <form id="orderSslForm" method="post" action="addonmodules.php?module=domenytv&action=sslAdd">
            <table class="form" width="100%" cellspacing="2" cellpadding="3" border="0">
                <tbody>
                    <tr>
                        <td  class="fieldlabel">CSR</td>
                        <td class="fieldarea">
                            <textarea name="csr" rows="15" class="form-control csr-field" tabindex="29"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td  width="130" class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('ssl_type')}</td>
                        <td class="fieldarea">
                            <select name="product" class="form-control select-inline">
                                {foreach from=$sslTypes key=value item=name}
                                    <option value="{$value}">{$name}</option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('name')}</td>
                        <td class="fieldarea">
                            <input name="first_name" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('last_name')}</td>
                        <td class="fieldarea">
                            <input name="last_name" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('phone')}</td>
                        <td class="fieldarea">
                            <input name="phonenumber" class="form-control input-300" type="text">(+XX.XXXXXXXXX)
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('email')}</td>
                        <td class="fieldarea">
                            <input name="email" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('tech_email')}</td>
                        <td class="fieldarea">
                            <div class="input-group"> 
                                <select class="form-control" name="admin_email">
                                    <option value="webmaster">webmaster</option>
                                    <option value="admin">admin</option>
                                    <option value="administrator">administrator</option>
                                    <option value="hostmaster">hostmaster</option>
                                    <option value="postmaster">postmaster</option>
                                </select>
                                <span class="input-group-addon csr-domain-name" > -- nieprawdiłowy CSR -- </span> 
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('company')}</td>
                        <td class="fieldarea">
                            <input name="company" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">NIP</td>
                        <td class="fieldarea">
                            <input name="nip" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('address')}</td>
                        <td class="fieldarea">
                            <input name="address" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('city')}</td>
                        <td class="fieldarea">
                            <input name="city" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('district')}</td>
                        <td class="fieldarea">
                            <input name="district" class="form-control input-300" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('zip')}</td>
                        <td class="fieldarea">
                            <input name="zip" class="form-control input-80" type="text">
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('country')}</td>
                        <td class="fieldarea">
                            <select name="country" class="form-control select-inline">
                                {foreach from=$countries key=value item=name}
                                    <option {if $value == 'PL'}selected{/if} value="{$value}">{$name}</option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td  class="fieldlabel">{WHMCS\Module\Addon\domenytv\Lang::t('period')}</td>
                        <td>
                            <select name="period" class="form-control select-inline input-80">
                                <option value="1"> {WHMCS\Module\Addon\domenytv\Lang::t('period_1_year')}</option>
                                <option value="2"> {WHMCS\Module\Addon\domenytv\Lang::t('period_2_years')}</option>
                            </select>
                        </td>
                    </tr>

                    <tr>

                    </tr>
                </tbody>
            </table>
            <div class="btn-container">
                <input value="{WHMCS\Module\Addon\domenytv\Lang::t('button_order_ssl')}" tabindex="31" class="btn btn-primary newSsl-button" type="button">
            </div>
        </form>
    </div>
</div>

<script>
    function verifySslOrderData() {
        var formData = {};
        $('#orderSslForm').find('select, input, textarea').each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();

            if ($(this).is('select')) {
                $(this).find(":selected").val();
            }

            formData[name] = value;
        });

        ajx.post('Api_verify_ssl_order', formData, function (data) {
            $('#orderSslForm').submit();
        });

    }


    $('.newCsr-button').click(function () {
        var data = {
            csr_country: $('select[name="csr_country"]').val(),
            csr_state: $('input[name="csr_state"]').val(),
            csr_city: $('input[name="csr_city"]').val(),
            csr_organization: $('input[name="csr_organization"]').val(),
            csr_unit: $('input[name="csr_unit"]').val(),
            csr_domain: $('input[name="csr_domain"]').val(),
            csr_email: $('input[name="csr_email"]').val(),
            csr_key: $('select[name="csr_key"]').val()
        };
        ajx.post('Api_generate_csr', data, function (data) {
            $('.newCsr').show();
            $('textarea[name="new_csr"]').val(data.csr);
            $('textarea[name="new_private_key"]').val(data.private_key);
        });
    });

    $('.newSsl-button').click(function (e) {
        verifySslOrderData();
    });



    function checkCsr() {
        var postData = {
            'csr': $('.csr-field').val()
        };

        ajx.post('Api_check_csr', postData, function (data) {

            if (typeof data.domain != 'undefined')
            {
                $('.csr-domain-name').text('@' + data.domain);
            } else
            {
                $('.csr-domain-name').text('-- nieprawidłowy CSR--');
            }
        });
    }

    $('.csr-field').focusout(checkCsr).click(checkCsr);

</script>


