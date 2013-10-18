<?php
/* Banner Load Image and Check View */

// Global Data
$data = array();
define('DS', DIRECTORY_SEPARATOR);

// Database Connect
function getConnection()
{
	global $data;
	if (isset($data['_connection'])) return $data['_connection'];
	$conConfig = simplexml_load_file('..'.DS.'..'.DS.'..'.DS.'app'.DS.'etc'.DS.'local.xml')->global->resources;
	$tablePrefix = $conConfig->db->table_prefix;
	$connection = $conConfig->default_setup->connection;
	
	$data['_table_prefix'] = $tablePrefix;
	try {
		$data['_connection'] = mysql_connect($connection->host,$connection->username,$connection->password);
		if ($data['_connection']){
			mysql_set_charset('utf8',$data['_connection']);
			mysql_select_db($connection->dbname,$data['_connection']);
		}
	} catch (Exception $e){}
	
	return $data['_connection'];
}

function closeConnection()
{
	global $data;
	if (isset($data['_connection'])){
		try {
			mysql_close($data['_connection']);
		} catch (Exception $e){}
	}
}

function getTable($tableName)
{
	global $data;
	if (!isset($data['_connection'])) getConnection();
	return $data['_table_prefix'].$tableName;
}

// Request control
function getRequestParam($paramName)
{
    $paramName = trim($paramName);
	$value = isset($_REQUEST[$paramName]) ? $_REQUEST[$paramName] : null;
    if ($paramName == 'type') {
        return $value;
    }
    return intval($value);
}

// get affiiate action config
function getAffiliateplusConfig($path, $store = null)
{
    global $data;
    if (!isset($data['_action_config'])) {
        $actionConfig = array(
            'general/expired_time' => 360,
            'action/detect_iframe' => 1,
            'action/resetclickby'  => 0,
            'action/detect_cookie' => 1,
            'action/detect_proxy'  => 1,
            'action/detect_software'   => 1,
            'action/detect_proxy_header'   => '1,2,3,4,5,6,7,8',
            'action/detect_proxy_hostbyaddr'   => 0,
            'action/detect_proxy_bankip'   => '65.49.0.*;64.55.*.*;64.55.*.*;69.22.*.*;69.167.*.*;74.115.*.*;128.241.*.*;140.174.*.*;204.2.*.*;206.14.*.*;209.107.*.*'
        );
        $link = getConnection();
        $configTbl = getTable('core_config_data');
        $storeTbl  = getTable('core_store');
        $sql  = "SELECT `path`, `value` FROM `$configTbl` WHERE `path` LIKE 'affiliateplus/%' ";
        $sql .= "AND (`scope`= 'default' ";
        if ($store) {
            $sql .= "OR (`scope` = 'websites' AND `scope_id` = (SELECT `website_id` FROM `$storeTbl` WHERE `store_id` = $store)) ";
            $sql .= "OR (`scope` = 'stores' AND `scope_id` = $store) ";
        }
        $sql .= ") ORDER BY FIELD(`scope`, 'default', 'websites', 'stores')";
        $result = mysql_query($sql, $link);
        if ($result) {
            while ($cfgRow = mysql_fetch_assoc($result)) {
                $actionConfig[str_replace('affiliateplus/', '', $cfgRow['path'])] = $cfgRow['value'];
            }
        }
        $data['_action_config'] = $actionConfig;
    }
    return isset($data['_action_config'][$path]) ? $data['_action_config'][$path] : null;
}

// get banner data
function getBanner($bannerId, $store)
{
    $link = getConnection();
    $table = getTable('affiliateplus_banner');
    $valueTbl = getTable('affiliateplus_banner_value');
    $sql  = "SELECT m.`banner_id`,(IF(v.value IS NULL, m.title, v.value)) as `title`,m.`source_file` FROM `$table` as m ";
    $sql .= " LEFT JOIN `$valueTbl` as v ON (m.banner_id = v.banner_id AND v.store_id = $store AND v.attribute_code = 'title')";
    $sql .= " WHERE m.`banner_id` = $bannerId";
    $result = mysql_query($sql, $link);
    if ($result) return mysql_fetch_assoc($result);
    return false;
}

// get existed impression
function getActionId($accountId, $bannerId, $storeId, $ipAddress)
{
    $link = getConnection();
    $table = getTable('affiliateplus_action');
    $sql = "SELECT `action_id` FROM `$table` WHERE ";
    $sql .= " account_id = $accountId AND type = 1";
    $sql .= " AND banner_id = $bannerId AND store_id = $storeId";
    $sql .= " AND ip_address = '$ipAddress' AND created_date = '".date('Y-m-d')."'";
    $result = mysql_query($sql, $link);
    if ($result) {
        $action = mysql_fetch_assoc($result);
        if (isset($action['action_id'])) {
            return $action['action_id'];
        }
    }
    return false;
}

