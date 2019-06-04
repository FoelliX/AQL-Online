<?php
	include('config.php');
?>

<!doctype html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>AQL-Online</title>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
		<link rel="icon" href="favicon.ico" type="image/x-icon">
		<link rel="stylesheet" type="text/css" media="screen" href="style.css">
		<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
		<script src="jquery.ns-autogrow.min.js"></script>
	</head>
	<body>
		<div class="content">
			<div style="text-align:center;">
				<h1>AQL-Online</h1>
				<img src="logo.png" alt="AQL-Logo" height="150" />
			</div>
			<hr />
			<?php
				echo "<strong>AQL-WebService:</strong> ".$webservice."<br /><strong>Available:</strong> ";
			?>
			<script type="text/javascript">
				window.onload = function() {
					available();
				};
			</script>
			<span id="available">checking...</span>
			<hr />
			<h3>Query-Formular</h3>
			<form id="form" method="POST" enctype="multipart/form-data">
				Please enter your <a href="https://github.com/FoelliX/AQL-System/wiki/Questions" target="_blank">AQL-Query</a> below:<br />
				<textarea id="query" name="query" rows="10"></textarea><br />
				<br />
				Select all .apk files involved:<br />
				<input id="apps" name="files" type="file" multiple /><br />
				<br />
				<input id="btnAsk" class="btn" style="width:calc(100% + 6px);" type="submit" value="Ask query now!" />
			</form>
			<hr />
			<h3>Queue</h3>
			<div class="item">
				<table id="queue" width="100%">
					<tr><th>Query</th><th>.apks</th><th>Status</th><th>Answer</th></tr>
				</table>
			</div>
			<hr />
			<h3>AQL-Answer</h3>
			<div class="item">
				<table width="100%">
					<tr>
						<td>Link: <a id="answerlink" target="_blank">-</a></td>
						<td align="right"><a id="graphlink" class="btn" target="_blank" style="display:none;">Watch Graph</a></td>
					</tr>
				</table>
				Content:<br />
				<textarea id="answer" style="overflow:hidden;" readonly></textarea>
			</div>
		</div>
		
		<script type="text/javascript">
			function formatXml(xml) {
				var formatted = '';
				var reg = /(>)(<)(\/*)/g;
				xml = xml.replace(reg, '$1\r\n$2$3');
				var pad = 0;
				jQuery.each(xml.split('\r\n'), function(index, node) {
					var indent = 0;
					if (node.match( /.+<\/\w[^>]*>$/ )) {
						indent = 0;
					} else if (node.match( /^<\/\w/ )) {
						if (pad != 0) {
							pad -= 1;
						}
					} else if (node.match( /^<\w([^>]*[^\/])?>.*$/ )) {
						indent = 1;
					} else {
						indent = 0;
					}

					var padding = '';
					for (var i = 0; i < pad; i++) {
						padding += '  ';
					}

					formatted += padding + node + '\r\n';
					pad += indent;
				});

				return formatted;
			}
			
			function available(){
				$.get("<?php echo $webservice; ?>/index.html", function(data) {
					document.getElementById("available").innerHTML = "true";
				}).fail(function() {
					document.getElementById("available").innerHTML = "false";
				});
			}
			
			function loadAnswer(id) {
				$.get("<?php echo $webservice; ?>/answer/" + id, function(data) {
					$("#answer").text((new XMLSerializer()).serializeToString(data));
					$("#answer").autogrow({vertical: true, horizontal: false, flickering: false});
					$("#answerlink").text("<?php echo $webservice; ?>/answer/" + id);
					$("#answerlink").attr("href", "<?php echo $webservice; ?>/answer/" + id);
					$("#graphlink").css("display", "inline-block");
					$("#graphlink").attr("href", "web/?jsonFile=<?php echo $webservice; ?>/webanswer/" + id);
					console.log((new XMLSerializer()).serializeToString(data));
				});
			}
			
			function doPoll(id, cell_status, cell_answer){
				$.get("<?php echo $webservice; ?>/status/" + id, function(data) {
					cell_status.innerHTML = data;
					if(data == "Done") cell_answer.innerHTML = "<a class=\"btn\" onClick=\"loadAnswer(" + id + ")\">" + id + "</a>";
					else {
						cell_answer.innerHTML = "-";
						setTimeout(doPoll, <?php echo $interval; ?>000, id, cell_status, cell_answer);
					}
				});
			}
			
			$(document).ready(function () {
				$("#btnAsk").click(function (event) {
					// Stop submit the form, we will post it manually.
					event.preventDefault();
					
					// Add element to queue
					var table = document.getElementById("queue");
					var row = table.insertRow(1);
					var cell_query = row.insertCell(0);
					var cell_apps = row.insertCell(1);
					var cell_status = row.insertCell(2);
					var cell_answer = row.insertCell(3);
					cell_query.innerHTML = $("#query").val();
					cell_apps.innerHTML = $("#apps").val();
					cell_status.innerHTML = "Uploading..";
					cell_answer.innerHTML = "-";

					var form = $('#form')[0];
					var data = new FormData(form);
					// data.append("CustomField", "This is some extra data, testing");
					$("#btnAsk").prop("disabled", true);
					$.ajax({
						type: "POST",
						enctype: 'multipart/form-data',
						url: "<?php echo $webservice; ?>/query",
						data: data,
						processData: false,
						contentType: false,
						cache: false,
						timeout: 600000,
						success: function (data) {
							doPoll(data, cell_status, cell_answer);
							$("#btnAsk").prop("disabled", false);
						},
						error: function (e) {
							cell_status.innerHTML = "Error: " + e.responseText;
							$("#btnAsk").prop("disabled", false);
						}
					});
				});
			});
		</script>
	</body>
</html>