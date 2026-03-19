<?php 
    echo "<section id='scores-section' mx-get='heats/heat_scores/$heat_info->id' mx-trigger='click' mx-select='#scores-section' mx-target='#scores-section' mx-throttle='500' mx-indicator='.spinner'>";
    echo "<div class='mod-head' style='display: flex; justify-content: space-around;'>
    <h2>" . out($heat_info->round) . " Heat " . out($heat_info->heat_number) . "</h2>";

    // changing time zones to UTC from org settings
    // date_default_timezone_set($timezone);

    $future_time = new DateTime($heat_info->end_time); // Convert string to DateTime
    $current_time = new DateTime(); // Get current time

    if ($future_time > $current_time) {
        $interval = $current_time->diff($future_time);
        echo '<h2>' . $interval->format('%i:%s') . '</h2></div>';  // Outputs MM:SS
    } else {
        echo "<h2 style='color:red;text-align:right;'>ENDED</h2></div>"; // Timer has expired
    }
    
    echo '<span mx-get="competitions/heat_time" mx-trigger="load"></span>';
    echo "<table id='heat-scores' style='color:var(--white);'>";

    foreach ($participants as $participant) {
        $name_parts = explode(" ", $participant['name']);
        $first_Letters = substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1);
        switch (strtolower($participant['jersey_color'])) {
            case 'white':
                $color = 'black';
                break;
            default:
                $color = 'white';
        }
        ?>
        <tr>
                <td style="border:0" class="jersey <?= out($participant['jersey_color']) ?>"><span class="d-md-none" style="color:<?= $color ?>"><?= $first_Letters ?></span></td>
                <td><?= out($participant['name']) ?></td>

            <?php if (!empty($participant['scores'])) {
                    $averages = []; // Initialize array for this participant
                    foreach ($participant['scores'] as $score) {
                        $averages[] = $score['avg_score']; // Collect scores
            ?>
                        <td class="tooltips"><?= out($score['avg_score']) ?></td>
            <?php } 
                    // Calculate sum of two highest scores
                    rsort($averages); // Sort descending
                    $top_two = array_slice($averages, 0, 2); // Get top 2
                    $total = array_sum($top_two); // Sum them
            ?>
                <td style="flex: 1;align-items: end;display: flex;">
                    <div style="font-size:1rem;font-weight: bold;padding: 0.3rem;background: var(--primary);color: var(--bg);"><?= out($total) ?></div>
                </td>

            <?php
            } else {
                echo "<td colspan='2'>No Scores Available</td></tr>";
            }
        echo "</tr>";
    }
    echo "</table>"; // Close the table properly
    echo "<div class='spinner mx-indicator mb-2'></div>";
    echo "</section>";
?>
