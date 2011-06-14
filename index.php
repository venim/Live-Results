<?php

function cmpBest($a, $b){
	if ($a==$b)
		return 0;
	if ($a > 0){
		if ($b > 0)			//
			return ($a < $b) ? -1 : 1;	
		return -1;
	}
	if ($a < 0){
		if ($b < 0)
			return ($a < $b) ? 1 : -1;
		if ($b > 0)
			return 1;	
		return -1;
	}
	return 1;
}

function cmp($a, $b){
	$x= cmpBest($a[9], $b[9]);
	return ($x) ? $x : (cmpBest($a[8], $b[8]));
}

function toTime($time, $type=''){
	if ($time=='-1')
		return 'DNF';
	elseif ($time=='-2')
		return 'DNS';
	elseif ($time=='0')
		return '&nbsp;';
	$milli=$time%100;
	$time=(int)($time/100);
	$sec=$time%60;
	$time=(int)($time/60);
	$min=$time%60;
	$str='';
	if ($min!=0){
		$str.=$min.':';
		if (strlen($sec)<2)
			$str.='0'.$sec;
		else
			$str.=$sec;
	}
	else
		$str.=$sec;
	if (!$type)
		$str.='.'.sprintf('%\'02d',$milli);
	return $str;
}

$db_host = '';
$db_user = '';
$db_pwd = '';
$database = '';

