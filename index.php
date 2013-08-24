<?
	$key  = $_REQUEST["key"];

	if (!$key) {
		$hash = md5(time());
		$sub  = rand(1,20);
		$id   = substr($hash,$sub,6);

		header("Location: ./".$id); exit();
	}
	

	//$key  = "local";
	$path = "./data/".$key.".todo.json";

	function saveList($list) {
		global $path;
		
		$json = json_encode($list);

		$fr = fopen($path, "w");
		fwrite($fr, $json);
		fclose($fr);

		return($list);
	}	

	function getTag($s) {
		$name = "";
		if (substr($s,0,1) == "@") {
			 $p    = explode(" ",$s);
			 $name = str_replace(array("@",":"),array("",""), current($p));
		}

		return($name);
	}

	function notag($t) {
		$tag = getTag($t);
		return(trim(str_replace("@".$tag,"",$t)));
	}


	if (!file_exists($path)) {
		saveList(
			array(
				"list"=>array(
					array("id"=>time(),"name"=>"Bookmark this URL for later"),
					array("id"=>time()+1,"name"=>"Add shortcut on your phone home screen")
				),
				"tags"=>array()
			)
		);
	}

	$saved = file_get_contents($path);
	$db    = json_decode($saved, true);

	if ($_POST["newtask"]) {
		$t = trim($_POST["newtask"]);
		$f = substr($t,0,1);

		if ($f == "@") {
			$p  = explode(" ",$t);
			$rm = false;
			$tag = trim(current($p));
			
			foreach ($db["tags"] as $k => $a_item) {
				if (trim($a_item["name"]) == $tag) {
					// Tag exists
					$rm = true;
					
					// Remove tag from shortcut list if its submitted alone
					if (count($p) <= 1) {
						$t  = false;
						unset($db["tags"][$k]);
						saveList($db);
						break;
					}
				}
			}
			
			if (count($p) <= 1) { $t = false; }
			
			// Add new tag if it wasnt in the list already
			if (!$rm) {
				array_push($db["tags"], array(
					"id" => time(),
					"name" => $tag
				));
			}
		} 

		// Add task to list
		if ($t) {
			array_unshift($db["list"], array(
				"id" => time(),
				"name" => $t
			));
		}

		saveList($db);
	}

	// Remove task from list; ajax response
	if ($_GET["done"]) {
		foreach ($db["list"] as $k => $a_item) {
			if ($a_item["id"] == $_GET["done"]) {
				unset($db["list"][$k]);
				break;
			}
		}

		saveList($db);
		exit();
	}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>2DO</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<link href="apple-touch-icon-precomposed.png" rel="apple-touch-icon-precomposed" />
	<link href="apple-touch-icon-72x72-precomposed.png" rel="apple-touch-icon-precomposed" sizes="72x72" />
	<link href="apple-touch-icon-114x114-precomposed.png" rel="apple-touch-icon-precomposed" sizes="114x114" />
	<link href="apple-touch-icon-144x144-precomposed.png" rel="apple-touch-icon-precomposed" sizes="144x144" />	
	<meta name="msapplication-TileImage" content="apple-touch-icon-144x144-precomposed.png"/>
	<meta name="msapplication-TileColor" content="#3366cc"/>
	<meta name="application-name" content="2DO" />

	<style>
		html{font-size:62.5%;font-family:arial, helvetica, sans-serif;background-color:#fff}
		body{font-size:1.8rem;margin:0;padding:0}
		a{text-decoration:none}
		*{-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box}
		#layout{max-width:800px}
		ul,li{list-style:none;margin:0;padding:0;position:relative;}
		form{background-color:#333;padding:10px 20px}
		input{font-size:2rem;display:block;width:100%}

		.refresh { display:block; text-align:center; color:#ccc; padding:20px 0 10px; }
		.key { display:block;text-align:right;font-size:1.2rem;color:#ccc; }
		#todolist a.filter { display:inline; color:#999; background-color:#efefef; border-radius:3px; padding:2px 4px; }

		#todolist li { border-bottom:dotted 1px #999;padding:13px 15px }
		#todolist li span { text-decoration:line-through; color:#ccc;}
		
		#tags { margin-top: 8px; display:block; overflow:hidden; width:100%;}
		#tags li { float: left; padding-right: 8px; font-size: 1.4rem; }
		#tags a { color: #fff; padding:8px; }

		@media all and (min-width: 501px) { 
			#todolist li a { display: block; }
			#todolist a.filter { position: absolute; right:20px; top:8px; color:#999; background-color:#efefef; border-radius:3px; padding:2px 4px; }
		}		
		
		@media all and (min-width: 801px) { 
			#layout{margin-left:5%} 
		}
	</style>
	<script>
		var activeFilter = "none";
		var userkey      = "<?= $key; ?>";

		function taskdone(e){var t=e.getAttribute("data-id");e.innerHTML="<span>"+e.innerHTML+"</span>";ajax("index.php?done="+t+"&key="+userkey,function(t){e.parentNode.style.display="none"});return false}function addtag(e){var t=e.innerHTML;document.getElementById("taskname").value=t+" ";document.getElementById("taskname").focus();return false}function filter(e){var t=e.parentNode.className;var n=document.getElementById("todolist").getElementsByTagName("li");for(a=0;a<n.length;a++){if(n[a].className.indexOf(t)<0){n[a].style.display="none"}}return false}function ajax(e,t){var n;n=new XMLHttpRequest;n.onreadystatechange=function(){if(n.readyState==4&&n.status==200){t(n.responseText)}};n.open("GET",e,true);n.send()}
	</script>
</head>
<body>
	<div id="layout">
		<form id="newtask" method="post" action="./<?= $key; ?>">
			<input type="text" id="taskname" name="newtask" />
			<input type="hidden" name="key" value="<?= $key; ?>" />
			<ul id="tags">
				<? foreach ($db["tags"] as $a_item) { ?>
				<li>
					<a href="#" data-id="<?= $a_item["id"]; ?>" onclick="return addtag(this);"><?= $a_item["name"]; ?></a>
				</li>
				<? } ?>
			</ul>
		</form>
		<ul id="todolist">
			<? foreach ($db["list"] as $a_item) { $i++; $tag = getTag($a_item["name"]); ?>
			<li class="<?= $tag; ?>">
				<a href="#" data-id="<?= $a_item["id"]; ?>" onclick="return taskdone(this);"><?= notag($a_item["name"]); ?></a>
				<? if ($tag) { ?><a href="#" class="filter" onclick="return filter(this);">@<?= $tag; ?></a> <? } ?>
			</li>
			<?
				} 

				if ($i <= 0) { echo '<p style="text-align:center"><em>Nothing to do!</em></p>'; }
			?>
		</ul>
		<a href="./<?= $key; ?>" class="refresh"><em>Show all</em></a>
		<code class="key"><?= $key; ?></code>
	</div>
	<script>document.getElementById("taskname").focus();</script>
</body>
</html>
