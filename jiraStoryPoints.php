<?php
function httpget($url){
    $username_password = "username:password";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, $username_password);
    $header = array(
        'Content-Type: application/json',
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt_array($ch, array (
            CURLOPT_URL => $url,  
            CURLOPT_TIMEOUT => 10 
    ));
    $result = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($result, true);
    return $result;
}

function get_story_points($board_id, $project_name){
    // doc: https://developer.atlassian.com/cloud/jira/software/rest/#api-rest-agile-1-0-sprint-sprintId-get
    $url = "https://chopehq.atlassian.net/rest/agile/1.0/board/{$board_id}/sprint?state=active";
    $sprint = httpget($url);
    $sprint_id = $sprint['values'][0]['id'];
    $sprint_name = $sprint['values'][0]['name'];
    // doc: https://developer.atlassian.com/cloud/jira/software/rest/#api-rest-agile-1-0-sprint-sprintId-issue-get
    $url = "https://chopehq.atlassian.net/rest/agile/1.0/board/{$board_id}/sprint/{$sprint_id}/issue?maxResults=1000";
    $story_list = httpget($url);

    $total_point = 0;
    $tech_opt_point = 0;
    foreach ($story_list['issues'] as $issue) {
        $total_point +=  $issue['fields']['customfield_10030'];
        $summary = strtolower($issue['fields']['summary']);
        if (strpos($summary, 'tech optimisation') !== false || strpos($summary, 'tech optimization') !== false) {
            $tech_opt_point += $issue['fields']['customfield_10030'];
        }
    }
    echo $project_name . " | " . $sprint_name . " | Product Points : " . ($total_point - $tech_opt_point) . " | Tech Optimisation Points : " . $tech_opt_point . " | Total Points: " . $total_point . "\n";
}

get_story_points(5, 'ChopeApp');
get_story_points(24, 'ChopeDeals');
get_story_points(4, 'ChopeBook');
get_story_points(1, 'ChopeCloud');
get_story_points(7, 'ChopeWeb');
get_story_points(8, 'InternalTools');
get_story_points(23, 'ExternalApi');
get_story_points(43, 'AT/CD');

