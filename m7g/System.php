<?php
/* Description: System class for M7G, Author: Ramesh Singh, Copyright © 2024 PASA */
class System {
    var $settings;
    function getSettings() {
        $settings['dbhost'] = "localhost"; //"host.docker.internal";//"mnao-tool-db-dev.mysql.database.azure.com";
        $settings['dbuser'] = "root";//"mnaotooladmindev";//"editor";
        $settings['dbpass'] = "";//"yeYx)i&2b)KB";//"Q&ZVaY#_y7Mv";
        $settings['dbname'] = "dbm7g";//"mnao-tool";
        $settings['pasa_email'] = "rameshk.singh@ext.panasonic.com";
        $settings['mnao_email'] = "rameshk.singh@mazda.com";
        return $settings;
    }
    
}
?>