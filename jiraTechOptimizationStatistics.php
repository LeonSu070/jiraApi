<?php
/*
 * 用于月绩效的Tech Optimization Story Points统计
 */


/*
 * Http post request
 */
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


/*
 *调用jira接口获取列表
 *Document: https://developer.atlassian.com/cloud/jira/platform/rest/v3/?utm_source=%2Fcloud%2Fjira%2Fplatform%2Frest%2F&utm_medium=302#api-rest-api-3-search-post
*/
function get_list_from_jira($jql=""){
    $data = array(
        'jql' => $jql,
        'startAt' => 0,
        'maxResults' => 1000,
        'fieldsByKeys' => false,
        'fields' => ["summary", "status", "assignee", "customfield_10030","project"],
    );
    $data = json_encode($data);
    $url = "https://chopehq.atlassian.net/rest/api/3/search";
    $result = post_json($url, $data);
    $result = json_decode($result, true);
    return $result;
}


//配置信息
define(CHOPE_BOOK_NAME, "CB");
define(CHOPE_CLOUD_NAME, "MR");
define(CHOPE_APP_NAME, "APP");
define(CHOPE_WEB_NAME, "WID");
define(CHOPE_DEALS_NAME, "COMM");
define(INTERNAL_TOOLS_NAME, "ADMIN");
define(EXTERNAL_API_NAME, "CPTN");

$do = array(
    CHOPE_WEB_NAME => 'aaa@a.com', 
    CHOPE_BOOK_NAME => 'aaa@a.com', 
    CHOPE_APP_NAME => 'aaa@a.com', 
    CHOPE_DEALS_NAME => 'aaa@a.com', 
    CHOPE_CLOUD_NAME => 'aaa@a.com', 
    INTERNAL_TOOLS_NAME => 'aaa@a.com', 
    EXTERNAL_API_NAME => 'aaa@a.com', 
);
$manager = array(
    'aaa@ccc.com' => array(
        "bbb@xxx.com",
    ),
);
//配置信息结束

function find_manager($email, $manager){
    foreach ($manager as $me => $members) {
        if(in_array($email, $members)){
            return $me;
        }
    }
    return false;
}

$jql_all = "(summary ~ \"tech optimization\" OR summary ~ \"tech optimisation\") AND status = Done AND updated >= -4w ORDER BY updated DESC";
$issue_list = get_list_from_jira($jql_all);


$p_list = array_keys($do);

$tech_opt_points = array();
foreach ($issue_list['issues'] as $issue) {
    //除DO和Manager的Tech optimization points
    $email = $issue['fields']['assignee']['emailAddress'];
    if(empty($email)){
        continue;
    }
    $pkey = $issue['fields']['project']['key'];
    $do_email = $do[$pkey];
    $manager_email = find_manager($email, $manager);
    if( in_array($pkey, $p_list) && $email != $do_email) {
        //给DO加分
        $tech_opt_points[$do_email] += $issue['fields']['customfield_10030'];
    } 
    //给经理加分
    if( $manager_email && $do_email != $manager_email ){
        $tech_opt_points[$manager_email] += $issue['fields']['customfield_10030'];
    } 
    //给开发者加分
    $tech_opt_points[$email] += $issue['fields']['customfield_10030'];
}



//输出结果
ksort($tech_opt_points);
foreach ($tech_opt_points as $email => $points) {
    echo $email . ": " . $points . "\n"; 
}

echo "Total ". $issue_list['total'] . " issues\n";
exit;






