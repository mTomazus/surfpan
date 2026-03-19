<!-- Payment Modal -->
<div class="pm-header">
    <p class="pm-overline">Entry Fee Payment</p>
    <p style="margin: 0;font-size: 1.3rem;">
        <?= out($user_comp->comp_name) ?>
    </p>
    <p class="pm-subtitle">
        <?= out($user_comp->comp_location) ?> • <?= out($user_comp->comp_year) ?>
    </p>
</div>

<section class="pm-body">
    <div class="pm-summary">
        <div class="pm-row">
            <span>Competitor:</span>
            <strong>
                <?= out($user_comp->user_name) ?>
            </strong>
        </div>
        <div class="pm-row">
            <span>Amount:</span>
            <strong>
                <?= number_format($user_comp->entry_fee ?? 0, 2) ?> <i class="fa fa-euro" style="font-weight: 900;" aria-hidden="true"></i>
            </strong>
        </div>
        <div class="pm-row">
            <span>Status:</span>
            <?php
                $status = strtolower($user_comp->status ?? 'not_required');
                $status_class = 'pm-chip--notrequired';
                $status_label = 'Not Set';

                if ($status === 'paid') {
                    $status_class = 'pm-chip--paid';
                    $status_label = 'Paid';
                } elseif ($status === 'pending') {
                    $status_class = 'pm-chip--pending';
                    $status_label = 'Payment pending';
                }
            ?>
            <span id="pmStatusChip" class="pm-chip <?= $status_class ?>">
                <?= $status_label ?>
            </span>
        </div>
    </div>
    <div class="pm-summary merchant-info">
        <div class="pm-row">
            <span>Merchant:</span>
            <strong>
                VšĮ Banglentė
            </strong>
        </div>
        <div class="pm-row">
            <span>Address</span>
            <strong>
                Debreceno g. 31-49
            </strong>
        </div>
        <div class="pm-row">
            <span>City, Country:</span>
            <strong style="text-align: right;">
                Klaipeda, Lithuania
            </strong>
        </div>

    </div>

    <p class="pm-note">
        After payment you will be fully registered for this competition. You will receive a confirmation email with the receipt.
    </p>
</section>

<label class="mb-1 ml-2 agree">
    <div class="checkbox-wrapper-30">
        <span class="checkbox">
        <input type="checkbox" name="agree" value="1" required/>
        <svg>
            <use xlink:href="#checkbox-30" class="checkbox"></use>
        </svg>
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
        <symbol id="checkbox-30" viewBox="0 0 22 22">
            <path fill="none" stroke="currentColor" d="M5.5,11.3L9,14.8L20.2,3.3l0,0c-0.5-1-1.5-1.8-2.7-1.8h-13c-1.7,0-3,1.3-3,3v13c0,1.7,1.3,3,3,3h13 c1.7,0,3-1.3,3-3v-13c0-0.4-0.1-0.8-0.3-1.2"/>
        </symbol>
        </svg>
    </div>
    <span>I agree with <a class="link" href="<?= BASE_URL ?>welcome/terms">terms and conditions</a></span>
</label>

<div class="pm-footer">
    <button type="button" class="btn" onclick="closeModal()">Cancel</button>

    <?php if ($user_comp->status === 'paid'): ?>
        <button type="button" class="btn" aria-disabled="true">
            Already paid
        </button>
    <?php else: ?>
        <a href="<?= out($payment_link) ?>" class="btn secondary">
             Pay <?= number_format($user_comp->entry_fee ?? 0) ?> <i class="fa fa-euro" style="font-weight: 900;" aria-hidden="true"></i>
        </a>
    <?php endif; ?>
</div>


<style>
.pm-header {
    margin-bottom: 1rem;
}

.pm-overline {
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.12em;
    color: light-dark(var(--accent), var(--opposite));
    margin: 0 0 0.25rem;
}

.pm-subtitle {
    margin: 0.15rem 0 0;
    font-size: 0.9rem;
    color: #6b7280;
}

.pm-body {
    margin-top: 0.8rem;
}

.pm-summary {
    border-radius: 14px;
    border: 1px dashed light-dark(var(--accent), var(--opposite));
    background: var(--bg-light);
    padding: 0.9rem 1rem;
    margin-bottom: 0.8rem;
    font-size: 0.9rem;
}

.pm-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin: 0.2rem 0;
}

.pm-row span:first-child {
    color: var(--text-dark);
}

.pm-chip {
    display: inline-flex;
    padding: 0.1rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Chip colors */
.pm-chip--paid {
    background: #ecfdf3;
    color: #166534;
}
.pm-chip--pending {
    background: #fef9c3;
    color: #92400e;
}
.pm-chip--notrequired {
    background: #eff6ff;
    color: #1d4ed8;
}

.pm-note {
    font-size: 0.85rem;
    margin: 0;
}

.pm-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.6rem;
    margin-top: 1.25rem;
}

.pm-primary[aria-disabled="true"] {
    opacity: 0.6;
    cursor: default;
    pointer-events: none;
}

.pm-primary:hover:not([aria-disabled="true"]) {
    opacity: 0.9;
}

</style>