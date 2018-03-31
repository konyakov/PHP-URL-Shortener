<!DOCTYPE html>
<html>
<title>URL shortener</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</html>
<body>
<form method="post" action="shorten.php" id="shortener">
<label for="longurl">URL to shorten</label> <input type="text" name="longurl" id="longurl"> <input type="submit" value="Shorten">
</form>
</form>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script>
$(function () {
	$('#shortener').submit(function () {
		$.ajax({data: {longurl: $('#longurl').val()}, url: 'shorten.php', complete: function (XMLHttpRequest, textStatus) {
			$('#longurl').val(XMLHttpRequest.responseText);
		}});
		return false;
	});
});
</script>
</body>
</html>
