<?php
include("libs/FeedTypes.php");
include("libs/simple_html_dom.php");

//Creating an instance of FeedWriter class. 
$TestFeed = new RSS2FeedWriter();
$TestFeed->setTitle('Dengeki Daisy Cat');
$TestFeed->setLink('https://dengekidaisycat.wordpress.com/');

$tidy_config = dirname(__FILE__) . "/tidy.conf";

$html_text = file_get_contents("https://dengekidaisycat.wordpress.com/") or exit(1);
$tidy = tidy_parse_string($html_text, $tidy_config, 'UTF8');
tidy_clean_repair($tidy);
$html = str_get_html(tidy_get_output($tidy));

$go_on = TRUE;
$feed_count = 0;

while ($go_on){
	//parse through the HTML and build up the RSS feed as we go along
	foreach($html->find('article') as $article) {
		//Create an empty FeedItem
		$newItem = $TestFeed->createNewItem();

		//Look up and add elements to the feed item   
		$title = $article->find('h1.entry-title a', 0);
		if ($title!=NULL){
			$newItem->setTitle($title->innertext);
		}
		else{
			$newItem->setTitle('(Sense títol)');
		}

		$description = str_replace("text-align:center;","",$article->find('div.entry-content', 0)->innertext);
		$description = preg_replace("/\<img (.*)submanga(.*)w=190\" \/\>/i", '', $description);
		$description = preg_replace("/\<img (.*)mediafire(.*)w=190\" \/\>/i", '', $description);
		$description = preg_replace("/\<img (.*)submanga(.*)w=560\" \/\>/i", '', $description);
		$description = preg_replace("/\<img (.*)mediafire(.*)w=560\" \/\>/i", '', $description);

		$newItem->setDescription($description);

		if ($title!=NULL){
			$newItem->setLink($title->href);
		}
		else{
			$newItem->setLink('https://dengekidaisycat.wordpress.com/');
		}

		//The format is: 2013-09-02T14:43:43+00:00
		$datetext = $article->find('time', 0)->datetime;
	
		$date = date_create_from_format('Y-m-d\TH:i:sP', $datetext);

		$newItem->setDate($date->format('Y-m-d H:i:s'));

		//Now add the feed item
		$TestFeed->addItem($newItem);
		$feed_count++;
	}

	$texts = $html->find('text');
	$go_on = FALSE;
	foreach ($texts as $text){
		if ($text->plaintext==' Entradas anteriores'){
			//Not sleeping, Wordpress.com does not appear to be rate-limited
			$html_text = file_get_contents($text->parent->href) or exit(1);
			$tidy = tidy_parse_string($html_text, $tidy_config, 'UTF8');
			tidy_clean_repair($tidy);
			$html = str_get_html(tidy_get_output($tidy));
			$go_on = TRUE;
			break;
		}
	}
	
}

if ($feed_count==0){
	//No error but no feeds, this is wrong
	exit(1);
}

$TestFeed->generateFeed();
?>
