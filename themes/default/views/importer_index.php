
<?php
/* ----------------------------------------------------------------------
 * app/plugins/flexImport/views/settings_html.php : 
 * ----------------------------------------------------------------------
 */	

$data = $this->getVar('data');

//print_r($data);
?>
<div class="container">
    <div class="jumbotron">
        <div class="row">
            <h3>תבניות ייבוא</h3>
        </div>
        <div class="row">
            <div class="col-4">
                <label for="tablefilter">חיפוש תבנית</label>
                <input type="text" id='tablefilter' mane='tablefilter' placeholder="תבנית, סוג.."></input>
            </div>
            <div class="col-5"></div>
            <div class="col-3">
                <a class="btn btn-primary" href="/mana/index.php/importer/importer/edit" name="AddTamplet" id="btnAddTamplet" data-screen-name="importMappingFirst"> +ייבוא חדש מאקסל</a>
            </div>
        </div>
    </div>
        <ul class="list-group list-group-flush pt-4" id="import_template">   
        <?php
         //print_r($data);

            foreach ($data as $name => $item) {
        ?>
            <li class="list-group-item pt-4">

                <div class="row">
                    <h4 class="col-12" id="name_templat"><?php print $item["label"]; ?></h4>
                <div>
                <div class="row pb-4">
                    <div class="col-8 pt-1" >Updated: <?php print caGetLocalizedDate($item['last_modified_on'], array('dateFormat' => 'delimited')); ?></div>
                    <!-- <div class="col-1 pt-1" ><a class="btn-outline-danger" href="/mana/index.php/batch/MetadataImport/Delete/importer_id/<?php echo $item['importer_id'];?>"><i class="fa fa-trash-o fa-lg" aria-hidden="true" ></i></a></div> -->
                    <div class="col-1 pt-1" ><a class="btn-outline-danger" href="/mana/index.php/importer/importer/Delete/importer_id/<?php echo $item['importer_id'];?>"><i class="fa fa-trash-o fa-lg" aria-hidden="true" ></i></a></div>
                    <div class="col-2 " ><a href="/mana/index.php/batch/MetadataImport/Run/importer_id/<?php echo $item['importer_id'];?>" id="btnType" class="<?php if ( $item["table_num"] == "57" ) { echo "btn btn-outline-primary"; } 
                                                                                                                                                                else if ( $item["table_num"] == "51" ) { echo "btn btn-outline-secondary"; } 
                                                                                                                                                                else if ( $item["table_num"] == "20" ) { echo "btn btn-outline-success"; } 
                                                                                                                                                                else if ( $item["table_num"] == "67" ) { echo "btn btn-outline-danger"; } 
                                                                                                                                                                else if ( $item["table_num"] == "33" ) { echo "btn btn-outline-dark"; } 
                                                                                                                                                                else if ( $item["table_num"] == "13" ) { echo "btn btn-outline-info"; } 
                                                                                                                                                                else if ( $item["table_num"] == "133" ) { echo "btn btn-outline-dark"; } 
                                                                                                                                                                else if ( $item["table_num"] == "72" ) { echo "btn btn-outline-dark"; } 
                                                                                                                                                                else if ( $item["table_num"] == "137" ) { echo "btn btn-outline-dark"; } 
                                                                                                                                                                else if ( $item["table_num"] == "89" ) { echo "btn btn-outline-warning"; } 
                                                                    ?>" id="import_type"><?php print $item["importer_type"];?>               
                                        </a ></div>
                    <div class="col-1 pt-1"  ><a class="btn-outline-secondary" href="/mana/index.php/batch/MetadataImport/Run/importer_id/<?php echo $item['importer_id'];?>"><i class="fa fa-chevron-left" aria-hidden="true"></i></a></div>
                </div>

            </li>
 

        <?php 
        }
        ?>
        </ul>
</div>

</div>   


 
<!-- 
<script src="<?php print __CA_URL_ROOT__?>/app/plugins/ImportMapping/assets/mapping.js">
</script>
<script src="<?php print __CA_URL_ROOT__?>/app/plugins/ImportMapping/assets/bootstrap.bundle.min.js"></script>
 -->


<script type="text/javascript">
$(document).ready(function(){
    $('#tablefilter').val('');
    
    var $rows = $("#import_template li");

    $('#tablefilter').keyup( function(e) {
      Search();                                              
       
    });
    
    
    function Search(){
            if($('#tablefilter').val() != '') {
                            var value = $.trim($('#tablefilter').val()).toLowerCase();
                            var r =  $rows.show().filter(function() {
                          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)                   
                            });
                           
            }
            else{$rows.show();}                    

               

    }

              
});    
</script>





        