// Controller Action
function controllerAction()
{
    global $data;
    $bannerId = getRequestParam('id');
    $storeId = getRequestParam('store_id') ? getRequestParam('store_id') : 0;
    $accountId = getRequestParam('account_id');
    
    if (!$accountId) return ;
    $banner = getBanner($bannerId, $storeId);
    if (!$banner) return ;
    
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if (!isRobots()) {
        if (!detectCookie()) {
            $link = getConnection();
            $table = getTable('affiliateplus_action');
            if ($actionId = getActionId($accountId, $bannerId, $storeId, $ipAddress)) {
                // update total view
                $sql = "UPDATE `$table` SET `totals` = `totals` + 1,";
                $sql .= "updated_time = '".date('Y-m-d H:i:s')."' WHERE `action_id` = $actionId";
                mysql_query($sql, $link);
            } else {
                // process for unique view row
                $uniqueSql = "SELECT `is_unique` FROM `$table` WHERE ";
                $uniqueSql .= "account_id = $accountId AND type = 1";
                $uniqueSql .= " AND banner_id = $bannerId AND store_id = $storeId";
                $uniqueSql .= " AND ip_address = '$ipAddress' AND is_unique = 1";
                if ($resetDay = getAffiliateplusConfig('action/resetclickby')) {
                    $uniqueSql .= " AND created_date > '".date('Y-m-d', time()-$resetDay*86400)."'";
                }
                $uniqueSql .= " LIMIT 1";
                $isUniqueView = 1;
                if ($result = mysql_query($uniqueSql, $link)) {
                    $result = mysql_fetch_assoc($result);
                    if (isset($result['is_unique'])){
                        $isUniqueView = 0;
                    }
                }
                if ($isUniqueView) {
                    // check for 1000 view (999 is ok) and call the Magento system
                    $lastSql = "SELECT `action_id` FROM `$table` WHERE ";
                    $lastSql .= "account_id = $accountId AND type = 1";
                    $lastSql .= " AND banner_id = $bannerId AND store_id = $storeId";
                    $lastSql .= " AND is_commission = 1 ORDER BY `action_id` DESC LIMIT 1";
                    
                    $totalSql = "SELECT SUM(`is_unique`) as total_view FROM `$table` WHERE ";
                    $totalSql .= "account_id = $accountId AND type = 1";
                    $totalSql .= " AND banner_id = $bannerId AND store_id = $storeId";
                    $totalSql .= " AND (action_id > ($lastSql) OR ($lastSql) IS NULL)";
                    
                    if ($result = mysql_query($totalSql, $link)) {
                        $result = mysql_fetch_assoc($result);
                        if (isset($result['total_view']) && ($result['total_view'] == 999 || $result['total_view'] == 1000)) {
                            $url = 'http';
                            if ($_SERVER["HTTPS"] == "on") {
                                $url .= 's';
                            }
                            $url .= '://' . $_SERVER["SERVER_NAME"];
                            if ($_SERVER["SERVER_PORT"] != "80") {
                                $url .= ':' . $_SERVER["SERVER_PORT"];
                            }
                            $url .= $_SERVER["REQUEST_URI"];
                            $url = str_replace('js/magestore/affiliateplus/banner.php',
                                'index.php/affiliates/banner/image/',
                                $url
                            );
                            header("Location: $url");
                            exit();
                        }
                    }
                }
                // create new view row
                $accountTbl = getTable('affiliateplus_account');
                $sql  = "INSERT INTO `$table` SET ";
                $sql .= "account_id = $accountId,";
                $sql .= "account_email = (SELECT email FROM `$accountTbl` WHERE account_id = $accountId),";
                $sql .= "banner_id = $bannerId,";
                $sql .= "banner_title = '".mysql_real_escape_string($banner['title'], $link)."',";
                $sql .= "type = 1,totals = 1,";
                $sql .= "ip_address = '$ipAddress',";
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $sql .= "domain = '{$_SERVER['HTTP_REFERER']}',";
                    $sql .= "referer = '".refineDomain($_SERVER['HTTP_REFERER'])."'";
                }
                $sql .= "created_date = '".date('Y-m-d')."',";
                $sql .= "updated_time = '".date('Y-m-d H:i:s')."',";
                $sql .= "store_id = $storeId,";
                $sql .= "is_unique = $isUniqueView";
                
                mysql_query($sql, $link);
            }
        }
    }
    if (getRequestParam('type') == 'javascript') {
        return ;
    }
    $sourceFile = '..'.DS.'..'.DS.'..'.DS.'media'.DS.'affiliateplus'.DS.'banner'.DS;
    $sourceFile .= $banner['source_file'];
    $fileExt = pathinfo($banner['source_file'], PATHINFO_EXTENSION);
    $mimeTypes = array(
        'swf'   => 'application/x-shockwave-flash',
        'swf'   => 'application/x-shockwave-flash',
        'jpg'   => 'image/jpeg',
        'JPG'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'JPEG'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'GIF'   => 'image/gif',
        'png'   => 'image/png',
        'PNG'   => 'image/png',
    );
    if (isset($mimeTypes[$fileExt])) {
        header("Content-Type: ".$mimeTypes[$fileExt],true);
        header("Content-Length: ".filesize($sourceFile),true);
        header("Accept-Ranges: bytes",true);
        header("Connection: keep-alive",true);
        echo file_get_contents($sourceFile);
    } else {
        header('Content-Type: text/javascript');
    }
}

