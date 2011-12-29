<?php
require_once('Db.php');
require_once('conf.php');
$db = Db::getInstance();
$charts = $db->listAllCharts();
echo "Available charts: <br/>";
?>
<table cellpadding="2px" cellspacing="5px" border="1">
<tr><th>Chart Id</th><th>Title</th><th>Type</th><th>x-axis</th><th>y-axis</th></tr>
<?php
foreach($charts as $chart){
  echo "<tr>";
  echo "<td>".$chart['id']."</td>";
  echo "<td>".$chart['title']."</td>";
  echo "<td>".$chart['type']."</td>";
  echo "<td>".$chart['xAxis']."</td>";
  echo "<td>".$chart['yAxis']."</td>";
  echo "<td><a href='modifyChart.php?id=".$chart['id']."'>Modify</a></td>";
  echo "<td><a href='viewChart.php?id=".$chart['id']."'>View</a></td>";
  echo "</tr>";
}
//echo "<pre>";print_r($charts);echo "</pre>";
?>
</table>
<div>
  <p>Add Chart:</p>
  <form action="addChart.php" method="post">
  ChartId :<input type="text" name="chartid">
  Title : <input type="text" name="title">
  Type of Chart : <input type="text" name="type"><br/>
  x-Axis : <input type="text" name="xAxis">
  y-Axis : <input type="text" name="yAxis"><br/>
  <input type="submit" value="Add">
</div>
