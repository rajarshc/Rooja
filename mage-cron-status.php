<?php

// Parse magento's local.xml to get db info, if local.xml is found

if (file_exists('app/etc/local.xml')) {

$xml = simplexml_load_file('app/etc/local.xml');

$tblprefix = $xml->global->resources->db->table_prefix;
$dbhost = $xml->global->resources->default_setup->connection->host;
$dbuser = $xml->global->resources->default_setup->connection->username;
$dbpass = $xml->global->resources->default_setup->connection->password;
$dbname = $xml->global->resources->default_setup->connection->dbname;

} 

else {
    exit('Failed to open app/etc/local.xml');
}

// DB Interaction
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);

$result = mysql_query("SELECT * FROM " . $tblprefix . "cron_schedule") or die (mysql_error());


// CSS for NexStyle
echo '
<html>
<head>
<title=Magento Cron Status>
<style type="text/css">
html {
    width: 100%;
    font-family: Helvetica, Arial, sans-serif;
}
body {
    background-color:#00AEEF;
    color:#FFFFFF;
    line-height:1.0em;
    font-size: 125%;
}
b {
    color: #FFFFFF;
}
table{
    border-spacing: 1px;
    border-collapse: collapse;
    width: 300px;
}
th {
    text-align: center;
    font-size: 125%;
    font-weight: bold;
    padding: 5px;
    border: 2px solid #FFFFFF;
    background: #00AEEF;
    color: #FFFFFF;
}
td {
    text-align: left;
    padding: 4px;
    border: 2px solid #FFFFFF;
    color: #FFFFFF;
    background: #666;
}
</style>
</head>';

// DB info for user to see
echo '
<body>
<a href="http://nexcess.net">
<img src="http://static.nexcess.net/images/logoMainR2.gif" width="217" height="38" alt="Nexcess Beyond Hosting"></a>
<br />
<br />
<br />
<b>Table Prefix:</b> ' . $tblprefix . '<br />'
. '<b>DB Host:</b> ' . $dbhost . '<br />'
. '<b>DB User:</b> ' . $dbuser . '<br />'
. '<b>DB Name</b>: ' . $dbname . '<br /><br /></p>';

// Set up the table
echo "
        <table border='1'>
        <thread>
        <tr>
        <th>schedule_id</th>
           <th>job_code</th>
           <th>status</th>
           <th>messages</th>
           <th>created_at</th>
           <th>scheduled_at</th>
           <th>executed_at</th>
           <th>finished_at</th>
           </tr>
           </thread>
           <tbody>";

// Display the data from the query
while ($row = mysql_fetch_array($result)) {
           echo "<tr>";
           echo "<td>" . $row['schedule_id'] . "</td>";
           echo "<td>" . $row['job_code'] . "</td>";
           echo "<td>" . $row['status'] . "</td>";
           echo "<td>" . $row['messages'] . "</td>";
           echo "<td>" . $row['created_at'] . "</td>";
           echo "<td>" . $row['scheduled_at'] . "</td>";
           echo "<td>" . $row['executed_at'] . "</td>";
           echo "<td>" . $row['finished_at'] . "</td>";
           echo "</tr>";
}

// Close table and last few tags
echo "</tbody></table><br /></body></html>";

mysql_close($conn);
?>