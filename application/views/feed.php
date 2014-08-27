<!doctype html>
<html>
	<head>
	    <meta charset="utf-8">
	    <script src="/pace/pace.js"></script>
	    <link rel="stylesheet" type="text/css" href="css/flash.css">
		<title>Spread The Word</title>
		<link rel="icon" href="<?php echo base_url()?>/favicon.ico" type="image/ico">
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link href='http://fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
		<link rel="stylesheet" type="text/css" href="/css/tooltipster.css" />
		<link rel="stylesheet" href="/css/jquery.fancybox.css" type="text/css" media="screen" />
		<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
		<link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.5.0/pure-min.css">
		<link rel="stylesheet" type="text/css" href="/css/feed.css">
	</head>
	<body>
	    <?php
	        function ago($time)
            {
               $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
               $lengths = array("60","60","24","7","4.35","12","10");
            
               $now = time();
            
                   $difference     = $now - $time;
                   $tense         = "ago";
            
               for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
                   $difference /= $lengths[$j];
               }
            
               $difference = round($difference);
            
               if($difference != 1) {
                   $periods[$j].= "s";
               }
            
               return "$difference $periods[$j] ago ";
            }
	    ?>
		<div id="container">
			<div id="main_title">
				<img src="/images/favicon.png" id="title_icon"/>
				Spread The Word
			</div>
			<textarea placeholder="Spread a new word..." maxlength="500" id="feed_input_textarea" ></textarea>
			<br/>
			<span id="char_counter">500</span>
			<button id="feed_submit_button" class="pure-button pure-button-primary">Post</button>
			<div id="user_level">
				Your informant level is <b><?php echo $user_level; ?></b>&nbsp;
				<img src="/images/help.png" id="help_icon" class="tooltip"/>
			</div>
			<hr/>
			
			<div class="my_stuff_mains" id="my_feeds_main">
				<div class="my_open_toggles" id="my_feeds_open">
					<img class="arrow_right_icons" src="/images/arrow_right.png"/>
					My Words <b>(<?php echo $num_mywords;?>)</b>
				</div>
				<div class="my_lists" id="my_feeds_list">
					<?php //$con=mysqli_connect("localhost","root","","freeforsale"); ?>
					<?php foreach ($myfeeds as $item):?>
						<?php echo '<div title="Click to show comments for this word" class="feed_items" id="feed_item_'.$item['ID'].'">
						<a class="comment_view_links" href="/index.php/feed/comment/'.$item['ID'].'"></a>
										<div class="feed_item_messages">'.$item['message'].'</div>';
										
										$sql = "SELECT * FROM likes WHERE feedID='".$item['ID']."' AND ipID='".$ipID."'";
										$result = mysql_query($sql) or die(mysql_error());
										$row = mysql_fetch_array($result); 
										$num_results = mysql_num_rows($result);
										if($num_results == 0){
											echo '<a href="#" class="like_buttons">
											<i class="fa fa-bullhorn"></i>
											Spread
											</a>';
										}else{
											echo '<span class="afterlike_messages">The word has been spread</span>';
										}
										
										//echo '<span class="afterlike_messages">The word has been spread</span>';
										echo '<span class="likecount_spans"><span class="location_spans">From '.$item['location'].'</span>&nbsp;<span class="blueify">'.$item['likes'].'</span> Spreads</span>';
									echo '</div>';
						?>
					<?php endforeach?>
				</div>
			</div>
			
			<div class="my_stuff_mains" id="my_spreads_main">
				<div class="my_open_toggles" id="my_spreads_open">
					<img class="arrow_right_icons" src="/images/arrow_right.png"/>
					My Spreads <b>(<?php echo $num_myspreads;?>)</b>
				</div>
				<div class="my_lists" id="my_spreads_list">
					<?php $con=mysqli_connect("localhost","peterkim95","Simpsons12","stw"); ?>
					<?php foreach ($myspreads as $item):?>
						<?php echo '<div title="Click to show comments for this word" class="feed_items" id="feed_item_'.$item['ID'].'">
						                <a class="comment_view_links" href="/index.php/feed/comment/'.$item['ID'].'"></a>
										<div class="feed_item_messages">'.$item['message'].'</div>';
										/*
										$sql = "SELECT * FROM likes WHERE feedID='".$item['ID']."' AND ipID='".$ipID."'";
										$result = mysql_query($sql) or die(mysql_error());
										$row = mysql_fetch_array($result); 
										$num_results = mysql_num_rows($result);
										if($num_results == 0){
											echo '<a href="#" class="like_buttons">
											<i class="fa fa-bullhorn"></i>
											Spread
											</a>';
										}else{
											echo '<span class="afterlike_messages">The word has been spread</span>';
										}*/
										echo '<span class="afterlike_messages">The word has been spread</span>';
										echo '<span class="likecount_spans"><span class="location_spans">From '.$item['location'].'</span>&nbsp;<span class="blueify">'.$item['likes'].'</span> Spreads</span>';
									echo '</div>';
						?>
					<?php endforeach?>
				</div>
			</div>
			
			<span id="loading_image"></span>
			<div id="feed_city_information">&nbsp;
				Fetching your location...
			</div>

			<div id="feed_main_div">
				<?php $con=mysqli_connect("localhost","peterkim95","Simpsons12","stw"); ?>
				<?php foreach ($feeds as $item):?>
					<?php echo '<div class="feed_items" title="Click to show comments for this word" id="feed_item_'.$item['ID'].'">
					                <a class="comment_view_links" href="/index.php/feed/comment/'.$item['ID'].'"></a>
					                <div class="rank_dev_panel">
					                <span>Distance: '.$item['distance'].'</span><br/>
					                <span>Spreads: '.$item['spreads'].'</span><br/>
					                <span>Time: '.$item['time'].'</span><br/>
					                <span>Informant Level: '.$item['informantlev'].'</span><br/>
					                <span>Rank: '.$item['rank'].'</span>
					                </div>
									<div class="feed_item_messages">'.$item['message'].'</div>';
									$sql = "SELECT * FROM likes WHERE feedID='".$item['ID']."' AND ipID='".$ipID."'";
									$result = mysql_query($sql) or die(mysql_error());
									$row = mysql_fetch_array($result); 
									$num_results = mysql_num_rows($result);
									if($num_results == 0){
										echo '<a href="#" class="like_buttons">
										<i class="fa fa-bullhorn"></i>
										Spread
										</a>';
									}else{
										echo '<span class="afterlike_messages">The word has been spread</span>';
									}
									
									
                                    
									echo '<span class="likecount_spans"><abbr class="time_spans">'.ago(strtotime($item['timestamp'])).'</abbr><span class="location_spans">From '.$item['location'].'</span>&nbsp;<span class="blueify">'.$item['likes'].'</span> Spreads</span>';
								echo '</div>';
					?>
				<?php endforeach?>
			</div>
		</div>
		<input type="hidden" id="input_lat_hidden"/>
		<input type="hidden" id="input_long_hidden"/>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
		<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script> 
		<script type="text/javascript" src="/js/noty/packaged/jquery.noty.packaged.min.js"></script>
		<script type="text/javascript" src="/js/jquery.tooltipster.min.js"></script>
		<script type="text/javascript" src="/js/jQueryRotateCompressed.js"></script>
		<script type="text/javascript" src="/js/jquery.fancybox.pack.js"></script>
		<script type='text/javascript' src="https://github.com/petersendidit/jquery-timeago/raw/master/jquery.timeago.js"></script>

		<script src="/js/spin.min.js"></script>
		<script src="/js/jquery.autosize.min.js"></script>
		<script src="/js/feed.js"></script>
	</body>
</html>