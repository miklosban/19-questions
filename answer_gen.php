<?php
require('sql.conf.php');
$ID = PGPconnectSQL(USER,PASSWORD,DATABASE,HOST,PORT);

$host = 'localhost';
$port = 8083;
$path = '';
$protocol = 'http';

$obj = 3;

$cmd = "
SELECT * FROM (select questionid as q, name, o$obj as answer from input WHERE o1 IS NOT NULL AND o$obj='1' order by random() limit 10) a
UNION
SELECT * FROM (select questionid as q, name, o$obj as answer from input WHERE o1 IS NOT NULL AND o$obj IN ('0','x') order by random() limit 10) b";
list($res,$n) = PGquery($cmd);

$l = array();
while($row = pg_fetch_assoc($res)) {
    switch ($row['answer']) {
    case '0':
        $answer = 'n';
        break;
    case '1':
        $answer = 'y';
        break;
    case 'x':
        $answer = 's';
        break;
    }
    $l[] = "$answer{$row['q']}";
}
printf("%s://%s:%d/%ssave.php?obj=%d&q=%s\n",$protocol,$host,$port,$path,$obj,implode('',$l));

function PGquery($qstr) {
    global $ID;
    $res = pg_query($ID,$qstr);
    $n = pg_num_rows($res);
    return array($res,$n);
}
function PGPconnectSQL($db_user,$db_pass,$db_name,$db_host,$db_port) {
    if ($db_host=='' or $db_user=='' or $db_pass=='' or $db_name=='') return;
    $conn = pg_connect("host=$db_host port=$db_port user=$db_user password=$db_pass dbname=$db_name connect_timeout=5");
    if (!is_resource($conn)) {
        return false;
    } else {
        pg_set_client_encoding( $conn, 'UTF8' );
        return $conn;
    }
}


?>
