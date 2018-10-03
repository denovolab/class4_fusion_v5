<style type="text/css">
    select.in-select {margin-bottom: 0px;}
    .button_group {text-align: center;}
    .overflow_x{overflow-x:auto; margin-bottom: 10px;}
</style>

<ul class="breadcrumb">
    <li><?php __('You are here') ?></li>
    <li class="divider"><i class="icon-caret-right"></i></li>
    <li><?php __('File Format') ?></li>
</ul>

<div class="heading-buttons">
    <h4 class="heading"><?php __('File Format') ?></h4>

    <div class="clearfix"></div>
</div>
<div class="separator bottom"></div>
<div class="buttons pull-right newpadding">
    <a  class="btn btn-icon btn-inverse back_button glyphicons circle_arrow_left" href="<?php echo $this->webroot ?>clientrates/import/<?php echo base64_encode($rate_table_id); ?>"><i></i>
        <?php __('Back')?>
    </a>
</div>
<div class="clearfix"></div>
<div class="innerLR">
    <div class="widget widget-tabs widget-body-white">
        <div class="widget-body">
            <form  id="myform" method="post" action="<?php echo $this->webroot ?>clientrates/import/<?php echo $rate_table_id; ?>">
                <input type="hidden" name="default_min_time" value="<?php echo $defaultMinTime ?>">
                <input type="hidden" name="default_interval" value="<?php echo $defaultInterval ?>">
                <table class="center">
                    <tr>
                        <td align="right">
                            <label><?php __('Effective Date Format') ?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <select name="date_format">
                                <option value="mm/dd/yyyy" <?php
                                if (!strcmp("mm/dd/yyyy", $date_format))
                                {
                                ?>selected="selected"<?php } ?>>mm/dd/yyyy</option>
                                <option value="yyyy-mm-dd" <?php
                                if (!strcmp("yyyy-mm-dd", $date_format))
                                {
                                ?>selected="selected"<?php } ?>>yyyy-mm-dd</option>
                                <option value="dd-mm-yyyy" <?php
                                if (!strcmp("dd-mm-yyyy", $date_format))
                                {
                                ?>selected="selected"<?php } ?>>dd-mm-yyyy</option>
                                <option value="dd/mm/yyyy" <?php
                                if (!strcmp("dd/mm/yyyy", $date_format))
                                {
                                ?>selected="selected"<?php } ?>>dd/mm/yyyy</option>
                                <option value="yyyy/mm/dd" <?php
                                if (!strcmp("yyyy/mm/dd", $date_format))
                                {
                                ?>selected="selected"<?php } ?>>yyyy/mm/dd</option>
                            </select>
                        </td>
                        <td  align="right">
                            <label style="margin-left:15px"><?php __('Start from') ?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <input type="text" name="start_from" id="start_from" value=1 >
                        </td>
                    </tr>
                    <tr>
                        <td class="align_right padding-r10">
                            <label><?php __('Append Prefix')?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <input type="checkbox" name="append_prefix" id="append_prefix" <?php if($append_prefix): ?>checked="checked"<?php endif; ?> />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="text" name="append_prefix_value" value="<?php echo $append_prefix_value; ?>" id="append_prefix_value" maxlength="10" class="width80" style="display: none;" />
                        </td>
                    </tr>
                    <!--
                    <tr id="set_default_date" <?php if (isset($effective_date_flg))
                    {
                        ?>style="display:none;"<?php } ?>>
                        <td align="right">
                            <label><?php __('Set Default Effective Date') ?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <input type="hidden" name="is_effective_date" id="is_effective_date" value="<?php echo isset($effective_date_flg) ? '' : 1; ?>" />
                            <input type="text" class="validate[required] in-text" name="effetive_date" value="<?php //echo date("Y-m-d 00:00:00");   ?>" onfocus="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'});" />
                        </td>
                    </tr>
                    <tr id="set_default_min_time" <?php if (isset($min_time_flg))
                    {
                        ?>style="display:none;"<?php } ?>>
                        <td align="right">
                            <label><?php __('Set Default Min Time') ?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <input type="hidden" name="is_min_time" id="is_min_time" value="<?php echo isset($min_time_flg) ? '' : 1; ?>" />
                            <input type="text" class="validate[required,custom[integer]] in-text" name="min_time" value="" />
                        </td>
                    </tr>
                    <tr id="set_default_interval" <?php if (isset($interval_flg))
                    {
                        ?>style="display:none;"<?php } ?>>
                        <td align="right">
                            <label><?php __('Set Default Interval') ?>:</label>
                        </td>
                        <td align="left" style="padding-left:10px;">
                            <input type="hidden" name="is_interval" id="is_interval" value="<?php echo isset($interval_flg) ? '' : 1; ?>" />
                            <input type="text" class="validate[required,custom[integer]] in-text" name="interval" value="" />
                        </td>
                    </tr>
                    -->
                </table>
                <div class="overflow_x">
                    <input type="hidden" class="default_finished" />
                    <input type="hidden" name="date_check" id="date_check" />
                    <input type="hidden" name="abspath" value="<?php echo $abspath; ?>">
                    <input type="hidden" name="is_ocn_lata" value="<?php echo $is_ocn_lata; ?>">
                    <input type="hidden" name="rates_file_cmd" value="<?php echo $rates_file_cmd; ?>">
                    <input type="hidden" name="with_header" value="<?php echo $with_header; ?>">
                    <input type="hidden" name="code_name_match" value="<?php echo $code_name_match; ?>">
                    <input type="hidden" name="send_error_email_to" value="<?php echo $send_error_email_to; ?>" />
                    <input type="hidden" name="zero_rate" />
                    <input type="hidden" name="code_deck_id" value="<?php echo $code_deck_id; ?>"  />
                    <input type="hidden" name="import_file_name" value="<?php echo $import_file_name; ?>"  />
                    <input type="hidden" name="sample_do" value="<?php echo $sample_do; ?>"  />
                    <input type="hidden" name="exist_end_date" value="<?php echo $exist_end_date; ?>"  />
                    <input type="hidden" name="all_end_date" value="<?php echo $all_end_date; ?>"  />
                    <table  class="list footable table table-striped tableTools table-bordered  table-white table-primary">
                        <thead>
                        <?php
                        $headers = array_shift($table);
                        foreach ($headers as $header):
                            ?>
                            <th>
                                <select class="columns" name="columns[]">
                                    <?php
                                    foreach ($columns as $column):
                                        $header = strtolower($header);
                                        $header = preg_replace('/[\s ]/','_',$header);
                                        ?>
                                        <option value="<?php echo $column; ?>" <?php if ($header == $column) echo 'selected="selected"'; ?>><?php echo $column; ?></option>
                                    <?php endforeach; ?>
                                    <option value="" <?php if ($header == '' || ($header != '' && !in_array($header, $columns))) echo 'selected="selected"'; ?>><?php __('ignore') ?></option>
                                    <option value="unkown" ><?php __('unkown') ?></option>
                                </select>
                            </th>
                            <?php
                        endforeach;
                        ?>
                        </thead>


                        <tbody id="data_tbody">
                        <?php foreach ($table as $row): ?>
                            <tr>
                                <?php foreach ($row as $field): ?>
                                    <td><?php echo $field; ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <a href="#myModal_rateImportDefault" data-toggle="modal" id="rateImportDefault_btn" class="hide"></a>
                <div id="myModal_rateImportDefault" class="modal hide">
                    <div class="modal-header">
                        <button data-dismiss="modal" class="close" type="button">&times;</button>
                        <h3><?php __('Define Missing Information'); ?></h3>
                    </div>
                    <div class="modal-body">
                        <table>
                            <tr id="set_default_date" <?php if (isset($effective_date_flg))
                            {
                            ?>style="display:none;"<?php } ?>>
                                <td align="right">
                                    <label><?php __('Set Default Effective Date') ?>:</label>
                                </td>
                                <td align="left" style="padding-left:10px;">
                                    <input type="hidden" name="is_effective_date" id="is_effective_date" value="<?php echo isset($effective_date_flg) ? '' : 1; ?>" />
                                    <input type="text" class="validate[required] in-text" name="effetive_date" value="<?php echo date("Y-m-d 00:00:00");   ?>" onfocus="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'});" />
                                </td>
                            </tr>
                        </table>
                        <div class="separator interval_mintime_show" <?php if (isset($interval_flg) && isset($min_time_flg)){ ?>style="display:none;"<?php } ?>>
                            <a id="add_interval_mintime" class="btn btn-primary btn-icon glyphicons circle_plus" href="javascript:void(0)">
                                <i></i>
                                <?php __('ADD Interval and Min Time'); ?>
                            </a>
                        </div>
                        <table class="footable table table-striped dynamicTable tableTools table-bordered  table-white table-primary default footable-loaded interval_mintime_show"
                               <?php if (isset($interval_flg) && isset($min_time_flg)){ ?>style="display:none;"<?php } ?>>
                            <input type="hidden" name="is_interval" id="is_interval" value="<?php echo isset($interval_flg) ? '' : 1; ?>" />
                            <input type="hidden" name="is_min_time" id="is_min_time" value="<?php echo isset($min_time_flg) ? '' : 1; ?>" />
                            <thead>
                            <tr>
                                <th><?php __('Code'); ?></th>
                                <th><?php __('Interval'); ?>(s)</th>
                                <th><?php __('Min Time'); ?>(s)</th>
                                <th><?php __('Action'); ?></th>
                            </tr>
                            </thead>
                            <tbody id="interval_mintime_table">
                            <tr class="interval_mintime_table_tr">
                                <td><input type="text" name="interval_mintime[code][]"  class="validate[required] width80 code_tr" /></td>
                                <td><input type="text" name="interval_mintime[interval][]" class="validate[required,custom[onlyNumberSp]] width80"  /></td>
                                <td><input type="text" name="interval_mintime[min_time][]" class="validate[required,custom[onlyNumberSp]] width80"  /></td>
                                <td>
                                    <a title="Remove" href="javascript:void(0)" onclick="$(this).closest('tr').remove();" style="margin-left: 20px;">
                                        <i class="icon-remove"></i>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                            <tbody>
                            <tr>
                                <td>
                                    <?php __('Default'); ?>
                                </td>
                                <td>
                                    <input type="text" name="default_interval" value="" class="validate[required,custom[onlyNumberSp]] width80" value="<?php echo $defaultInterval; ?>" />
                                </td>
                                <td>
                                    <input type="text" name="default_min_time" value="" class="validate[required,custom[onlyNumberSp]] width80" value="<?php echo $defaultMinTime; ?>" />
                                </td>
                                <td></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-primary form_sub" value="<?php __('Submit'); ?>">
                        <a href="javascript:void(0)" data-dismiss="modal" class="btn btn-default"><?php __('Close'); ?></a>
                    </div>

                </div>

            </form>
            <div class="button_group">
                <input type="button" id="form_submit" value="<?php __('Submit') ?>" class="input in-submit btn btn-primary">
                <button id="show_errors" class="hidden btn btn-primary" style="width: 115px;" ><?php __('Show Errors')?></button>
                <button id="reupload" class="hidden btn btn-primary" style="width: 115px;" ><?php __('Reupload')?></button>
            </div>
        </div>
    </div>
