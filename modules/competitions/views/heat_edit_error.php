<form id="form-table" style="grid-template-columns: 1fr;color:white"><h2><?= $error ?></h2>
<section>
    <h4>To edit past heat scores:</h4>
    <a class="text-center side-button" mx-get="competitions/edit_scores" mx-target="#form-container" mx-select="#form-table"><h3>edit ended heats</h3></a>
    <a class="text-center side-button" mx-get="competitions/edit_all_scores" mx-target="#form-container" mx-select="#form-table"><h3>edit all heats</h3></a>
</section>
</form>
    
