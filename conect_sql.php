<?php
require_once ('include/pear/MDB2.php');

$dsn = array(
            'phptype'  => 'mssql',
            'username' => 'appflexar',
            'password' => 'vamosmujer',
            'hostspec' => '10.1.1.2',
            'database' => 'flexar',
        );

$options = array(
            'debug'       => 2,
            'portability' => MDB2_PORTABILITY_ALL,
        );

/*$mdb2 = mssql_connect("10.1.1.2", "appflexar", "vamosmujer");
mssql_select_db("flexar", $mdb2)
    or die ("Can't connect to Database");

    $resultado = mssql_query('select modelo from modelos');

    echo $resultado;
*/

//uses MDB2::factory() to create the instance
 // and also attempts to connect to the host

  
$mdb2 =& MDB2::connect($dsn, $options);
if (PEAR::isError($mdb2)) {
     die($mdb2->getMessage());
}

$res =& $mdb2->query('select * from modelos');

echo $res->numCols();

/*while (($row = $res->fetchrow())) {
       // Assuming MDB2's default fetchmode is MDB2_FETCHMODE_ORDERED
     echo $row[0] . "\n";
     }

if (PEAR::isError($res)) {
        die($res->getMessage());
}

$mdb2->disconnect();
*/

?>