</div>


<div id="myModal_errors" class="modal hide" style=" width: 1000px; margin-left: -500px;">
    <div class="modal-header">
        <button data-dismiss="modal" class="close" type="button">&times;</button>
        <h3><?php __('Errors'); ?></h3>
    </div>
    <div class="separator"></div>
    <div class="widget-body" style="padding:0 15px 10px 15px ;width: 970px; overflow: scroll; max-height:400px ;height: auto;">
       <table style="" class="hidden footable table table-striped dynamicTable tableTools table-bordered  table-white table-primary">

           <thead>

           <tr>
               <th><?php __('Duplicated Code'); ?></th>
               <th><?php __('Rows'); ?></th>
           </tr>

           </thead>
           <tbody class = "errors_body">

           </tbody>
       </table>

       <div class="wrong_rows hidden">
       <div class="separator"></div>
       </div>
   </div>

    <div class="modal-footer">
        <a href="javascript:void(0)" id="support_close" data-dismiss="modal" class="btn btn-default"><?php __('Close'); ?></a>
    </div>

</div>

<script type="text/javascript">
    function append_prefix_show()
    {
        var checked = $("#append_prefix").is(":checked");
        if (checked){
            $("#append_prefix_value").show();
        }else{
            $("#append_prefix_value").hide();
        }
    }
    $(function () {
        $('#start_from').on('blur', function(){
             let start_from = $(this).val();
             let abspath = '<?php echo $abspath; ?>';
             $.ajax({
                url:'<?php echo $this->webroot ?>clientrates/change_header_start/'+start_from,
                type : 'POST',
                dataType:'json',
                data : {'start_from': start_from, 'abspath': abspath},
                success:function(response){
                    if(response.status && response.data.length){
                        let data_tbody = "";
                        $.each(response.data, function(number, row){
                            data_tbody += "<tr>";
                               $.each(row, function(index, value){
                                   data_tbody += "<td>" +value+ "</td>";
                               });
                            data_tbody += "</tr>";
                        });
                        $('#data_tbody').html(data_tbody);
                    }
                }
            });
        });

        $('input[name="start_from"]').keyup(function(e)
                                        {
          if (/\D/g.test(this.value))
          {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
          }
        });

        append_prefix_show();
        $("input[name='append_prefix']").click(function(){
            append_prefix_show();
        });

        var interval_mintime_table_tr = jQuery('.interval_mintime_table_tr').eq(0).remove();
        jQuery('a[id=add_interval_mintime]').click(function() {
            interval_mintime_table_tr.clone(true).prependTo('#interval_mintime_table');
        });

        $("#myModal_rateImportDefault").find('.form_sub').click(function(){
            var a = $("#myform").validationEngine('validate');
            if (a == false){
                return false;
            }
            $(".default_finished").val(1);
            $("#myform").submit();
        });

        $(".columns").change(function () {
            $("#set_default_date").show();
            $("#set_default_interval").show();
            $("#set_default_min_time").show();
            $("#is_min_time").val('1');
            $("#is_interval").val('1');
            $("#is_effective_date").val('1');
            $("option").each(function () {
                var selected_value = $(this).val();
                var selected = $(this).attr('selected');
                if (selected && selected_value == 'effective_date')
                {
                    $("#is_effective_date").val(' ');
                    $("#set_default_date").hide();
                }
                if (selected && selected_value == 'interval')
                {
                    $("#is_interval").val(' ');
                    $("#set_default_interval").hide();
                }
                if (selected && selected_value == 'min_time')
                {
                    $("#is_min_time").val(' ');
                    $("#set_default_min_time").hide();
                }
            });
        });


        var $myform = $('#myform');
        var $columns = $('.columns');
        $myform.validationEngine();
        $("#form_submit").click(function () {
            let start_from = $('#start_from').val();
            $("select").each(function () {
                var $select = $(this);
                if ($(this).children("option:selected").val() == 'effective_date')
                {
                    var $effe_date = $select;
                    var $index = $effe_date.parent().index();
                    var i = 0;
                    $("#data_tbody tr").each(function () {
                        if (i == 5)
                        {
                            return false;
                        }
                        var datetime = $(this).children().eq($index).html();
                        if ($(this).children().size() <= 1)
                            return true;
                        if ($("#date_check").val())
                        {
                            $("#date_check").val($("#date_check").val() + ',' + datetime);
                        } else {
                            $("#date_check").val(datetime);
                        }

                        <?php
                        $date_format = str_replace('mm', 'm', $date_format);
                        $date_format = str_replace('yyyy', 'Y', $date_format);
                        $php_date_format = str_replace('dd', 'd', $date_format);
                        ?>


                        i++;
                    });
                }
            });
            var flag = true;
            var effective_selected = false;
            var interval = false;
            var min_time = false;
            var rate_selected = false;
            var code_selected = false;
            var code_col_number = 0;
            $columns.each(function (index) {
                var val = $(this).val();
                if (val == 'unkown')
                {
                    $.jGrowl("There is unkown field!", {theme: 'jmsg-error'});
                    flag = false;
                    return;
                }
                if (val == 'effective_date'){
                    effective_selected = true;
                }

                if (val == 'rate'){
                    rate_selected = true;
                }
                if (val == 'interval'){
                    interval = true;
                }
                if (val == 'min_time'){
                    min_time = true;
                }
                if (val == 'code'){
                    code_col_number = index;
                    code_selected = true;
                }
            });
            $("input[name=zero_rate]").val(' ');
            if (!rate_selected)
            {
                $("input[name=zero_rate]").val('1');
//                jGrowl_to_notyfy("You have not selected the field of rate!",{theme:'jmsg-error'});
//                return false;
            }
            if (!code_selected)
            {
                jGrowl_to_notyfy("You have not selected the field of code!",{theme:'jmsg-error'});
                return false;
            }
            var default_finished = $(".default_finished").val();
            if (!effective_selected && $('input[name=is_ocn_lata]').val() != '1' && !default_finished)
            {
                $("#rateImportDefault_btn").click();
                if (!interval || !min_time){
                    $(".interval_mintime_show").show();
                }else{
                    $(".interval_mintime_show").hide();
                }
                return false;
            }
            if (!default_finished && (!interval || !min_time))
            {
                $("#rateImportDefault_btn").click();
                $(".interval_mintime_show").show();
                return false;
            }

             let abspath = '<?php echo $abspath; ?>';
             $.ajax({
                url:'<?php echo $this->webroot ?>clientrates/detectErrors',
                type : 'POST',
                dataType:'json',
                async: false,
                data : {'abspath': abspath, 'code_col_number' : code_col_number, 'start_from': start_from},
                success:function(response){
                    if(typeof response.status !=='undefined' && !response.status){
                        let duplicated = response.errors.duplicated;
                        let wrong_rows = response.errors.wrong_rows;
                        let modal = $('#myModal_errors');
                        if(Object.keys(duplicated).length){
                            let tbody = "";
                            $.each(duplicated, function(code, rows){
                                tbody += "<tr><td>" + code + "</td><td>" + rows.join(', ') +"</td></tr>";
                            });
                            modal.find('.errors_body').html(tbody);
                            modal.find('table').removeClass('hidden');
                        }
                        if(wrong_rows.length){
                            modal.find('.wrong_rows').html('<span style="color:red;">Incorrect Lines: </span>' + wrong_rows.join(', ')).removeClass('hidden');
                        }
                        modal.modal('show');
                        $('#show_errors').removeClass('hidden');
                        $('#reupload').removeClass('hidden');
                        $('#show_errors').on('click', function(e){
                            e.preventDefault();
                            modal.modal('show');
                        })
                        $('#reupload').on('click', function(e){
                            location.href ='<?php echo $this->webroot ?>clientrates/import/<?php echo $rate_table_id; ?>';
                        })
                        flag = false;
                    }else{
                        $('#show_errors').addClass('hidden');
                        $('#reupload').addClass('hidden');
                        flag = true;
                    }

                }
            });
            if(!flag){
                return false;
            }
            $("#myform").submit();
        });
    });
</script>