// refine domain
function refineDomain($domain) {
    $parseUrl = parse_url(trim($domain));
    $domain = trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2)));
    return $domain;
}

// check is robots request
function isRobots()
{
    $storeId = getRequestParam('store_id') ? getRequestParam('store_id') : 0;
    if (!getAffiliateplusConfig('action/detect_software', $storeId)) {
        return false;
    }
    if (!$_SERVER['HTTP_USER_AGENT']) {
        return true;
    }
    if ($_SERVER['HTTP_X_REQUESTED_WITH']) {
        return true;
    }
    define("UNKNOWN", 0);
    define("TRIDENT", 1);
    define("GECKO", 2);
    define("PRESTO", 3);
    define("WEBKIT", 4);
    define("VALIDATOR", 5);
    define("ROBOTS", 6);
    if (!isset($_SESSION["info"]['browser'])) {
        $_SESSION["info"]['browser']['engine'] = UNKNOWN;
        $_SESSION["info"]['browser']['version'] = UNKNOWN;
        $_SESSION["info"]['browser']['platform'] = 'Unknown';
        
        $navigator_user_agent = ' ' . strtolower($_SERVER['HTTP_USER_AGENT']);
        if (strpos($navigator_user_agent, 'linux')) :
            $_SESSION["info"]['browser']['platform'] = 'Linux';
        elseif (strpos($navigator_user_agent, 'mac')) :
            $_SESSION["info"]['browser']['platform'] = 'Mac';
        elseif (strpos($navigator_user_agent, 'win')) :
            $_SESSION["info"]['browser']['platform'] = 'Windows';
        endif;

        if (strpos($navigator_user_agent, "trident")) {
            $_SESSION["info"]['browser']['engine'] = TRIDENT;
            $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "trident/") + 8, 3));
        } elseif (strpos($navigator_user_agent, "webkit")) {
            $_SESSION["info"]['browser']['engine'] = WEBKIT;
            $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "webkit/") + 7, 8));
        } elseif (strpos($navigator_user_agent, "presto")) {
            $_SESSION["info"]['browser']['engine'] = PRESTO;
            $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "presto/") + 6, 7));
        } elseif (strpos($navigator_user_agent, "gecko")) {
            $_SESSION["info"]['browser']['engine'] = GECKO;
            $_SESSION["info"]['browser']['version'] = floatval(substr($navigator_user_agent, strpos($navigator_user_agent, "gecko/") + 6, 9));
        } elseif (strpos($navigator_user_agent, "robot"))
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        elseif (strpos($navigator_user_agent, "spider"))
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        elseif (strpos($navigator_user_agent, "bot"))
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        elseif (strpos($navigator_user_agent, "crawl"))
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        elseif (strpos($navigator_user_agent, "search"))
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        elseif (strpos($navigator_user_agent, "w3c_validator"))
            $_SESSION["info"]['browser']['engine'] = VALIDATOR;
        elseif (strpos($navigator_user_agent, "jigsaw"))
            $_SESSION["info"]['browser']['engine'] = VALIDATOR;
        else
            $_SESSION["info"]['browser']['engine'] = ROBOTS;
        
        if ($_SESSION["info"]['browser']['engine'] == ROBOTS) {
            return true;
        }
    }
    return false;
}

// detect cookie
function detectCookie()
{
    $storeId = getRequestParam('store_id') ? getRequestParam('store_id') : 0;
    if (getAffiliateplusConfig('action/detect_cookie', $storeId)) {
        if (isset($_COOKIE['cpm']) && $_COOKIE['cpm']) {
            return true;
        }
        $expiredTime = getAffiliateplusConfig('general/expired_time', $storeId);
        $expiredTime = $expiredTime ? (time() + intval($expiredTime) * 86400) : 0;
        setcookie('cpm', 1, $expiredTime);
    }
    return false;
}

// Main Execute
if (getConnection())
{
    controllerAction();
    closeConnection();
}
