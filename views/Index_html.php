<?php
/* ----------------------------------------------------------------------
 * ImportMapping/views/index_html.php : 
 * ----------------------------------------------------------------------
 * Israel Ministry of Sports and Culture 
 * 
 * Plugin for CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * For more information about Israel Ministry of Sports and Culture visit:
 * https://www.gov.il/en/Departments/ministry_of_culture_and_sport
 *
 * For more information about CollectiveAccess visit:
 * http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license.
 *
 * This plugin for CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details. 
 * ----------------------------------------------------------------------
 */
?>
<script>

    $(document).ready(function(){
        $("#inputBtn").click(function(){
            $("importMapping").hide();
        });      
    });
</script>


<div class="jumbotron" id="importMapping">
        <div class="form-group">
            <label for="Module"><?php print  _p('Type')?></label>
            <select class="custom-select form-control settings-select" name="Module" id="Module" required>
                <option></option>
                <option value="ca_objects"><?php print  _p('objects')?></option>
                <option value="ca_object_lots"><?php print  _p('lots')?></option>
                <option value="ca_entities"><?php print  _p('entities')?></option>
                <option value="ca_collections"><?php print  _p('collections')?></option>
                <option value="ca_loans"><?php print  _p('loans')?></option>
                <option value="ca_places"><?php print  _p('places')?></option>
                <option value="ca_movements"><?php print  _p('Movements')?></option>
                <option value="ca_storage_locations"><?php print  _p('storage locations')?></option>
                <option value="ca_list_items"><?php print  _p('list items')?></option>
                <option value="ca_occurrences"><?php print  _p('occurrences')?></option>
            </select>
            <div class="invalid-feedback">
                יש להזין סוג
             </div>
        </div>
        <br>
        <div class="form-group">
            <div class="custom-file">
                <label  for="excelfile"><?php print  _p('file')?></label>
                <input type="file" class="form-control-file" name="excelfile" id="excelfile" required>
            </div>
        </div>
        <br>
        <input type="button" class="btn btn-primary floatbylang" value="הבא >" name="LoadExcel" id="botLoadExcel" data-screen-name="importMapping">
    </form>
</div>   

<!--2-->

<div class="jumbotron" id="importMappingFirst">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label  for="importCode"><?php _p('Code'); ?></label>
            <input type="text" name="importCode" id="importCode" class="form-control ">
            <div class="invalid-feedback">
                יש להזין קוד      
            </div>
        </div> 
        <div class="form-group">
            <label  for="importName"><?php _p('Name'); ?></label>
            <input type="text" name="importName" id="importName" class="form-control ">  
            <div class="invalid-feedback">
                יש להזין שם  
            </div>    
        </div>
        <br>
        <div class="form-group">
            <label for="locallanguage"><?php print _p('Locale')?></label>
            <select class="custom-select form-control settings-select" name="importlanguage" id="importlanguage" required class="custom-select form-control settings-select">
                <option value="he_IL">Hebrew</option>
                <option value="en_US">English</option>
                <option value="ar_MA">Arabic</option>
            </select>
            <div class="invalid-feedback">
                יש להזין שפה   
            </div>             
        </div> 
        <input type="button" class="btn btn-secondary" value="הוספת הגדרה" name="LoadExcel" id="botAddDefinition" data-screen-name="addDefinition"> 
        <input type="button" class="btn btn-primary floatbylang" value="הבא >" name="LoadExcel" id="btnLoadExcelProperties" data-screen-name="importMappingFirst">
    </form>
</div>

<!--3-->

<div class="jumbotron p-3" id="importMappingSecond">
    <form method="post" enctype="multipart/form-data">
    <div class = "row rowExcel">
        <!--<div class = "col-6">
            
        </div> 
        <div class = "col-6">
          
        </div> -->
    </div>  
    <div class = "row rowExcel">
    </div>
    <div class = "row rowExcel1">
        <!--<div class = "col-6">
            
        </div> 
        <div class = "col-4">
           
        </div>  -->
        <div class="col-2 text-left">
          <input type="button" class="btn btn-primary" value="שמירה" name="LoadExcel" id="btnSave" data-screen-name="importMappingFirst">  
        </div>
        
    </div>
        
        <!--<input type="button" class="btn btn-outline-primary" value="הבא1 >" name="LoadExcel" id="botLoadExcel1" data-screen-name="importMappingSecond">-->
    </form>
</div>



<div class="pb-5" id="fieldsMapping">
    
</div> 

<script src="<?php print __CA_URL_ROOT__?>/app/plugins/ImportMapping/assets/mapping.js">

</script>

<script>
    $("#importMapping").show();
    $("#importMappingFirst").hide();
    $("#importMappingSecond").hide();

</script>


        
