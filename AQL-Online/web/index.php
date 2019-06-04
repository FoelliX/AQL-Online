<html>
	<head>
		<title>AQL-WebView</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		
		<link rel="stylesheet" href="style.css" />
		<script src="cytoscape.umd.js"></script>
		<script src="cola.min.js"></script>
		<script src="cytoscape-cola.js"></script>
		
		<link rel="stylesheet" href="highlight/styles/foellix.css">
		<script src="highlight/highlight.pack.js"></script>
		<script>hljs.initHighlightingOnLoad();</script>
	</head>
	<body>
		<div class="content">
			<div class="menu">
				<label><input type="checkbox" onclick="toggleRight();" checked="checked" />Show XML</label>
				<label><input type="checkbox" onclick="toggleBottom();" checked="checked" />Show Details</label>
				<label>
					Layout: 
					<select onchange="layout(this.value);">
						<option value="breadthfirst">Breadthfirst</option>
						<option value="circle">Circle</option>
						<option value="concentric" selected="selected">Concentric</option>
						<option value="cola">Cola</option>
						<option value="grid">Grid</option>
					</select>
				</label>
				<label>
					Node-size:
					<input type="range" min="0" max="180" value="50" oninput="spacing(this.value)" onchange="spacing(this.value)" style="width:300px;" />
				</label>
				<input type="button" onclick="initCytoscape();" value="Reset" />
			</div>
			<div id="center" class="center">
				<div id="cyBox">
					<div id="cy"></div>
				</div>
			</div>
			<div id="right" class="right">
				<div id="xmlBox">
					<div id="xmlInnerBox"><pre><code id="xml" class="xml">-</code></pre></div>
				</div>
				<div class="title2">XML</div>
			</div>
			<div id="bottom" class="bottom">
				<div id="verboseBox">
					<div id="verboseInnerBox"><textarea id="verbose" resizeable="false">-</textarea></div>
				</div>
				<div class="title1">Details</div>
			</div>
		</div>
		
		<script>
			var layoutType = "concentric";
			var layoutSpacing = 1;
			var edgeLengthValue = 520;
			
			var verbose = document.getElementById('verbose');
			var xml = document.getElementById('xml');
			var myObject = {};
			var cy = {};

			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function () {
				myObject = JSON.parse(this.responseText);
				initCytoscape();
			};
			xhttp.open("GET", "<?php echo stripslashes($_GET["jsonFile"]); ?>", true);
			xhttp.send();
			
			function initCytoscape() {
				cy = cytoscape({
						container: document.getElementById('cy'),
						elements: myObject,
						zoom: 1,
						style: [{
								selector: 'node',
								style: {
									'content': 'data(label)',
									'background-color': 'data(color)',
									'border-color': 'data(color)',
									'border-width': 3,
									'shape': 'rectangle',
									'width': 'label',
									'height': 'label',
									'text-valign': 'center',
									'text-wrap': 'wrap',
									'text-max-width': 1000,
									'font-family': 'Myriad Pro, Calibri, Helvetica, Arial, sans-serif',
									'font-size': '18px',
									'font-weight': 'normal',
									'padding': '10px'
								}
							}, {
								selector: '.nodeselected',
								style: {
									'border-color': '#000000'
								}
							}, {
								selector: '.nodehidden',
								style: {
									'display': 'none'
								}
							}, {
								selector: '.nodehover',
								style: {
									'border-color': '#000000',
									'border-style': 'dashed'
								}
							}, {
								selector: 'edge',
								style: {
									'curve-style': 'bezier',
									'width': 'data(width)',
									'line-color': 'data(color)',
									'target-arrow-color': 'data(color)',
									'target-arrow-shape': 'triangle'
								}

							}, {
								selector: '.edgeselected',
								style: {
									'width': 3
								}
							}, {
								selector: '.edgehidden',
								style: {
									'display': 'none'
								}
							}, {
								selector: '.edgehover',
								style: {
									'line-style': 'dashed',
									'width': 3
								}
							}
						],

						layout: {
							name: layoutType,
							spacingFactor: layoutSpacing
						}
					});

				cy.on('cxttapstart', 'node', function (e) {
					e.target.addClass('nodehidden');
					e.target.outgoers("edge").addClass('edgehidden');
				});
				
				cy.on('cxttapstart', 'edge', function (e) {
					e.target.addClass('edgehidden');
				});
				
				cy.on('vmousedown', 'node', function (e) {
					for (x in myObject.nodes) {
						if (myObject.nodes[x].data.id == e.target.id()) {
							verbose.innerHTML = myObject.nodes[x].data.verbose.replace(new RegExp('->', 'g'), '\n->');
							xml.innerHTML = hljs.highlight("xml", myObject.nodes[x].data.xml).value;
							break;
						}
					}
				});

				cy.on('taphold', 'node', function (e) {
					e.target.removeClass('nodehover');
					e.target.outgoers("edge").removeClass('edgehover');
					e.target.addClass('nodeselected');
					e.target.outgoers("edge").addClass('edgeselected');
				});
				
				cy.on('mouseover', 'node', function (e) {
					e.target.addClass('nodehover');
					e.target.outgoers("edge").addClass('edgehover');
				});

				cy.on('mouseout', 'node', function (e) {
					e.target.removeClass('nodehover');
					e.target.outgoers("edge").removeClass('edgehover');
				});
				
				cy.on('mouseover', 'edge', function (e) {
					e.target.addClass('edgehover');
				});

				cy.on('mouseout', 'edge', function (e) {
					e.target.removeClass('edgehover');
				});
				
				refresh(true);
			}
			
			function toggleRight(elementId) {
				var right = document.getElementById("right");
				var center = document.getElementById("center");
				if(right.style.display == "none") {
					center.style.width = "75%";
					right.style.display = "block";
				} else {
					right.style.display = "none";
					center.style.width = "100%";
				}
				initCytoscape();
			}
			function toggleBottom(elementId) {
				var right = document.getElementById("right");
				var bottom = document.getElementById("bottom");
				var center = document.getElementById("center");
				if(bottom.style.display == "none") {
					right.style.height = "85%";
					center.style.height = "85%";
					bottom.style.display = "block";
				} else {
					bottom.style.display = "none";
					right.style.height = "100%";
					center.style.height = "100%";
				}
				initCytoscape();
			}
			
			function refresh(ani) {
				var layout;
				if(layoutType == 'cola') {
					layout = cy.layout({
						name: layoutType,
						animate: true,
						randomize: ani,
						avoidOverlap: true,
						edgeLength: edgeLengthValue,
						maxSimulationTime: 30000
					});
				} else {
					layout = cy.layout({
						name: layoutType,
						animate: ani,
						spacingFactor: layoutSpacing
					});
				}
				layout.run();
			}
			
			function layout(newLayout) {
				layoutType = newLayout;
				refresh(true);
			}
			
			function spacing(newValue) {
				layoutSpacing = 2 - (newValue / 100);
				edgeLengthValue = 720 - (4 * newValue);
				refresh(false);
			}
		</script>
	</body>
</html>