<?php
class Feed extends CI_Controller
{
    public function index()
    {
		//$query = $this->db->query("SELECT * FROM feeds");
		
		$ip = $this->get_client_ip();
		$query = $this->db->query("SELECT * FROM ips WHERE ip='".$ip."'");
		
		if($query->num_rows() > 0){	//Existing & Returning User
			$row = $query->row_array(); 
			$data['ipID'] = $row['ID'];
			$curlong = $row['longitude'];
			$curlat = $row['latitude'];
            
            //Need to see if scalable.
			$sql = "SELECT ips.user_likes, feeds.ID, feeds.message, feeds.latitude, feeds.longitude, feeds.likes, feeds.timestamp, feeds.location,
					( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ) AS distance,
					feeds.likes AS spreads,
					(TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP())) AS time,
					FLOOR(ips.user_likes/10) AS informantlev,
                    (( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ) - feeds.likes + ((TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP()))/50000) - ((FLOOR(ips.user_likes/10))*100)) AS rank
					FROM feeds
					INNER JOIN ips
					ON feeds.ipID=ips.ID
					ORDER BY rank ASC
					LIMIT 0,20";
            /*
			//Need to see if scalable.
			$sql = "SELECT ips.user_likes, feeds.ID, feeds.message, feeds.latitude, feeds.longitude, feeds.likes, feeds.timestamp, 
					( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ) AS distance,
					feeds.likes AS spreads,
					(TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP())) AS time,
					FLOOR(ips.user_likes/10) AS informantlev,
					((1 / ((POW(( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ),feeds.likes)) * (TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP())))) + FLOOR(ips.user_likes/10)) AS rank
					FROM feeds
					INNER JOIN ips
					ON feeds.ipID=ips.ID
					ORDER BY rank DESC
					LIMIT 0,20";
			*/
			/*
			$sql = "SELECT ID, message, latitude, longitude, likes, timestamp, 
									((likes+0.1) / ( LN(TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP())) * POW(( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( latitude ) ) ) )+0.1,2))) AS rank  
									FROM feeds
									ORDER BY rank DESC
									LIMIT 0,20";
			*/
			//echo $sql;
			//To search by kilometers instead of miles, replace 3959 with 6371.
			$query = $this->db->query($sql);

			
			
			$data['feeds'] = $query->result_array();
			
			$query = $this->db->query("SELECT * FROM ips WHERE ID='".$row['ID']."'");
			$row2 = $query->row_array();
			$data['raw_user_likes'] = $row2['user_likes'];
			$data['user_level'] = floor($row2['user_likes']/10);
			
			$query = $this->db->query("SELECT * FROM feeds WHERE ipID='".$row['ID']."' ORDER BY timestamp DESC LIMIT 0,20");
			$data['num_mywords'] = $query->num_rows();
			$data['myfeeds'] = $query->result_array();
			
