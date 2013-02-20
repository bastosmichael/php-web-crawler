<?php

// Inculde the phpcrawl-mainclass 
include_once("./src/PHPCrawler.php");

// Extend the class and override the handleDocumentInfo()-method  
class hulu extends PHPCrawler { 
        
        function handleDocumentInfo($DocInfo) { 
                if (PHP_SAPI == "cli") $lb = "\n"; 
                else $lb = "<br />"; 
                echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")".$lb; 
                //echo "HTTP-statuscode: ".$PageInfo->http_status_code.$lb;
                echo "Referer-page: ".$DocInfo->referer_url.$lb; 
                if ($DocInfo->received == true){ 
                        echo "Content received: ".$DocInfo->bytes_received." bytes".$lb; 
                        if (strpos($DocInfo->url,'watch') !== false) {
                                $DocInfo->content = preg_replace('/(.*)\n(.*)/', '$1$2', $DocInfo->content);
                                $this->parsePage($DocInfo->content, $DocInfo->url);
                                sleep(5);
                        }
                }else{  
                        echo "Content not received".$lb;
                }
                echo $lb; 
                flush(); 
        }  
  
        function parsePage($page, $page_url){
                $group = array();
                $count = 0;
                $show = "";
                $lineArray = split('~', $page);
                for($i = 0; $i < count($lineArray); $i++) {
                        if (strpos($lineArray[$i],'var video') !== false) {
                                $data = array();
                                $category = array();
                                $custom = array();
                                $count++;
                
                                $data['title'] = preg_replace('/.*"title": (.+?), "video_type".*/', '$1', $lineArray[$i]);
                                $data['title'] = preg_replace('/"(.+?)"/', '$1', $data['title']);
                                $data['title'] = preg_replace('/.*:(.+?)/', '$1', $data['title']);
                                
                                $data['description'] = preg_replace('/.*"description": (.+?), "copyright".*/', '$1', $lineArray[$i]);
                                $data['description'] = preg_replace('/"(.+?)"/', '$1', $data['description']);
                                $data['description'] = preg_replace('/(.+?)\/(.+?)/', '$1 $2', $data['description']);

                                $category['category'] = preg_replace('/.*"categories": (.+?), "original.*/', '$1', $lineArray[$i]);
                                $category['category'] = preg_replace('/.*"genre": (.+?), "genres".*/', '$1', $lineArray[$i]);
                                $category['category'] = preg_replace('/"(.+?)"/', '$1', $category['category']);

                                $custom['show'] = preg_replace('/.*"show": {"id": .+, "name": "(.+?)", "canonical_name".*/', '$1', $lineArray[$i]);
                                $custom['show_name'] = preg_replace('/.*"show": {"id": .+, "name": ".+", "canonical_name": "(.+?)", "rating".*/', '$1', $lineArray[$i]);
                                $custom['show_id'] = preg_replace('/.*"show": {"id": (.+?), "name".*/', '$1', $lineArray[$i]);

                                $custom['season'] = preg_replace('/.*"season_number": (.+?), "episode_number".*/', '$1', $lineArray[$i]);
                                $custom['season'] = preg_replace('/"(.+?)"/', '$1', $custom['season']);

                                $custom['episode'] = preg_replace('/.*"episode_number": (.+?), "programming_type".*/', '$1', $lineArray[$i]);
                                $custom['episode'] = preg_replace('/"(.+?)"/', '$1', $custom['episode']);
                                $custom['episode_id'] = preg_replace('/.*"id": (.+?), "eid".*/', '$1', $lineArray[$i]);

                                $custom['rating'] = preg_replace('/.*"content_rating": (.+?), "studio".*/', '$1', $lineArray[$i]);
                                $custom['rating'] = preg_replace('/"(.+?)"/', '$1', $custom['rating']);
                                
                                $custom['hulu_url'] = preg_replace('/.*"id": (.+?), "eid".*/', 'http://www.hulu.com/watch/$1', $lineArray[$i]);
                                $custom['poster_url'] = preg_replace('/.*"poster_url": (.+?), "rating".*/', '$1', $lineArray[$i]);
                                $custom['poster_url'] = preg_replace('/"(.+?)"/', '$1', $custom['poster_url']);
                                $custom['embeded_video'] = preg_replace('/.*"eid": "(.+?)", "title".*/', 'http://www.hulu.com/embed.html?eid=$1', $lineArray[$i]);                          
                                
                                $custom['air_date'] = preg_replace('/.*"original_premiere_date": (.+?), "is_subscriber_only".*/', '$1', $lineArray[$i]);
                                $custom['air_date'] = preg_replace('/"(.+?)T.*Z"/', '$1', $custom['air_date']);
                                $custom['expires'] = preg_replace('/.*"expires_at": (.+?), "tune_in_information".*/', '$1', $lineArray[$i]);
                                $custom['expires'] = preg_replace('/"(.+?)T.*Z"/', '$1', $custom['expires']);
                                $custom['tune_in'] = preg_replace('/.*"tune_in_information": (.+?), "embed_permitted".*/', '$1', $lineArray[$i]);
                                $custom['tune_in'] = preg_replace('/"(.+?)"/', '$1', $custom['tune_in']);
                                $custom['tune_in'] = preg_replace('/(.+?)\\\n(.+?)/', '$1 $2', $custom['tune_in']);
                                $custom['duration'] = preg_replace('/.*"duration": (.+?), "has_captions".*/', '$1', $lineArray[$i]);
                                
                                $custom['video_type'] = preg_replace('/.*"video_type": (.+?), "content_id".*/', '$1', $lineArray[$i]);
                                $custom['video_type'] = preg_replace('/"(.+?)"/', '$1', $custom['video_type']);
                                $custom['program_type'] = preg_replace('/.*"programming_type": (.+?), "poster_url".*/', '$1', $lineArray[$i]);
                                $custom['program_type'] = preg_replace('/"(.+?)"/', '$1', $custom['program_type']);
                                
                                $custom['embed_permitted'] = preg_replace('/.*"embed_permitted": (.+?), "show_id".*/', '$1', $lineArray[$i]);
                                $custom['hulu_plus'] = preg_replace('/.*"is_subscriber_only": (.+?), "is_web_only".*/', '$1', $lineArray[$i]);
                                $custom['web_only'] = preg_replace('/.*"is_web_only": (.+?), "is_auth_valid".*/', '$1', $lineArray[$i]);
                                $custom['has_captions'] = preg_replace('/.*"has_captions": (.+?), "released_at".*/', '$1', $lineArray[$i]);
                                $custom['is_movie'] = preg_replace('/.*"is_movie": (.+?), "is_subscriber_only".*/', '$1', $lineArray[$i]);
                                
                                $custom['studio'] = preg_replace('/.*"company": {"id": .+, "name": "(.+?)", "canonical_name": ".+", "key_art_url".*/', '$1', $lineArray[$i]);
                                $custom['studio_name'] = preg_replace('/.*"company": {"id": .+, "name": ".+", "canonical_name": "(.+?)", "key_art_url".*/', '$1', $lineArray[$i]);
                                $custom['studio_id'] = preg_replace('/.*"company": {"id": (.+?), "name".*/', '$1', $lineArray[$i]);
                                $custom['copyright'] = preg_replace('/.*"copyright": (.+?), "season_number".*/', '$1', $lineArray[$i]);
                                $custom['copyright'] = preg_replace('/"(.+?)"/', '$1', $custom['copyright']);

                                $custom['show_description'] = preg_replace('/.*"description": (.+?)\},.*/', '$1', $lineArray[$i]);
                                $custom['show_description'] = preg_replace('/"(.+?)"/', '$1', $custom['show_description']);
                                $custom['show_description'] = preg_replace('/(.+?)\/(.+?)/', '$1 $2', $custom['show_description']);

                                $data['categories'] = $category;

                                $this->insert($data, $custom);
                                $group[$count] = $data;
                        }
                }
                print_r($group);       
        }

        function insert($data, $custom){
                $string = implode("~", $data);
                $string = preg_replace('/(.*)\n(.*)/', '$1$2', $string);
                echo $string . "\n\n";
                file_put_contents('hulu.csv', $string . "\n", FILE_APPEND);
                print_r($data);
        }  
} 


?>
