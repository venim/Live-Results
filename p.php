<?php
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


echo '
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../results/tables.css" />
	<link rel="stylesheet" type="text/css" href="../results/general.css" />
	<link rel="stylesheet" type="text/css" href="../results/links.css" />
	<title>Live Results</title>
	<script type="text/JavaScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script> 
	<script type="text/JavaScript">
		$(document).ready(function(){
			$("select#goto").change(function(){
				window.location.hash= $("select$goto option:selected").attr("id").substring(1);
			});
		});
	</script>
</head>
<body>
<a href="./">Home</a><br>';
$comps=array('UniverCidadeOpen2009' => "Univercidade Open 2009", 'UNESPOpen2009' => 'UNESPOpen2009', 'BigCubesSummer2009'=> "Big Cubes Summer 2009",'SantiagoOpen2009'=>'Santiago Open 2009','UPennSpring2009' => "UPenn Spring 2009", 'FortLeeWinter2009' => "Fort Lee Winter 2009", 'NewarkWinter2009' => 'Newark Winter 2009',
			 'DaVinci2008' => 'Da Vinci Science Center 2008'); 
$events=array("333" => "Rubik's Cube","444" => "4x4x4 Cube","555" => "5x5x5 Cube", "222" => "2x2x2 Cube", "333bf" => "3x3x3 blindfolded","333oh" => "3x3x3 one-handed", "333wf" => "3x3x3 with feet","333fm" => "3x3x3 fewest moves","magic" => "Rubik's Magic","mmagic" => "Rubik's Master Magic","minx" => "Megaminx", "pyram" => "Pyraminx","sq1" => "Square-1","clock" => "Rubik's Clock","666" => "6x6x6 Cube","777" => "7x7x7 Cube","444bf" => "4x4x4 blindfolded", "555bf" => "5x5x5 blindfolded","333mbf" => "3x3x3 multi blind");
$rounds=array("c" => "Combined Final", "f" => "Final", "1" => "First Round", "2" => "Second Round", "0" => "Qualification Round", "e" => "Combined First round / B Final", "d" => "Combined First / Second round", "3" => "Semi final", "b" => "B Final");


$person=urldecode($_GET['p']);
$db_host = '';
$db_user = '';
$db_pwd = '';
$database = '';

if (!mysql_connect($db_host, $db_user, $db_pwd))
    die("Can't connect to database");

if (!mysql_select_db($database))
    die("Can't select database");

$result=mysql_query("select * from Results where personName='$person';");
$rows=mysql_num_rows($result);
if ($rows>0){
	echo "<h1>".str_replace('\\','',$person)."</h1><br>";
	echo "<label>Goto: </label><select id ='goto' class='drop'>";
	foreach($events as $eventId => $eventName){
		$result=mysql_query("select * from Results where eventId='$eventId' and personName='$person';");
		$rows=mysql_num_rows($result);
		if ($rows>0){
			echo "<option id='e$eventId'><a href=#$eventId>$eventName</a></option>";
		}
	}
	echo "</select>";
	foreach($events as $eventId => $eventName){
		$result=mysql_query("select * from Results where eventId='$eventId' and personName='$person';");
		$rows=mysql_num_rows($result);
		
		if ($rows>0){
			echo "
			<table width='100%' border='0' cellpadding='0' cellspacing='0' class='results'>
	
			<tr><td colspan='8'>&nbsp;<a name='$eventId'>&nbsp;</a></td></tr>
			
			<tr><td class='caption' colspan='8'><a class='e' href='#top'>$eventName</a></td></tr>
			
			<tr>
			<th >Competition</th>
			<th >Round</th>
			<th  class='r'>Place</th>
			<th  class='R'>Best</th>
			<th >&nbsp;</th>
			<th  class='R'>Average</th>
			<th >&nbsp;</th>
			<th  class='f'>Result&nbsp;Details</th>
			</tr>";
			
			$cnt=0;
			foreach($comps as $compId => $compName){
				$result=mysql_query("select * from Results where personName='$person' and eventId='$eventId' and competitionId='$compId' order by roundId desc;");
				$rows=mysql_num_rows($result);
				for ($i=0;$i<$rows;$i++){
					$row=mysql_fetch_row($result);
					echo '<tr';
					if ($cnt%2==1)
						echo ' class="e"';
					if ($i!=0)
						echo " style='color:#AAA'><td>&nbsp;</td>";
					else
						echo "><td><a class'c' href='./?c=$compId'>$compName</a></td>";
					$str=str_replace('Round','',$rounds[$row[6]]);
					echo '<td>'.$str.'</td><td class="r"><b>'.$row[0].'</b></td><td class="R">';
					if (strcmp($eventId, '333mbf')==0 and $row[13]>0){
						$x=$row[13];
						$t2=$x%100;
						$x=(int)($x/100);
						$t=$x%100000;
						$t1=(int)($x/100000);
						$solved=99+$t2-$t1;
						$tried=$t2+$solved;
						echo "$solved/$tried <span style='color:#999'>".toTime($t*100, '333mbf')."</span>";
					}
					elseif (strcmp($eventId, '333fm')==0 and $row[13]>0)
						echo $row[13];
					else{
						echo toTime($row[13]);
					}
					echo '</td><td>&nbsp;</td>';
					if (in_array($row[7], array('a','m')))
						echo "<td class='R'>".toTime($row[14])."</td>";	
					else
						echo "<td class='R'>&nbsp;</td>";
	
					$times=array($row[8],$row[9],$row[10],$row[11],$row[12]); $str='';
					foreach($times as $time){
						if (strcmp($eventId, '333mbf')==0 and $time>0){
							$x=$time;
							$t2=$x%100;
							$x=(int)($x/100);
							$t=$x%100000;
							$t1=(int)($x/100000);
							$solved=99+$t2-$t1;
							$tried=$t2+$solved;
							$str.= "$solved/$tried <span style='color:#999'>".toTime($t*100, '333mbf')."</span>".'&nbsp;';
						}
						elseif (strcmp($eventId, '333fm')==0 and $time>0)
							$str.= $time.'&nbsp;';
						elseif ($time!='0'){
							$str.=toTime($time).'&nbsp;';
						}
						else
							break;
					}
					echo "<td>&nbsp;</td><td class='f'>$str</td>";
					echo '</tr>';
					$cnt+=1;
				}
			}
		}
	}
}
else{
echo "<br>Unknown person: \"$person\"";	
}
?>