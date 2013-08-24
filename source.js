		function taskdone(obj) { 
			var id = obj.getAttribute("data-id");
			obj.innerHTML = '<span>'+obj.innerHTML+'</span>';
			ajax("index.php?done="+id+"&key="+userkey, function(r) {
				obj.parentNode.style.display = "none";
			});

			return(false);
		}

		function addtag(obj) {
			var t = obj.innerHTML;
			document.getElementById("taskname").value = t+" ";
			document.getElementById("taskname").focus();
			return(false);
		}

		function filter(obj) {
			var activeFilter = obj.parentNode.className;
			var list         = document.getElementById("todolist").getElementsByTagName("li");
			
			for (a=0; a < list.length; a++) {
				if (list[a].className.indexOf(activeFilter) < 0) {
					list[a].style.display = "none";
				}
			}

			return(false);
		}

		function ajax(url, callback) {
		    var xmlhttp;
		    xmlhttp = new XMLHttpRequest();

		    xmlhttp.onreadystatechange = function() {
		        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) { callback(xmlhttp.responseText); }
		    }
		    xmlhttp.open("GET", url, true);
		    xmlhttp.send();
		}