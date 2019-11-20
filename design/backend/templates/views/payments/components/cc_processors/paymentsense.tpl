{$paymentsense_hash_methods = ["SHA1", "MD5", "HMACSHA1", "HMACMD5"]}

<p><b>{__("addon_name")} {__("addon_version")}</b></p>
<hr/>
<div class="control-group">
    <label class="control-label" for="merchant_id">{__("merchant_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="password" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}" size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="preshared_key">{__("preshared_key")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][preshared_key]" id="preshared_key" value="{$processor_params.preshared_key}" size="100">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="hash_method">{__("hash_method")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][hash_method]" id="hash_method">
            {foreach from=$paymentsense_hash_methods item="hash_method"}
                <option value="{$hash_method}" {if $processor_params.hash_method == $hash_method}selected="selected"{/if}>{$hash_method}</option>
            {/foreach}
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="transaction_type">{__("transaction_type")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][transaction_type]" id="transaction_type">
            <option value="SALE" {if $processor_params.transaction_type == "SALE"}selected="selected"{/if}>{__("sale")}</option>
            <option value="PREAUTH" {if $processor_params.transaction_type == "PREAUTH"}selected="selected"{/if}>{__("preauth")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="currency">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency">
            <option value="GBP"{if $processor_params.currency == "GBP"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
            <option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="AUD"{if $processor_params.currency == "AUD"} selected="selected"{/if}>{__("currency_code_aud")}</option>
            <option value="CAD"{if $processor_params.currency == "CAD"} selected="selected"{/if}>{__("currency_code_cad")}</option>
        </select>
    </div>
</div>
<hr/>
<div class="control-group">
    <label class="control-label" for="cv2_mandatory">{__("cvv2")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][cv2_mandatory]" id="cv2_mandatory">
            <option value="true" {if $processor_params.cv2_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.cv2_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="country_mandatory">{__("country")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][country_mandatory]" id="country_mandatory">
            <option value="true" {if $processor_params.country_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.country_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="state_mandatory">{__("state")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][state_mandatory]" id="state_mandatory">
            <option value="true" {if $processor_params.state_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.state_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="city_mandatory">{__("city")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][city_mandatory]" id="city_mandatory">
            <option value="true" {if $processor_params.city_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.city_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="address_mandatory">{__("address")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][address_mandatory]" id="address_mandatory">
            <option value="true" {if $processor_params.address_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.address_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="postcode_mandatory">{__("zip_postal_code")}&nbsp;{__("mandatory")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][postcode_mandatory]" id="postcode_mandatory">
            <option value="true" {if $processor_params.postcode_mandatory == "true"}selected="selected"{/if}>{__("yes")}</option>
            <option value="false" {if $processor_params.postcode_mandatory == "false"}selected="selected"{/if}>{__("no")}</option>
        </select>
    </div>
</div>
