<div>
  <div class="section-head" style="margin-bottom:1rem">
    <h3 style="margin:0">Liability Waiver</h3>
    <span class="subtle"><?= out($participant['comp_name']) ?> <?= out($participant['year']) ?></span>
  </div>

  <div style="max-height:260px;overflow-y:auto;border:1px solid color-mix(in oklab,var(--text),transparent 80%);border-radius:.5rem;padding:1rem;font-size:.85rem;line-height:1.6;margin-bottom:1.25rem">
    <p><strong>PARTICIPANT LIABILITY WAIVER AND RELEASE OF CLAIMS</strong></p>
    <p>In consideration of being permitted to participate in this surfing competition ("Event"), I, the undersigned participant, acknowledge and agree to the following:</p>
    <p><strong>1. Assumption of Risk.</strong> I understand that surfing and participation in competitive surfing events involves inherent risks, including but not limited to: physical injury, drowning, collision with other participants or watercraft, and adverse weather or ocean conditions. I voluntarily assume all such risks.</p>
    <p><strong>2. Release of Liability.</strong> I hereby release, waive, and discharge the event organiser, SurfPan, their officers, employees, volunteers, and agents from any and all claims, demands, or causes of action arising out of my participation in the Event, including claims arising from negligence.</p>
    <p><strong>3. Medical Authorisation.</strong> I consent to receive emergency medical treatment if required and agree to bear all costs associated with such treatment.</p>
    <p><strong>4. Media Release.</strong> I grant permission for the use of my name, image, and likeness in photographs or video recordings taken during the Event for promotional purposes.</p>
    <p><strong>5. Rules Compliance.</strong> I agree to abide by all competition rules, decisions of the judges, and instructions of event officials.</p>
    <p>By accepting this waiver I confirm that I have read, understood, and agreed to all terms above, and that I am at least 18 years of age or have obtained parental/guardian consent.</p>
  </div>

  <?php if ($participant['waiver_accepted_at']): ?>
    <p class="mx-banner success" style="margin-bottom:0">Waiver already accepted on <?= out(date('Y-m-d', strtotime($participant['waiver_accepted_at']))) ?>.</p>
  <?php else: ?>
    <div id="waiverResult"></div>
    <label style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem;font-size:.9rem;cursor:pointer">
      <input type="checkbox" id="waiverCheck" onchange="document.getElementById('waiverBtn').disabled = !this.checked;" />
      I have read and accept the terms of this waiver
    </label>
    <form mx-post="users/accept_waiver/<?= out($participant['id']) ?>"
          mx-target="#waiverResult"
          mx-close-on-success="true">
      <div style="display:flex;justify-content:flex-end;gap:.6rem">
        <button class="btn" type="button" onclick="closeModal()">Cancel</button>
        <button class="btn primary" id="waiverBtn" disabled type="submit">
          Accept &amp; Sign
        </button>
      </div>
      <?= form_close() ?>
  <?php endif; ?>
</div>