if ($_POST['action']=='getdb'){
	if (!mysql_connect($db_host, $db_user, $db_pwd))
	    die("Can't connect to database");
	
	if (!mysql_select_db($database))
	    die("Can't select database");
	
	$events=array("333" => "Rubik's Cube","444" => "4x4x4 Cube","555" => "5x5x5 Cube", "222" => "2x2x2 Cube", "333bf" => "3x3x3 blindfolded","333oh" => "3x3x3 one-handed", "333wf" => "3x3x3 with feet","333fm" => "3x3x3 fewest moves","magic" => "Rubik's Magic","mmagic" => "Rubik's Master Magic","minx" => "Megaminx", "pyram" => "Pyraminx","sq1" => "Square-1","clock" => "Rubik's Clock","666" => "6x6x6 Cube","777" => "7x7x7 Cube","444bf" => "4x4x4 blindfolded", "555bf" => "5x5x5 blindfolded","333mbf" => "3x3x3 multi blind");
	
	$comp = $_POST['comp'];
	$puzzle=$_POST['puzzle'];
	$round=$_POST['round'];
	$rounds=array("q" => "Qualification Round", "1" => "First Round", "2" => "Second Round", "e" => "Combined First round / B Final", "d" => "Combined First / Second round", "3" => "Semi final", "b" => "B Final", "c" => "Combined Final", "f" => "Final");
	$formats=array("a" => "Average of 5", "m" => "Mean of 3", "1" => "Best of 1", "2" => "Best of 2", "3" => "Best of 3");
	$formatlen=array("a" => 5, "m" => 3, "1" => 1, "2" => 2, "3" => 3);
	$result=mysql_query("select formatId from Results where eventId='$puzzle' and competitionId='$comp' and roundId='$round';");
	$row=mysql_fetch_row($result);
	$format=$row[0];
	
	$result=mysql_query("select distinct pos, personName, personId, value1, value2, value3, value4, value5, best, average from Results where eventId='$puzzle' and roundId='$round' and competitionId='$comp' and best != 0;");
	
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' class='results'>";
	echo "<tr><td class='caption' colspan='8'><a class='e' href='http://www.worldcubeassociation.org/results/e.php?i=$puzzle'>{$events[$puzzle]}</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$rounds[$round]}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$formats[$format]}</td></tr>";
	echo "<tr><th class='r'>Place</th><th>Person</th><th class='R'>Best</th><th>&nbsp;</th>";
	if (in_array($format, array('a','m')))
		echo "<th class='R'>Average</th>";	
	else
		echo "<th class='R'>&nbsp;</th>";
	echo "<th>&nbsp;</th>";
	for ($i=0; $i<$formatlen[$format]; $i++){
		echo "<th class='fr'>".($i+1)."</th>";
	}
	echo "</tr>";
	$rows=mysql_num_rows($result);
	$data=array();
	for ($i=0;$i<$rows; $i++){
		$row=mysql_fetch_row($result);
		$data[]=$row;	
	}
	usort($data, "cmp");
	
	for($i=0;$i<$rows; $i++){
		$row=$data[$i];
		if ($row[1]!=""){
			echo "<tr ";
			if ($i%2==1)
				echo "class='e'";
			echo "><td class='r'>".($i+$cnt+1)."</td>";
			echo "<td><a class='p' href='./p.php?p=".urlencode($row[1])."'>$row[1]</a></td>";
		
			echo "<td class='R'>";
			
			if (strcmp($puzzle, '333mbf')==0 and $row[8]>0){
				$x=$row[8];
				$t2=$x%100;
				$x=(int)($x/100);
				$t=$x%100000;
				$t1=(int)($x/100000);
				$solved=99+$t2-$t1;
				$tried=$t2+$solved;
				echo "$solved/$tried <span style='color:#999'>".toTime($t*100, '333mbf')."</span>";
			}
			elseif (strcmp($puzzle, '333fm')==0 and $row[8]>0)
				echo $row[8];
			else
				echo toTime($row[8]);
			echo "</td><td>&nbsp;</td>";
			if (in_array($format, array('a','m')))
				echo "<td class='R'>".toTime($row[9])."</th>";	
			else
				echo "<td class='R'>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			for ($x=3; $x<3+$formatlen[$format]; $x++){
				echo "<td class='fr' >";
				if (strcmp($puzzle, '333mbf')==0 and $row[$x]>0){
					$y=$row[$x];
					$t2=$y%100;
					$y=(int)($y/100);
					$t=$y%100000;
					$t1=(int)($y/100000);
					$solved=99+$t2-$t1;
					$tried=$t2+$solved;
					echo "$solved/$tried <span style='color:#999'>".toTime($t*100, '333mbf')."</span>";
				}
				elseif (strcmp($puzzle, '333fm')==0 and $row[$x]>0)
					echo $row[$x];
				else
					echo toTime($row[$x]);
				echo "</td>";
			}
			echo "</tr>";	
		}	
	}
	echo "</table>";
}
elseif ($_POST['action']=='type'){
	$events=array("333" => "Rubik's Cube","444" => "4x4x4 Cube","555" => "5x5x5 Cube", "222" => "2x2x2 Cube", "333bf" => "3x3x3 blindfolded","333oh" => "3x3x3 one-handed", "333wf" => "3x3x3 with feet","333fm" => "3x3x3 fewest moves","magic" => "Rubik's Magic","mmagic" => "Rubik's Master Magic","minx" => "Megaminx", "pyram" => "Pyraminx","sq1" => "Square-1","clock" => "Rubik's Clock","666" => "6x6x6 Cube","777" => "7x7x7 Cube","444bf" => "4x4x4 blindfolded", "555bf" => "5x5x5 blindfolded","333mbf" => "3x3x3 multi blind");

	if (!mysql_connect($db_host, $db_user, $db_pwd))
	    die("Can't connect to database");
	
	if (!mysql_select_db($database))
	    die("Can't select database");
	
	$str=""; 
	$cnt=0;
	$comp = $_POST['comp'];
	$result=mysql_query("select distinct eventId from Results where competitionId='$comp';");
	$arr=array();
	$rows=mysql_num_rows($result);
	for($i=0; $i<$rows; $i++){
		$row=mysql_fetch_row($result);
		$arr[]=$row[0];
	}
	foreach ($events as $event => $name){
		if (in_array($event, $arr))	{
			$str.= '<option name="'.$event.'">'.$name."</option>\n";
			$cnt+=1;
		}
	}
	echo json_encode(array("cnt"=>$cnt, "html"=> $str));
}
elseif ($_POST['action']=='round'){	
	$puzzle=$_POST['puzzle'];
	$comp = $_POST['comp'];
	
	if (!mysql_connect($db_host, $db_user, $db_pwd))
	    die("Can't connect to database");
	
	if (!mysql_select_db($database))
	    die("Can't select database");
	    
	$rounds=array("q" => "Qualification Round", "1" => "First Round", "2" => "Second Round", "e" => "Combined First round / B Final", "d" => "Combined First / Second round", "3" => "Semi final", "b" => "B Final", "c" => "Combined Final", "f" => "Final");
	$result=mysql_query("select distinct roundId from Results where competitionId='$comp' and eventId='$puzzle';");
	
	$str="";
	$cnt=0;
	$arr=array();
	$rows=mysql_num_rows($result);
	for($i=0; $i<$rows; $i++){
		$row=mysql_fetch_row($result);
		$arr[]=$row[0];
	}
	foreach ($rounds as $round => $name){
		if (in_array($round, $arr)){
			$str.= '<option value="'.$round.'">'.$name.'</option>\n';	
			$cnt+=1;
		}	
	}
	echo json_encode(array("cnt"=>$cnt, "html"=>$str));
	
}
else{
echo '
<html>
<head>
<link rel="stylesheet" type="text/css" href="../results/tables.css" />
<link rel="stylesheet" type="text/css" href="../results/general.css" />
<link rel="stylesheet" type="text/css" href="../results/links.css" />
<title>Live Results</title>
	<script type="text/JavaScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script> 
	<script type="text/JavaScript" src="./results.js"></script>
	<noscript>You REALLY need JavaScript enabled, or else none of this will work!</noscript>
	</head>
<body>
<table>
<tr><select id="comp" class="drop">';
if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    die("Can't select database");

$comps=array('UniverCidadeOpen2009' => "Univercidade Open 2009", 'UNESPOpen2009' => 'UNESPOpen2009');//'BigCubesSummer2009'=> "Big Cubes Summer 2009");//, 'SantiagoOpen2009'=> "Santiago Open 2009", 'UPennSpring2009' => 'UPenn Spring 2009');    
$result=mysql_query("select distinct competitionId from Results;");
$arr=array();
$rows=mysql_num_rows($result);
for($i=0; $i<$rows; $i++){
	$row=mysql_fetch_row($result);
	$arr[]=$row[0];
}
foreach($comps as $compId => $compName){
	if (in_array($compId, $arr)){
		echo '<option id="'.$compId.'">'.$compName."</option>\n";	
	}	
}


echo '
</select>
<tr>
<td style="vertical-align: top;">
		<h1><a>Results</a></h1>
<table><tr><td>
<select id="type" MULTIPLE></select></td><td style="vertical-align: bottom;">
<select id="round" MULTIPLE></select></td></tr></table>
</td><td style="vertical-align: top;">
	<p id="results"></p>
</td></tr></table>
</div>
</body>
</html>';}
?>