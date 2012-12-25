<?php

//using the Mustache template engine to seperate logic from view
require 'mustache/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

//initialize data array to be passed to the template
$m_data = array(); 		

//defaults
$file = 'pg2600.txt';
$string_search = 'peace';

//defaults for counters
$found = 0;
$notfound = 0;

//$notfound counter values go into this array
$notfound_arr = array();

//checks to see if form was submitted 
if(isset($_REQUEST['submit_yes'])) {

	if (isset($_POST['string_search']) && 
		(strlen($_POST['string_search']) > 0) ) {		

		$string_search = $_POST['string_search'];

	}

	if (isset($_POST['file_choose']) && 
		($_POST['file_choose'] != 'choose')) {
		$file = $_POST['file_choose'];
	}

	if (isset($_POST['case_insensitive'])) {	
		$all_cases = 1;
	}

	//Uploading the txt file to the server  
	if ($_FILES['new_text_file']['name']) {
		
		//validation to see if the file is a txt file
		$pieces = explode('.', $_FILES['new_text_file']['name']);
		foreach ($pieces as $piece) {
			if ((strtolower($piece)) == 'txt') {
				$m_data['is_txt_file'] = true; 
			}
		}
		if ($m_data['is_txt_file']) {

			/*if the txt file uploads 
			then replace $file with 
			the uploaded file's name*/
		  	if(move_uploaded_file($_FILES['new_text_file']['tmp_name'],
		  		$_FILES['new_text_file']['name'])) 
		  	{
		  		$file = $_FILES['new_text_file']['name'];
		  	}

	  	}else {
	  		$m_data['is_not_txt_file'] = true; 
	  	}
	}
}

//adding data to mustache data array
$m_data['string_search'] = $string_search;
$m_data['file'] = $file;
$m_data['all_cases'] = $all_cases;

//open the file
$handle = fopen($file, 'r');

//while not end of file, do this
while(!feof($handle)) {

	//seperate the text file by lines
	$line = fgets($handle);	

	//grab all the words in the line
	$words = explode(' ', $line);

	/*if user checks case insensitive 
	then make string lowercase*/ 
	if ($all_cases) { $i = 'i'; }else { $i = ''; }

	/*if $string_search (user input) has a match 
	then add one to $found and then take $notfound and 
	put it into the $notfound_arr array.
	If they are not equal, then add one to $notfound*/	
	foreach ($words as $word) {
		
		/*The “\b” in the pattern indicates a word boundary, so only distinct
		The “i” after the pattern delimiter indicates a case-insensitive search*/	
		if ( preg_match("/\b".$string_search."\b/{$i}", $word) ) {
	    	
	    	$found++;
	    	$notfound_arr[] = $notfound;
	    	$notfound = 0;

		} else {
		    
			$notfound++;
		}		
	
	}
}

//close the file
fclose($handle);

/*put the number of times the $search_string was found into 
the mustache data array*/
$m_data['found'] = $found;

//initialize the sum of $notfound_arr values 
$sum = 0;

/*go through and add all of the values
of $notfound_arr except for the first value,
because we need to find the average 
number of words between each instance of
the user inputted search string*/
foreach ($notfound_arr as $key => $num) { 

	if ($key != 0) {

		$sum += $num;
	} 

}

/*round up and divide the 
$sum by the number of 
notfound_arr values minus 1,
since we're not using the first value.
---------
Then put the average into 
the mustache data array*/
$m_data['average'] = ceil($sum / (count($notfound_arr) -1) );

/*get all the names of the text files in the directory 
and put them into the mustache data array*/
foreach ((glob('*.txt')) as $key => $filename) {

	$m_data['filenames'][$key]['filename'] = $filename; 			

}

//make a new mustache template object
$m = new Mustache_Engine;

/*first paramater of the render method 
is the view, and the second paramater 
is the data array*/
echo $m->render(
	"
	<!DOCTYPE html>
	<html>
    <head>
        <title>GA Development Exercise</title>
		<meta name='author' content='Pavan Katepalli'>
        <link rel='stylesheet' type='text/css' href='style.css'>
        <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' 
        rel='stylesheet' type='text/css'>

    </head>     
    <body>
    	<div id='container'>
	    	<h1>Text File Search</h1>
	    	{{#is_not_txt_file}}
	    		<span class='error'>You need to upload a txt file.</span>
	    	{{/is_not_txt_file}}
	    	<p>
	    		It will default and use 
	    		<span class='strong'>pg2600.txt</span> 
	    		for the file, unless you choose a text file
	    		or upload one. 
	    		If you don't pick anything
	    		for the string, it will search for
	    	 	<span class='strong'>peace</span>. 
	    	</p>
	    	<form enctype='multipart/form-data' 
	    	action='ga-dev-exercise-pavan.php' method='POST'> 
		   
	    		<div class='form'>
		    		<label>What text file should we search?</label>
		    		<select name='file_choose'>
		    			<option value='choose'>Choose a text file</option>
		    			{{#filenames}}
		    				<option value='{{filename}}'>{{filename}}</option>    			
		    			{{/filenames}}
		    		</select>
	    		</div>

	    		<div class='form'>
		    		<label>Don't see your text file? Browse for it:</label> 
					<input type='file' name='new_text_file' id='file' />

	    		</div>

	    		<div class='form'>
		    		<label>What string do you want to search for?</label> 
		    		<input type='text' name='string_search' />
	    		</div>

	    		<div class='form'>
		    		<label>Make search case insensitive?</label> 
		    		<input type='checkbox' name='case_insensitive' 
		    		value='true' class='check' />
	    		</div>

	    		<input type='hidden' name='submit_yes' value='true' />
	    		<input type='submit' value='search' />
		    </form>

		    <hr>
		    <h2>Output:</h2>
		    {{#all_cases}}
		    	<p>Searching all cases of 
		    	<span class='highlight'>{{string_search}}</span> 
		    	in <span class='highlight'>{{file}}</span>:</p>
			{{/all_cases}}
			{{^all_cases}}
				<p>Searching <span class='highlight'>{{string_search}}</span>
				(case sensitive) in <span class='highlight'>{{file}}</span>:</p>	
			{{/all_cases}}

			<p><span class ='highlight'>{$string_search}</span> count: {{found}}</p>

			<p>average number of words between each instance of 
			<span class='highlight'>{$string_search}:</span> {{average}}</p>

		</div>
	    	<script 
	    	src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'></script>
	</body>
	</html>
	"
	, $m_data
);
