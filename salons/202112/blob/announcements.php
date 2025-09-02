<?php
// List of Announcements
$ann_postpone_exhibition = <<<HTML
    <h4 class="text-color">Update on Salon Exhibition</h4>
    <p class="text-justify">The Salon Exhibition that was scheduled to open on January 7, 2022 has been postponed due to the decision of the Government
        of Karnataka to impose weekend curfew to curtail the COVID pandemic. We will announce the revised plans once the same is finalized.
        We regret the inconvenience.
    </p>
    <!-- <p class="text-justify">Please note that the details related to the exhibition are subject to change based on any Government guidelines
        that may be issued.
    </p>
    <p class="text-justify">YPS Salon Committee welcomes everyone to attend the Salon Exhibition. Please familiarize yourselves with <thead>
        the protocols to be followed at the Exhibition Venue.
    </p> -->
HTML;

$salon_announcements = [
        array("from_date" => "2021-12-06", "to_date" => "2022-01-15", "message" => $ann_postpone_exhibition, "status" => "ACTIVE")
];

// Determine if there are announcements to be displayed
$show_announcement = false;
foreach ($salon_announcements as $announcement)
    if (date("Y-m-d") >= $announcement["from_date"] && date("Y-m-d") <= $announcement["to_date"] && $announcement['status'] == 'ACTIVE')
        $show_announcement = true;

// Show the announcement
if ($show_announcement) {
?>
    <div class="well well-sm" style="background-color: #ffffcc;">
        <h3 class="text-color">Announcements</h3>
<?php
    foreach ($salon_announcements as $announcement) {
        if (date("Y-m-d") >= $announcement["from_date"] && date("Y-m-d") <= $announcement["to_date"] && $announcement['status'] == 'ACTIVE') {
            echo $announcement['message'];
            echo "<hr>";
        }
    }
?>
    </div>
<?php

}
?>
