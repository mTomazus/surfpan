<h2>Organizer <span>Profile</span></h2>
<p>Keep your profile information up to date.</p>
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
echo form_open_upload('#', $form_attr); ?>

<!-- Logo upload -->
<div class="avatar-wrap" style="margin-bottom: 1.5rem;grid-column: -1 / 1;margin-inline: auto; display: flex; align-items: center; gap: 1rem;">
  <div class="avatar-preview" id="logo-preview">
    <?php if (!empty($org->logo)): ?>
      <img id="logo-img" src="<?= BASE_URL ?>organizations_module/images/org_avatars/<?= out($org->logo) ?>" alt="Logo">
    <?php else: ?>
      <span id="logo-initials"><?= mb_strtoupper(mb_substr($org->organization ?? '?', 0, 1)) ?></span>
    <?php endif; ?>
  </div>
  <div>
    <label class="avatar-upload-btn" for="logo-file-input"><i class="fa fa-camera"></i> Change logo</label>
    <?php
      $logo_attr = [
        'id'         => 'logo-file-input',
        'accept'     => 'image/jpeg,image/png,image/gif,image/webp',
        'style'      => 'display:none',
        'mx-trigger' => 'change',
        'mx-target'  => '#logo-preview',
        'mx-post'    => 'organizations/upload_logo',
      ];
      echo form_file_select('logo', $logo_attr);
    ?>
    <div style="font-size:0.75rem;color:var(--text-light);margin-top:4px;">JPEG, PNG or WEBP · max 400×400px</div>
  </div>
</div>
<?php
echo '<div class="field">';
echo form_label('Name', ['class' => 'lbl']);
echo form_input('organization', $org->organization, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label('Time Zone', ['class' => 'lbl']);
$zone_options = Modules::run("organizations/_tz_options_html", $org->timezone);
?>
<select id="timezone" name="timezone" required class="txt" style="appearance:none;font-size: 1em;">
    <?= $zone_options ?>
</select>
<?php
echo '</div><div class="field">';
echo form_label('Vat Code', ['class' => 'lbl']);
echo form_input('company_code', $org->company_code, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label('Email', ['class' => 'lbl']);
echo form_email('email', $org->email, ['class'=>'txt','maxlength'=>'255', 'required' => 'required']);
echo '</div><div class="field">';
echo form_label('Phone', ['class' => 'lbl']);
echo form_input('phone', $org->phone,['maxlength'=>'50', 'type'=>'tel', 'class'=>'txt']);
echo '</div><div class="field">';
echo form_label('Address', ['class' => 'lbl']);
echo form_input('address', $org->address, ['maxlength'=>'255', 'class'=>'txt']);
echo '</div><div class="field">';
echo form_label('Status', ['class' => 'lbl']);
$status_attr = [
    'class' => 'txt',
    'id'    => 'status',
    'style' => 'appearance:none;font-size: 1em;',
];
// build status options array
$status_options = [
    'active'   => 'Active',
    'inactive' => 'Inactive',
    'private'  => 'Private'
];
echo form_dropdown('status', $status_options, $org->status, $status_attr);
echo '</div><div class="field">';
echo form_label('Country', ['class' => 'lbl']);
$country_attr = [
    'class' => 'txt',
    'id'    => 'country',
    'style' => 'appearance:none;font-size: 1em;',
];
// build country options array
$country_options = ['-- Select Country --'];
if (!empty($countries) && is_array($countries)) {
    foreach ($countries as $country) {
        $country_options[$country->code] = $country->name;
    }
}
echo form_dropdown('country', $country_options, $org->country, $country_attr);
echo '</div><div class="btn-group">';
echo form_submit('submit', 'Update', ['class' => 'btn primary']);
$btn_attr = [
    'mx-get' => 'organizations/change_pass',
    'mx-build-modal' => 'pass-change-modal',
    'class' => 'btn'
];
echo form_button('view_btn', 'Change Password', $btn_attr);
if (from_trongate_mx()) {
    echo '<button type="button" class="btn" onclick="closeModal()">Close</button>';
}
echo '</div>';
echo form_close();
?>
