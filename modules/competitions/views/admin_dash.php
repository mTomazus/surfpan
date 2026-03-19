<div id="form-container"></div>
<?php if ($organization) { ?>
    <div id="load-on" mx-get="competitions/create_judge" mx-target="#form-container" mx-select="#judge-table" mx-trigger="load"></div>
<?php } else { ?>
    <div id="load-on" mx-get="competitions/score_heat" mx-target="#form-container" mx-select="form" mx-trigger="load"></div>
<?php } ?>