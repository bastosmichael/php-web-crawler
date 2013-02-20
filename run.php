<?php

set_time_limit(10000); 
$crawler = "";

$sites = glob("templates/*.php");

for($i = 0; $i < count($sites); $i++) {
        include_once("$sites[$i]");
        $sites[$i] = preg_replace('/templates\/(.+?).php/', '$1', $sites[$i]); 
        $crawler = new $sites[$i](); 

        // URL to crawl 
        $crawler->setURL("www.$sites[$i].com"); 
        
        $crawler->setWorkingDirectory("/dev/shm/"); 
        $crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

        // Only receive content of files with content-type "text/html" 
        $crawler->addContentTypeReceiveRule("#text/html#"); 

        // Ignore links to pictures, dont even request pictures 
        $crawler->addURLFilterRule("#\.(jpg|jpeg|gif|png|ico|js)$# i"); 

        // Store and send cookie-data like a browser does 
        $crawler->enableCookieHandling(true); 

        // Set the traffic-limit to 1 MB (in bytes, 
        // for testing we dont want to "suck" the whole site) 
        $crawler->setTrafficLimit(10000 * 10240); 
        
        $crawler->setFollowMode(1);

        // Thats enough, now here we go 
        $crawler->go();

        // At the end, after the process is finished, we print a short 
        // report (see method getProcessReport() for more information) 
        $report = $crawler->getProcessReport(); 

        if (PHP_SAPI == "cli") $lb = "\n"; 
        else $lb = "<br />"; 
     
        echo "Summary:".$lb; 
        echo "Links followed: ".$report->links_followed.$lb; 
        echo "Documents received: ".$report->files_received.$lb; 
        echo "Bytes received: ".$report->bytes_received." bytes".$lb; 
        echo "Process runtime: ".$report->process_runtime." sec".$lb;  
        //sleep(15);
} 

// It may take a whils to crawl a site ... 

#while ($sites){
 #       for($i = 0; $i < count($sites); $i++) {
  #              echo $sites[$i] . "\n";
   #             
    #    } 
#}

?>
