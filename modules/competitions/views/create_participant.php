<div id="form-table" class="container">
    <div style="width:100%">
        <h2 class="mb-0">Register Participant</h2>
        <div id="response"></div>

        <?php
            if (empty($rows)) {
                echo '<section><h4>No open competitions available yet.</h4>';
                echo '<h4>Please create a competition first or open one.</h4>';
                echo '<h3>Come back later ;)</h3></section>';
                return;
            } else {
                echo '<p style="text-align:right">Competition: <strong class="lg" style="color:lawngreen">' . $rows->name . ' ' . $rows->year . '</strong></p>';
                echo '<p style="">Fill in your details to register.</p>';
                $form_attr = [
                    'mx-post' => 'competitions/submit_create_participant',
                    'mx-target' => '#response'
                ];
                echo form_open('#', $form_attr);
                $comp_id = $rows->id;
                echo form_hidden('comp_id', $comp_id);
                echo form_label('Name');
                $attr['placeholder'] = 'Enter your name here...';
                $attr['autocomplete'] = 'off';
                echo form_input('first_name', '', $attr);
                echo form_label('Surname');
                $attr['placeholder'] = 'Enter your surname here...';
                $attr['autocomplete'] = 'off';
                echo form_input('last_name', '', $attr);
                echo form_label('Email');
                $attr['placeholder'] = 'Enter your email here...';
                $attr['autocomplete'] = 'off';
                echo form_input('email', '', $attr);
                // Add Division dropdown
                echo form_label('Division');
                $division_options = [];
                foreach ($divisions as $division) {
                    $division_options[$division->id] = $division->name;
                }
                echo form_dropdown('division_id', $division_options);
                echo form_submit('submit', 'Register');
                echo form_close();
            }
        ?>
    </div>
</div>

<style>
    label {
        clear: both;
        display: block;
        margin: 0;
        text-align: right;
    }
    form {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 1rem;
        justify-content: center;
        align-items: center;
    }
    #form-table {
        font-family: monospace;
        color: var(--secondary-color);
        margin:auto;
        background: var(--bg-secondary);
        border-radius: 15px;
        width: max(350px, 40%);
        backdrop-filter: blur(5px);
    }
    main {
        display:grid;
    }
    h2 {
        border-bottom: 2px solid;
        margin-bottom: 2rem;
        text-align: right;
        font-family: inherit;
    }
    button {
        background: #fafafa;
        color: black;
        border: 2px solid gray;
        padding: 1rem;
        border-radius: 5px;
        cursor: pointer;
        grid-column: 1 / -1;
        width: max(200px, 50%);
        margin: 1rem auto;
    }
    button:hover {
        background: #f0f0f0;
        border: 2px solid black;
        color:black;
    }
   
    @media screen and (max-width: 600px) {
        #form-table {
            width: 90%;
        }
        form {
            grid-template-columns: 1fr;
        }
    }
</style>