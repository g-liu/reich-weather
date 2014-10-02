<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>QUnit Testing</title>
	<link rel="stylesheet" href="//code.jquery.com/qunit/qunit-1.14.0.css" />
	<base href="/sandbox/reichweather/"></base>
</head>
<body>
	<div id="qunit"></div>
	<div id="qunit-fixture"></div>
	<script src="//code.jquery.com/qunit/qunit-1.14.0.js"></script>
	<script>
		test( "hello test", function() {
			ok( 1 == "1", "Passed!" );
		});
		
		test( "another test", function() {
			ok( true == true, "passed" );
		});
	</script>
</body>
</html>