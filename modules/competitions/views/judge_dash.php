<div id="form-container"></div>

<?php if ($user->role === 'organizer') { ?>
    <div id="load-on" mx-get="organizations/dashboard" mx-target="#form-container" mx-trigger="load"></div>
<?php } else { ?>
    <div id="load-on" mx-get="judges/score_heat" mx-target="#form-container" mx-select="#form-container" mx-trigger="load"></div>
<?php } ?>

<script>
    function updateRangeSliderBackground() {
        var rangeInput = document.getElementById('score_range');
        if (!rangeInput) return;
        var value = rangeInput.value;
        var min = rangeInput.min ? rangeInput.min : 0;
        var max = rangeInput.max ? rangeInput.max : 10;
        var percent = ((value - min) / (max - min)) * 100;
        var color = 'linear-gradient(to right, rgb(0, 247, 148) ' + percent + '%, rgb(63, 63, 63) ' + percent + '%)';
        rangeInput.style.background = color;
    }

    function rangeSlider(value) {
        document.getElementById('score_value').innerText = value;
        updateRangeSliderBackground();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var rangeInput = document.getElementById('score_range');
        if (rangeInput) {
            updateRangeSliderBackground();
            rangeInput.addEventListener('input', function() {
                rangeSlider(this.value);
            });
            // Add touch event listeners for better mobile support
            rangeInput.addEventListener('touchstart', function() {
                updateRangeSliderBackground();
            });
            rangeInput.addEventListener('touchmove', function() {
                updateRangeSliderBackground();
            });
            rangeInput.addEventListener('touchend', function() {
                updateRangeSliderBackground();
            });
        }
    });
    (function countdownTimerWatcher() {
    function updateCountdown() {
        const timerEl = document.querySelector('[data-end-time]');
        if (timerEl) {
            const endTimeStr = timerEl.getAttribute('data-end-time');
            const endTime = new Date(endTimeStr.replace(' ', 'T') + 'Z').getTime(); // Treat as UTC
            // const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
            const now = new Date().getTime();
            let diff = Math.floor((endTime - now) / 1000);
            if (diff > 0) {
                const minutes = String(Math.floor(diff / 60)).padStart(2, '0');
                const seconds = String(diff % 60).padStart(2, '0');
                timerEl.textContent = `${minutes}:${seconds}`;
            } else {
                timerEl.textContent = "00:00";
            }
        }
    }
    setInterval(updateCountdown, 500);
    updateCountdown();
})();
</script>