			$query = $this->db->query("SELECT feeds.ID, feeds.message, feeds.likes, feeds.ipID, feeds.location, feeds.latitude, feeds.longitude, feeds.timestamp FROM feeds INNER JOIN likes ON likes.feedID=feeds.ID AND likes.ipID='".$row['ID']."' ORDER BY likes.timestamp DESC LIMIT 0,20");
			$data['num_myspreads'] = $query->num_rows();
			$data['myspreads'] = $query->result_array();
            $this->load->view('feed',$data);
		}else{	//New User
		    $this->load->view('intro');
			//$this->load->view('setup');
		}
        
    }
	
	public function add_ip(){
		$latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
		$ip = $this->get_client_ip();
		
		$query = $this->db->query("SELECT * FROM ips WHERE ip='".$ip."'");
		if($query->num_rows() > 0){	//IP exists...
			$row = $query->row_array();
			$query = $this->db->query("SELECT * FROM ips WHERE ip='".$ip."' AND latitude='".$latitude."' AND longitude='".$longitude."'");	//but did the location change?
			if($query->num_rows() == 0){	//It did, so update it with the new location coordinates
				$data = array(
				   'latitude' => $latitude ,
				   'longitude' => $longitude
				);
				$this->db->update('ips', $data, "ID = ".$row['ID']);
				echo "Updated Record";
			}
		}else{	//IP Doesn't Exist.
			$data = array(
			   'ip' =>  $ip,
			   'latitude' => $latitude ,
			   'longitude' => $longitude
			);
			$this->db->insert('ips', $data);
			echo "Inserted New IP Record";
		}
	}

	
    public function add_feed()
    { 
        $latitude = $this->input->post('latitude');
        $longitude = $this->input->post('longitude');
		$message  = $this->input->post('message');
		$location  = $this->input->post('location');
		
		$ip = $this->get_ip_id();
		
        $data = array(
			'ipID' => $ip,
			'message' => $message ,
			'latitude' => $latitude ,
			'longitude' => $longitude,
			'location' => $location
        );
        $this->db->insert('feeds', $data); 
    }
	
	public function spread_feed($id)
	{
		$ip = $this->get_ip_id();

		$data = array(
			'ipID' => $ip,
			'feedID' => $id
		);
		$this->db->insert('likes', $data); 
		
		$query = $this->db->query("UPDATE feeds SET likes=likes+1 WHERE ID='".$id."'");
		$query = $this->db->query("SELECT * FROM feeds WHERE ID='".$id."'");
		$row = $query->row_array(); 
		$query = $this->db->query("UPDATE ips SET user_likes=user_likes+1 WHERE ID='".$row['ipID']."'");
	}
	
	public function refresh_feed()
	{
		$curlong = $this->input->post('longitude');
		$curlat = $this->input->post('latitude');
		$ipID = $this->get_ip_id();

		//Need to see if scalable.
		$sql = "SELECT ips.user_likes, feeds.ID, feeds.message, feeds.latitude, feeds.longitude, feeds.likes, feeds.timestamp, feeds.location,
				( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ) AS distance,
				feeds.likes AS spreads,
				(TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP())) AS time,
				FLOOR(ips.user_likes/10) AS informantlev,
                (( 3959 * acos( cos( radians(".$curlat.") ) * cos( radians( feeds.latitude ) ) * cos( radians( feeds.longitude ) - radians(".$curlong.") ) + sin( radians(".$curlat.") ) * sin( radians( feeds.latitude ) ) ) ) - feeds.likes + ((TIMESTAMPDIFF(SECOND, feeds.timestamp, CURRENT_TIMESTAMP()))/50000) - ((FLOOR(ips.user_likes/10))*100)) AS rank
				FROM feeds
				INNER JOIN ips
				ON feeds.ipID=ips.ID
				ORDER BY rank ASC
				LIMIT 0,20";
		$query = $this->db->query($sql);
		
		//$con=mysqli_connect("localhost","root","","freeforsale");
		foreach ($query->result_array() as $item){
			echo '<div class="feed_items" id="feed_item_'.$item['ID'].'">
			<a class="comment_view_links" href="/index.php/feed/comment/'.$item['ID'].'"></a>
						<div class="feed_item_messages">'.$item['message'].'</div>';
						$q = $this->db->query("SELECT * FROM likes WHERE feedID='".$item['ID']."' AND ipID='".$ipID."'");
						//$result = mysql_query($sql) or die(mysql_error());
						//$row = mysql_fetch_array($result); 
						//$num_results = mysql_num_rows($result);
						if($q->num_rows() == 0){
							echo '<a href="#" class="like_buttons">
								<i class="fa fa-bullhorn"></i>
								Spread
								</a>';
						}else{
							echo '<span class="afterlike_messages">The word has been spread</span>';
						}
						
						echo '<span class="likecount_spans"><abbr class="time_spans">'.$this->humanTiming(strtotime($item['timestamp'])).' ago</abbr><span class="location_spans">From '.$item['location'].'</span><span class="blueify">'.$item['likes'].'</span> Spreads</span>';
					echo '</div>';
		}

	}
	
	public function add_comment($fid){
	    $ip = $this->get_ip_id();
	    $message = $this->input->post('message');
	    //echo $message;
	    $data = array(
			'ipID' => $ip,
			'feedID' => $fid,
			'message' => $message
		);
		$this->db->insert('comments', $data); 
	    
	    $query = $this->db->query("SELECT * FROM comments WHERE feedID='".$fid."' ORDER BY timestamp ASC");
	    foreach ($query->result() as $row){
	        echo '
	        <div class="comment_items">
	            <div class="comment_messages">'.$row->message.'</div>
	        </div>';
	    }
	}
	
	public function comment($id)
	{
	    $query = $this->db->query("SELECT * FROM feeds WHERE ID='".$id."'");
	    $ip = $this->get_ip_id();
	    if ($query->num_rows() > 0)
        {
           foreach ($query->result() as $row)
           {
              echo '<div class="feed_items" id="feed_item_'.$row->ID.'">
				<div class="feed_item_messages">'.$row->message.'</div>';
				$q = $this->db->query("SELECT * FROM likes WHERE feedID='".$row->ID."' AND ipID='".$ip."'");
				//$result = mysql_query($sql) or die(mysql_error());
				//$row = mysql_fetch_array($result); 
				//$num_results = mysql_num_rows($result);
				if($q->num_rows() == 0){
					echo '<a href="#" class="like_buttons">
						<i class="fa fa-bullhorn"></i>
						Spread
						</a>';
				}else{
					echo '<span class="afterlike_messages">The word has been spread</span>';
				}
				
				echo '<span class="likecount_spans"><span class="location_spans">From '.$row->location.'</span><span class="blueify">'.$row->likes.'</span> Spreads</span>';
			echo '</div>';
           }
        }
	    echo '<div class="comment_mains">';
	    $query = $this->db->query("SELECT * FROM comments WHERE feedID='".$id."' ORDER BY timestamp ASC");
	    if($query->num_rows() == 0){
	        echo '<center><div class="no_comments">No <span class="blueify">comments</span> for this word yet. You can be the first.</div></center>';
	    }else{
    	    foreach ($query->result_array() as $item){
    	        echo '<div class="comment_items"><div class="comment_messages">'.$item['message'].'</div><span class="comment_times">'.$this->humanTiming(strtotime($item['timestamp'])).' ago</span></div>';
    	    } 
	    }
	    echo '</div>';
	    echo '<div class="comment_areas" id="">
	            <input autofocus placeholder="Leave a comment..." maxlength="500" class="comment_inputs" type="text" />
	        </div>';
	    
	}
	
	public function humanTiming ($time)
    {
    
        $time = time() - $time; // to get the time since that moment
    
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );
    
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }
    
    }
    
	public function get_ip_id(){
		$ip = $this->get_client_ip();
		$query = $this->db->query("SELECT * FROM ips WHERE ip='".$ip."'");	
		$row = $query->row_array(); 
		return $row['ID'];
	}
	
	public function get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
}
?>