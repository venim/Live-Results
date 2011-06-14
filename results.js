function compchange(){
	$.post("index.php",{action:"type",comp:$("select#comp option:selected").attr("id")}, function(data){
		$("#type").html(data.html);
		$("#type option:first").attr("selected", "selected");
		$("#type").attr("size",data.cnt);
		typechange();
	}, "json");
}
function typechange(){
	$("#type").attr("disabled",'disabled');
	$("select#round").attr("disabled",'disabled');
	$.post("index.php",{action:"round",comp:$("select#comp option:selected").attr("id"),puzzle:$("select#type option:selected").attr("name")}, function(data){
		$("#round").html(data.html);
		$("select#round option:first").attr("selected", "selected");
		$("select#round").attr('size',data.cnt);
		autoupdate();
	}, "json");
	
}
function autoupdate(){
	
	$.post("index.php",
	{action:"getdb",comp:$("select#comp option:selected").attr("id"),puzzle:$("select#type option:selected").attr("name"), round:$("select#round option:selected").attr("value")}, 
	function(data){
		$("p#results").html(data);
		$("#type").removeAttr("disabled");
		$("select#round").removeAttr("disabled");
		});		
}
$(document).ready(function(){
	$("select#comp option:first").attr("selected", "selected");
	var s="";
	var urlquery=location.href.split("?");
	if(urlquery.length>1){
		var urlterms=urlquery[1].split("&")
		for( var i=0; i<urlterms.length; i++){
			var urllr=urlterms[i].split("=");
			if(urllr[0]=="c") {
				$("select#comp option:first").removeAttr("selected");
				$("select#comp option#"+urllr[1]).attr("selected","selected");
			}
		}
	}
	compchange();
	$("select#comp").click(compchange);
	$("select#type").change(typechange);
	$("select#round").change(autoupdate);
	setTimeout(autoupdate, 45 * 1000);
});
