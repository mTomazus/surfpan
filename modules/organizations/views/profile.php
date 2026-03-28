<h2><?= t('org_profile_title') ?></h2>
<p><?= t('org_profile_desc') ?></p>
<div id="response"></div>
<?php
validation_errors(); // show validation errors
$form_attr = [
    'mx-post' => 'organizations/submit_update/' . ($org->id ?? ''),
    'mx-redirect-on-success' => 'true',
    'mx-target' => '#response',
    'class' => 'p-1',
    'style' => 'max-width: 868px; margin:auto;'
];
echo form_open('#', $form_attr);
echo '<div class="field">';
echo form_label(t('org_profile_name'), ['class' => 'lbl']);
echo form_input('organization', $org->organization, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label(t('org_profile_timezone'), ['class' => 'lbl']);
$zone_options = Modules::run("organizations/_tz_options_html", $org->timezone);
?>
<select id="timezone" name="timezone" required class="txt" style="appearance:none;font-size: 1em;">
    <?= $zone_options ?>
</select>
<?php
echo '</div><div class="field">';
echo form_label(t('org_profile_vat'), ['class' => 'lbl']);
echo form_input('company_code', $org->company_code, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label(t('org_profile_email'), ['class' => 'lbl']);
echo form_email('email', $org->email, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label(t('org_profile_phone'), ['class' => 'lbl']);
echo form_input('phone', $org->phone,['maxlength'=>'50', 'type'=>'tel', 'class'=>'txt']);
echo '</div><div class="field">';
echo form_label(t('org_profile_address'), ['class' => 'lbl']);
echo form_input('address', $org->address, ['maxlength'=>'255', 'class'=>'txt']);
echo '</div><div class="field">';
echo form_label(t('org_profile_status'), ['class' => 'lbl']);
$status_attr = [
    'class' => 'txt',
    'id'    => 'status',
    'style' => 'appearance:none;font-size: 1em;',
];
// build status options array
$status_options = [
    'active'   => t('org_status_active'),
    'inactive' => t('org_status_inactive'),
    'private'  => t('org_status_private'),
];
echo form_dropdown('status', $status_options, $org->status, $status_attr);
echo '</div><div class="field">';
echo form_label(t('org_profile_country'), ['class' => 'lbl']);
$country_attr = [
    'class' => 'txt',
    'id'    => 'country',
    'style' => 'appearance:none;font-size: 1em;',
];
// build country options array
$country_options = [t('org_profile_country_select')];
if (!empty($countries) && is_array($countries)) {
    foreach ($countries as $country) {
        $country_options[$country->code] = $country->name;
    }
}
echo form_dropdown('country', $country_options, $org->country, $country_attr);
echo '</div><div class="btn-group">';
echo form_submit('submit', t('org_update_btn'), ['class' => 'btn primary']);
$btn_attr = [
    'mx-get' => 'organizations/change_pass',
    'mx-build-modal' => 'pass-change-modal',
    'class' => 'btn'
];
echo form_button('view_btn', t('org_change_pass_btn'), $btn_attr);
if (from_trongate_mx()) {
    echo '<button type="button" class="btn" onclick="closeModal()">' . t('btn_close') . '</button>';
}
echo '</div>';
echo form_close();
?>
