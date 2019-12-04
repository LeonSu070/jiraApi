<?php
/*
 * 用于月绩效的bug统计
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
        'fields' => ["summary", "status", "assignee", "customfield_10059", "customfield_10061", "customfield_10065","customfield_10066"],
    );
    $data = json_encode($data);
    $url = "https://chopehq.atlassian.net/rest/api/3/search";
    $result = post_json($url, $data);
    $result = json_decode($result, true);
    return $result;
}


//配置信息
define(CHOPE_BOOK_NAME, "B2B Chopebook");
define(CHOPE_CLOUD_NAME, "B2B Chope Cloud");
define(CHOPE_APP_NAME, "B2C Main APP");
define(CHOPE_WEB_NAME, "B2C Web");
define(CHOPE_DEALS_NAME, "B2C Shop APP");
define(INTERNAL_TOOLS_NAME, "Internal Tools");
define(EXTERNAL_API_NAME, "External API");

DEFINE(FE,"Fore-End");
DEFINE(SERV,"Server");
DEFINE(THIRD_PARTY,"Third Part");

$manager_do = array(
    'aaa@a.com' => array(
        CHOPE_WEB_NAME => array(FE, SERV, THIRD_PARTY),  
    ),
    'bbb@a.com' => array(
        CHOPE_BOOK_NAME => array(FE, SERV, THIRD_PARTY),  
    ),
    'ccc@a.com' => array(
        CHOPE_APP_NAME => array(FE, SERV, THIRD_PARTY),
        CHOPE_BOOK_NAME => array(FE), 
        CHOPE_DEALS_NAME => array(FE),
    ),
    
);
//配置信息结束


$jql_all = "project = OBT AND resolution in (Unresolved, Done, \"Won't Do\", \"Cannot Reproduce\") AND \"Bug Classify\" in (\"Third Part\", Fore-End, Server) AND created >= -4w ORDER BY created DESC";
$bugs_list = get_list_from_jira($jql_all);

$bug_number = array();
foreach ($bugs_list['issues'] as $bug) {
    //程序员的bug
    $bug_number[$bug['fields']['customfield_10065']['emailAddress']] += 1;
    //QA的Bug, internal tools和external api除外
    if (!in_array($bug['fields']['customfield_10059']['value'], array(INTERNAL_TOOLS_NAME, EXTERNAL_API_NAME))) {
        $bug_number[$bug['fields']['customfield_10066']['emailAddress']] += 1;
    }
}

//Manager和DO重算
foreach ($manager_do as $email => $projects) {
    $bug_number[$email] = 0;
    foreach ($projects as $pro => $classify) {
        foreach ($bugs_list['issues'] as $bug) {
            if ($pro == $bug['fields']['customfield_10059']['value'] && in_array($bug['fields']['customfield_10061']['value'], $classify)) {
                $bug_number[$email] += 1;
            } 
        }
    }
}

//输出结果
//var_dump($bug_number, $bugs_list['total']);
ksort($bug_number);
foreach ($bug_number as $email => $number) {
    echo $email . ": " . $number . "\n"; 
}

echo "Total ". $bugs_list['total'] . " bugs\n";
exit;






