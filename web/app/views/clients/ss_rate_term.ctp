<?php echo $this->element("selectheader")?>

<div id="title">
        <h1><?php __('RateTable')?></h1>
</div>
<div class="container">
                
<ul class="tabs">
    <li class="active"><a href="">
    <img src="<?php echo $this->webroot?>images/menuIcon.gif" height="16" width="16"/> <?php __('RateTable')?></a></li>      
</ul>

<script type="text/javascript">var smartSearch = 2;</script>
<div class="panel">
<form id="smartSearch3"  action=""  method="get">
<input class="input in-hidden" name="type" value="0" id="type" type="hidden">
<input class="input in-hidden" name="types" value="10" id="types" type="hidden">
<input class="input in-hidden" name="noall" value="" id="noall" type="hidden"><table class="form"><tbody><tr>
    <td class="value"><input name="search"   onclick="this.value=''"  title="Search"  value="<?php if(isset($search)){echo $search;}else{ echo __('pleaseinputkey');}?>"  class="in-search get-focus input in-text" id="search-_q" type="text"/></td>
</tr></tbody></table>
</form>
</div>
<div id="container"><!-- DYNAREA -->
<div id="toppage"></div>
<table class="list">
<thead>
<tr>
 <td width="20%"><?php echo __('id',true);?></td>
    <td width="20%"><?php __('RateTable')?></td>
     <td width="20%"><?php __('code')?></td>
    <td width="20%"><?php __('Currency')?></td>
   

</tr>
</thead>
<tbody>


<?php 	$mydata =$p->getDataArray();	$loop = count($mydata); for ($i=0;$i<$loop;$i++) {?>
<tr class="s-active row-1" onclick='opener.ss_process("rate_term", {"id_rates":"<?php echo $mydata[$i][0]['rate_table_id']?>","id_rates_name":"<?php echo $mydata[$i][0]['table_name']?>"});winClose();' style="cursor: pointer;">
   
  
    <td align="right"><?php echo $mydata[$i][0]['rate_table_id']?></td>
    <td class="last" align="left"> <?php echo $mydata[$i][0]['table_name']?>    </td>
     <td class="last" align="left"> <?php echo $mydata[$i][0]['code_name']?>    </td>
      <td class="last" align="left"> <?php echo $mydata[$i][0]['currency_code']?>    </td>
       
</tr>

<?php }?>

</tbody>
</table>

			<div id="tmppage">
<?php echo $this->element('page');?>

</div>
</div>
</div>
<?php echo $this->element("selectfooter")?>