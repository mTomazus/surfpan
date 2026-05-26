<section class="card pad span-12 danger-border" aria-labelledby="profile-title">
    <h2>Withdraw from Competition?</h2>
    <p>
        Are you sure you want to withdraw from this competition?
        Once you withdraw, your spot is released and you cannot re-enter the same division.
        If heats have already been drawn, withdrawal is blocked — contact the organiser instead.
    </p>
    <div class="modal-actions">
        <button class="btn" onclick="closeModal()">Cancel</button>
        <button class="btn" mx-close-on-success="true" mx-on-success="#registrations" mx-delete="users/confirm_withdraw/<?= out($record_id) ?>">Yes, Withdraw</button>
    </div>
</section>

<style>
    .danger-border {
        border: 10px dashed red;
    }

    .modal h2 {
        margin: 1rem 0;
        text-align: center;
        color: #c62828;
    }

    .modal-actions {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }
</style>