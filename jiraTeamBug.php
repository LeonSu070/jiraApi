<?php
function post_json($url,$data){
    $header = array(
        'Content-Type: application/json',
        'Authorization: Basic <token>'
    );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt_array($ch, array (
            CURLOPT_URL => $url,  
            CURLOPT_TIMEOUT => 10 
    ));

    $data && curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function get_bug_number($jql=""){
    $data = array(
        'jql' => $jql,
        'startAt' => 0,
        'maxResults' => 1,
        'fieldsByKeys' => false,
        'fields' => array("summary","status","assignee"),
    );
    $data = json_encode($data);
    $url = "https://chopehq.atlassian.net/rest/api/3/search";
    $result = post_json($url, $data);
    $result = json_decode($result, true);
    return $result['total'];
}

$jql_all = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w ORDER BY created DESC";
$all = get_bug_number($jql_all);

$jql_chopeweb = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w AND \"Scrum Team\" = \"B2C Web\" ORDER BY created DESC";
$chopeweb = get_bug_number($jql_chopeweb);

$jql_chopebook = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w AND \"Scrum Team\" = \"B2B Chopebook\" ORDER BY created DESC";
$chopebook = get_bug_number($jql_chopebook);

$jql_chopecloud = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w AND \"Scrum Team\" = \"B2B Chope Cloud\" ORDER BY created DESC";
$chopecloud = get_bug_number($jql_chopecloud);

$jql_chopeapp = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w AND \"Scrum Team\" = \"B2C Main APP\" ORDER BY created DESC";
$chopeapp = get_bug_number($jql_chopeapp);

$jql_chopedeals = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w AND \"Scrum Team\" = \"B2C Shop APP\" ORDER BY created DESC";
$chopedeals = get_bug_number($jql_chopedeals);


$jql_others = "project = OBT AND created >= -4w AND \"Scrum Team\" is EMPTY ORDER BY created DESC";
$others = get_bug_number($jql_others);

echo "Total bugs last 4 weeks: " . $all . " + " . $others . "(Others)\n";
echo "ChopeWeb: " . $chopeweb . "\n";
echo "ChopeBook: " . $chopebook . "\n";
echo "ChopeCloud: " . $chopecloud . "\n";
echo "ChopeApp: " . $chopeapp . "\n";
echo "ChopeDeals: " . $chopedeals . "\n";
echo "Others: " . $others . "\n";




