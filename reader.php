<?php
	
class RssReader{

	// VARIABLES
	public $article_list = array();
	public $chunks;
	public $pages_total;
	public $index;

	public function __construct($article_list, $chunks, $index){
		$this->article_list = $article_list;
		$this->chunks = $chunks;
		$this->pages_total = $pages_total;
		$this->index = $index;
	}

	/*
		Function to get the XML file from the provided link.
	*/
	public function get_news(){
		error_reporting(E_ALL);
		ini_set('display_errors', 1);


		// read data from the feed	
		$xml_file = file_get_contents('http://www.nextgreencar.com/rss/news-rss.xml');

		// convert the xml string to an object
		$xml_obj = simplexml_load_string($xml_file);

		foreach ($xml_obj->channel->item as $item) {
			// description is exploded in order to separate the image and text
			$split_descr = explode("  ", $item->description);
			// assign values to members of the object
			$this->article_list[] = array(
				'title'=>(string)$item->title,
				'descr'=>(string)$split_descr[1],
				'link' =>(string)$item->link,
				'date' =>(string)$item->pubDate,
				'img_path'=>(string)$split_descr[0],
			);	
		}
	}

	/*
		Function to paginate the results so that they are easier to read.
	*/
	public function paginate(){
		// split the array into chunks to allow for pagination
		
		$this->chunks = array_chunk($this->article_list, 10);
		$this->pages_total = count($this->chunks);
		
		// get the correct page index
		if(isset($_GET['p'])){
			// display an error if the page number is too big
			if($_GET['p'] > $this->pages_total){
				die("Page does not exist.");
			}
			$this->index = $_GET['p'];
		} else {
			// default index of 0 (when user enters the page for the first time)
			$this->index = 0;
		}
	}

	/*
		Function used to create links for pagination control.
	*/
	public function create_links(){
		// link to the current page without the page number
		// pagination links will add the index and modifier to move between pages
		$current_page = "{$_SERVER['SCRIPT_NAME']}?p=";


		// create pagination links
		echo "<div class=\"row\"><div id=\"pagination\" class=\"container-fluid pull-right\">";

		// previous pages
		if($this->index >= 1){
			// arrow = -1 page
			echo "<a href=\"".$current_page.($this->index-1)."\"><i class=\"fa fa-angle-left fa-lg\" aria-hidden=\"true\"></i></a>";
			// first page
			if($this->index > 3) echo "<a href=\"".$current_page."0"."\">1...</a>";
			// - 2 pages
			if($this->index > 1) echo "<a href=\"".$current_page.($this->index-2)."\">".($this->index - 1)."</a>";
			// - 1 page
			echo "<a href=\"".$current_page.($this->index-1)."\">".$this->index ."</a>";
		}

		// current page
	    echo "<a href=\"".$current_page.$this->index."\" class=\"active\">".($this->index+1)."</a>";

	    // next pages
		if($this->index < ($this->pages_total-1)){
			// + 1 page
			echo "<a href=\"".$current_page.($this->index+1)."\">".($this->index + 2)."</a>";
			// + 2 pages 
			if($this->index < ($this->pages_total - 2)) echo "<a href=\"".$current_page.($this->index+2)."\">".($this->index + 3)."</a>";
			// last page
			if($this->index < ($this->pages_total - 3)) echo "<a href=\"".$current_page."24"."\">...25</a>";
			// arrow = +1 page
	    	echo "<a href=\"".$current_page.($this->index+1)."\"><i class=\"fa fa-angle-right fa-lg\" aria-hidden=\"true\"></i></a>";
		}
	  	echo "</div></div>";
	}

	/*
		Function to output the news data into the page.
	*/
	public function print_data(){
		foreach ($this->chunks[$this->index] as $article) {
		// title and date
			echo "<div class=\"news_container\">
					<div class=\"title_bar\">";
			echo "<a href=".$article["link"].">".$article["title"]."</a>";
			
			echo "<p class=\"art_date\">".$article["date"]."</p>";
			echo "</div>";

			// thumbnail
			echo "<div class=\"row content_row\">";
			echo "<div class=\"col-lg-2 photo_square\">";
			echo $article["img_path"];
			echo "</div>";

			// content
			echo "<div class=\"col-lg-10 art_content\">";
			echo $article["descr"];
			echo "</div>";

			// closing tags
			echo "</div></div>";
		}
	}
}

// create the RssReader object, retrieve and display the data
	$rss_reader = new RssReader($article_list, $chunks, $index);

    $rss_reader->get_news();
    $rss_reader->paginate();
    $rss_reader->create_links();
    $rss_reader->print_data();
    $rss_reader->create_links();

?>