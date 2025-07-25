<?php
    global $path, $settings, $session;
    
    $version = 3;
?>


<script type="text/javascript" src="<?php echo $path; ?>Modules/device/Views/device.js?v=<?php echo $version; ?>"></script>
<script type="text/javascript" src="<?php echo $path; ?>Lib/tablejs/table.js?v=<?php echo $version; ?>"></script>
<script type="text/javascript" src="<?php echo $path; ?>Lib/tablejs/custom-table-fields.js?v=<?php echo $version; ?>"></script>

<style>
#table input[type="text"] {
  width: 88%;
}
#table td:nth-of-type(1) { width:5%;}
#table th:nth-of-type(6), td:nth-of-type(6) { text-align: right; }
#table th:nth-of-type(7), td:nth-of-type(7) { text-align: right; }
#table th[fieldg="time"] { font-weight:normal; text-align: right; }
#table td:nth-of-type(8) { width:14px; text-align: center; }
#table td:nth-of-type(9) { width:14px; text-align: center; }
#table td:nth-of-type(10) { width:14px; text-align: center; }
#table td:nth-of-type(11) { width:14px; text-align: center; }
</style>

<div>
    <div id="api-help-header" style="float:right;"><a href="api"><?php echo tr('Devices Help'); ?></a></div>
    <div id="device-header"><h2><?php echo tr('Devices Location'); ?></h2></div>

    <div id="table"></div>

    <div id="device-none" class="hide">
        <div class="alert alert-block">
            <h4 class="alert-heading"><?php echo tr('No devices'); ?></h4><br>
            <p>
                <?php echo tr('Devices are used to configure and prepare the communication with different physical devices. Devices are grouped by Location for easy tracking when deploying at scale.'); ?>
                <br><br>
                <?php echo tr('A device configures and prepares inputs, feeds and other possible settings. e.g. representing different registers of defined metering units.'); ?>
                <br>
                <?php echo tr('Follow the next link as a guide for generating your request: '); ?><a href="api"><?php echo tr('Device API helper'); ?></a>
            </p>
        </div>
    </div>

    <div id="toolbar_bottom"><hr>
        <button id="device-new" class="btn btn-small" >&nbsp;<i class="icon-plus-sign" ></i>&nbsp;<?php echo tr('New device'); ?></button>
    </div>
    
    <div id="device-loader" class="ajax-loader"></div>
</div>

<?php require "Modules/device/Views/device_dialog.php"; ?>

<script>
  var devices = <?php echo json_encode($templates); ?>;
  
  // Extend table library field types
  for (z in customtablefields) table.fieldtypes[z] = customtablefields[z];
  table.element = "#table";
  table.groupprefix = "";
  table.groupby = 'description';
  table.groupfields = {
    'dummy-4':{'title':'', 'type':"blank"},
    'dummy-5':{'title':'', 'type':"blank"},
    'time':{'title':'<?php echo tr('Updated'); ?>', 'type':"group-updated"},
    'dummy-7':{'title':'', 'type':"blank"},
    'dummy-8':{'title':'', 'type':"blank"}
  }
  
  table.deletedata = false;
  table.fields = {
    //'id':{'type':"fixed"},
    'nodeid':{'title':'<?php echo tr("Node"); ?>','type':"fixed"},
    'name':{'title':'<?php echo tr("Name"); ?>','type':"fixed"},
    'description':{'title':'<?php echo tr('Location'); ?>','type':"fixed"},
    'typename':{'title':'<?php echo tr("Type"); ?>','type':"fixed"},
    'ip':{'title':'<?php echo tr('IP'); ?>','type':"fixed"},
    'devicekey':{'title':'<?php echo tr('Device access key'); ?>','type':"fixed"},
    'time':{'title':'<?php echo tr("Updated"); ?>', 'type':"updated"},
    // 'public':{'title':"<?php echo tr('tbd'); ?>", 'type':"icon", 'trueicon':"icon-globe", 'falseicon':"icon-lock"},
    // Actions
    'delete-action':{'title':'', 'type':"delete"},
    'config-action':{'title':'', 'type':"iconconfig", 'icon':'icon-wrench'}
  }

  update();

  function update(){
    var requestTime = (new Date()).getTime();
    $.ajax({ url: path+"device/list.json", dataType: 'json', async: true, success: function(data, textStatus, xhr) {
      table.timeServerLocalOffset = requestTime-(new Date(xhr.getResponseHeader('Date'))).getTime(); // Offset in ms from local to server time
      table.data = data;

      for (d in data) {
        if (data[d]['type'] !== null && data[d]['type'] != '' && devices[data[d]['type']]!=undefined) {
          data[d]['typename'] = devices[data[d]['type']].name;
        }
        else data[d]['typename'] = '';
        /*
        if (data[d]['own'] != true){ 
          data[d]['#READ_ONLY#'] = true;  // if the data field #READ_ONLY# is true, the fields type: edit, delete will be ommited from the table row and icon type will not update when clicked.
        }
        */
      }

      table.draw();
      if (table.data.length != 0) {
        $("#device-none").hide();
        $("#device-header").show();
        $("#api-help-header").show();
      } else {
        $("#device-none").show();
        $("#device-header").hide();
        $("#api-help-header").hide();
      }
      $("#device-loader").hide();
    }});
  }

  var updater;
  function updaterStart(func, interval)
  {
    clearInterval(updater);
    updater = null;
    if (interval > 0) updater = setInterval(func, interval);
  }
  updaterStart(update, 10000);

  $("#table").bind("onEdit", function(e){
    updaterStart(update, 0);
  });

  $("#table").bind("onSave", function(e,id,fields_to_update){
    device.set(id,fields_to_update);
  });

  $("#table").bind("onResume", function(e){
    updaterStart(update, 10000);
  });

  $("#table").bind("onDelete", function(e,id,row) {
    // Get device of clicked row
    var localDevice = device.get(id);
    device_dialog.loadDelete(localDevice, row);
  });

  $("#table").on('click', '.icon-wrench', function() {
    // Get device of clicked row
    var localDevice = table.data[$(this).attr('row')];
    device_dialog.loadConfig(devices, localDevice);
  });

  $("#device-new").on('click', function () {
    device_dialog.loadConfig(devices, null);
  });

  $("#device-reload").click(function() {
    $.ajax({ url: path+"device/template/reload.json", async: true, dataType: "json", success: function(result)
      {
        alert(result.message);
      }
    });
  });

</script>
