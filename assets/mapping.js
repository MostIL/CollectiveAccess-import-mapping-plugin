/* ----------------------------------------------------------------------
 * ImportMapping/assets/mapping.js
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

    function main()
    {
        var identify;               // json object with the values we need to map
        var objTitle;               // Original object with values
        var copyObjTitle = [];      // Copy of the objTitle with one more field "id"
        var minimizeDropListStr;
        var settings = {};          // Hold the values in the screen settings except the 'choose file' field 
        var saveMappingUrl = "../ImportMapping/SaveImportMapping";      // Address of server for mapping & content data. 
        var arrByName = [];             // Associative array - every cell get his name by his id name
        var arrDefault = [];            // Default Fields on load  - רשימת שדות
        var innerTempJsonObj = {};     
        var optionsObjFactory = {};     // Associative array - key is "Type of Option", the value is - type of control we need to create and all his properties like: "lable" - "he" or "en", "type" - "chackbox", "values"(to initialize the checkbox) - [0,1]

        var location = [];
        var lastPosition = 0;

        // Just after this function will ended the other functions will run.
        function preload()
        {
            var arrPanels = ["importMapping", "importMappingFirst"];         // Holds the panesl/classes id we should go throughc on them and get the input values 

            $("#fieldsMapping").append('<i class="fa fa-spinner fa-spin" style="font-size:48px"></i>');
            
            var formDataModule = new FormData();
            var formDataFile = new FormData();
            formDataFile.append('excelfile', $('#excelfile')[0].files[0]);
            formDataModule.append('Module', $('#Module')[0].value);
            CreateOptionsObjFactory();

            for(var index = 0; index < arrPanels.length; index++)
            {
                var tempSet = $('#' + arrPanels[index] + ' :input');//$('#importMapping :input');
                for( i= 0; i < tempSet.length; i++)         // Set the settings object
                {
                    if( tempSet[i].type == "text" || tempSet[i].tagName == "SELECT") 
                    {
                        var strNoImport = (tempSet[i].name).replace("import",""); // Check with Avihay
                        settings[strNoImport] = tempSet[i].value;
                    }
                }
            }
    
            $.when(SendJsonToServer("../ImportMapping/ImportMappingModel", formDataModule), SendJsonToServer("../ImportMapping/ImportMappingFieldsList", formDataFile)).done( function( dataMapp, dataD)
            {
                identify = dataMapp[0];
                objTitle = dataD[0];
                CreateFirstTimeCustomObj();
                CreateDynamicDropList(copyObjTitle, "");
                createArrByName();    

                // Create the required(must) fields in the editList. 
                identify.forEach((element,i)=>{
                    if (element != undefined){        // Change the options to settings and require to somthing else.
                        if (element.require != undefined){
                            createEditedList(element.element_code, true, i);
                        }
                    }
                })

                createGui();
                ManageBackGroundcolorContainer();
            });
            
        }
    
        // Create associative array 'optionsObjFactory' with his types and his defaults values. 
        function CreateOptionsObjFactory()
        {
                optionsObjFactory = {
                "skipIfEmpty":{
                    "lable":{
                        "en":"skip If Empty",
                        "he":"אם ריק לדלג"
                    },
                    "type":"checkbox",
                    "values":[0,1]
                    },
                "skipGroupIfEmpty":{
                    "lable":{
                        "en":"skip On Group If Empty",
                        "he":"אם ריק לדלג על הקבוצה"
                    },
                    "type":"checkbox",
                    "values":[0,1]
                    },
                "matchOn":{
                    "lable":{
                        "en":"match by",
                        "he":"ביצוע התאמה לפי"
                    },
                    "type":"dropdown",
                    "values":[{name:"", id:""},{name:"מספר מזהה", id:"idno"},{name:"כותרת", id:"label"}, {name:"מספר מזהה וכותרת", id:["label","Idno"]}]
                    },
                "skipRowIfEmpty":{
                        "lable":{
                            "en":"skip Row If Empty",
                            "he":"אם ריק דלג על השורה"
                        },
                        "type":"chackbox",
                        "values":[0,1]
                        },   
                "delimiter":{
                            "lable":{
                                "en":"delimiter",
                                "he":"חלוקה"
                            },
                            "type":"textbox",
                            "values":[":",";"]
                            }
                        };
        }


        // Create temp object list and add new property "id".
        // We will work on this object and not on the original.
        function CreateFirstTimeCustomObj()
        {
            for( i=0 ; i< objTitle.length; i++)
            {
                tempObj = {}
                tempObj.id = "";
                tempObj.colNum = objTitle[i].colNum;
                tempObj.title = objTitle[i].title;
                copyObjTitle[i] = tempObj;
            }
        }
    
        // Get the selected control id and the selected value in the dropDownList
        // Return object with the mapping data. 
        function DropdownlistFirstOption(idVal, selectedValue, selectedTitle) // Full list managment
        {
            // Update the dropDownList with the id selected values
            for( i= 0; i < copyObjTitle.length; i++)
                {
                    // Delete the last selection for this id
                    if(copyObjTitle[i].id == idVal)
                    {
                        copyObjTitle[i].id ="";
                    }
    
                    // Set the new selection for the current id
                    if(copyObjTitle[i].colNum == selectedValue)
                    {
                        copyObjTitle[i].id = idVal;
                    }
                }           
        }
    
    
        /* Get the selected value in the deopDownList and the control id
           Create dynamic dropDownList.
           Locate and return the index position of the lest selected value in the new dynamic dropDownList.
        */
        function CreateDynamicDropList(selectedTitle, currentValue)
        {
            var tempObjTitle = [];
            var indexArrOfTheLastSelect;        // The position of the last selected value in the new dynamic dropDownList(Object).
            minimizeDropListStr = "";
    
    
            for( i= 0; i < copyObjTitle.length; i++)
            {
                if(copyObjTitle[i].id == "" || (copyObjTitle[i].colNum == currentValue))
                {
                    tempObj = {}
                    tempObj.id = "";
                    tempObj.colNum = copyObjTitle[i].colNum;
                    tempObj.title = copyObjTitle[i].title;
                    tempObjTitle[i] = tempObj;
                }
            }
    
            // Delete the holes in the array after we delete rows.
            tempObjTitle = tempObjTitle.filter(function( element)
                        {
                            return element !== undefined;
                        });
                
            minimizeDropListStr +='<option value="0"></option>';
                
            for(i = 0 ; i < tempObjTitle.length; i++)
            {   
                var tempTitle = (tempObjTitle[i].title != null ? tempObjTitle[i].title : "");
                
                minimizeDropListStr +='<option value="' + tempObjTitle[i].colNum +'">'+tempObjTitle[i].colNum +" : " + tempTitle + '</option>';
                if(tempObjTitle[i].colNum == currentValue)
                {
                    indexArrOfTheLastSelect = i+1;
                }
            }
    
            return indexArrOfTheLastSelect; 
        }


        function fieldHtml(element, index)
        {
            var fieldsRes;
            if (element.fields != undefined && element.fields.length > 0)
            {
                let fields = "";
                let elemGroup = '<h5 class="pb-2" data-element-code="'+ element.element_code + "Index" + index +'">' +  element.title[0] + '</h5>' +
                                '<div class = "rowExcelGroup" data-element-code="'+ element.element_code + '">';

                fields += elemGroup;
                element.fields.forEach(field => fields+= fieldHtml(field, index));
                fields += '</div>';
                fieldsRes = fields;
                //return fields;
            }else{
                let field = fieldTemplate(element, index);
                fieldsRes = field;
                //return field;
            }

            return fieldsRes;
        }

        //----------------

        // toUpdate -> update the object or get data from it.
        function GetObjDataForOptions(obj, toUpdate, ctrRowName)
        {
            var isConst = false;
            var id;
            var index;
            var elementCode;
            var res;

            if(toUpdate)
            {
                id = obj.attributes['data-sender-control'].value;
                elementCode = id.substring(0,id.indexOf("Index"));
            }
            else{
                id  = $(obj).parents(".rdv").data("row-excel");
                //var mainHeader = $(obj).parents(".rowExcelGroup").data("element-code");              
                elementCode = $(obj).data("element-code");
                //var elements = GetNumOfElementsUnder(obj, ".rowExcelGroup", ".rdv");
            }
            
            index = parseInt(id.substring(id.indexOf("Index")+5));      // Get the id name with the index num and extract the index from it.
            

            for(var i = 0; i< arrDefault.length; i++)
            {
                if(arrDefault[i].index == index)
                {
                    // Check if const or checked
                    isConst = $("#inputTextBox-" + ctrRowName).is(':checked');    //($(obj).parent(".rdv").find(".CBConst").is(':checked'));
                    res = recursiveUpdateObjOption(arrDefault[i].arr, elementCode, isConst, toUpdate);
                    break;                    
                }
            }

            if(toUpdate)
            {

            }
            else{
                return res.resObj;
            }
        }


        // Get the head element in the array and run deep to the end of the tree and:  
        // update the settings value OR return the settings object to show it's data on the screen.
        function recursiveUpdateObjOption(element, valName, isConst, toUpdate)
        {
            var arrRes = {isDone:false, resObj: {}};      // Update the object.

            if(element.fields != null)
            {
                for(var i = 0; i < element.fields.length; i++)
                {
                    arrRes = recursiveUpdateObjOption(element.fields[i], valName, isConst, toUpdate);
                    if(arrRes.isDone)
                    {
                        break;
                    }
                }
            }
            else
            {
                if(element.element_code == valName)
                    {
                        if(toUpdate)
                        {   // Update values
                            if(isConst)
                            { 
                                element.json_path.const_obj.settings = jQuery.extend({}, GetOptionsValues());//"TODO - UPDATE the settings values";
                            }   
                            else{
                                element.json_path.drop_down.settings = jQuery.extend({}, GetOptionsValues());//"TODO - UPDATE the settings values";
                            }
                        }
                        else{       // Return the object values -> settings
                            if(isConst)
                            {
                                arrRes.resObj = jQuery.extend({}, element.json_path.const_obj.settings);
                            }   
                            else{
                                arrRes.resObj = jQuery.extend({}, element.json_path.drop_down.settings);
                            }
                        }
                        
                        arrRes.isDone = true;      
                    } 
            }

            return arrRes;
        }

        // Return object with the last changes on the Options 
        function GetOptionsValues()
        {
            var optionsChanges = {};
            var optionsRows = $(".table-options").find('tbody').find('tr');

            for(var trIndex = 0; trIndex < optionsRows.length; trIndex++)
            {
                var tds = $(optionsRows[trIndex]).find('td');

                for(var tdIndex = 0; tdIndex < tds.length; tdIndex++)
                {
                    if(tds[tdIndex].children.length > 0)
                    {
                        if (tds[tdIndex].childNodes[0].tagName == "INPUT") {
                            if (tds[tdIndex].childNodes[0].type == "text") 
                            {
                                optionsChanges[tds[0].innerText] = tds[tdIndex].childNodes[0].value;  //valuesObj["settings"]={[constantParams]:1};  
                            }
                            if (tds[tdIndex].childNodes[0].type == "checkbox") {
                                if(tds[tdIndex].childNodes[0].checked)
                                {
                                    optionsChanges[tds[0].innerText] = 1;
                                }
                            }
                        }
                        if (tds[tdIndex].childNodes[0].tagName == "SELECT") {
                            var selectChilds = $(tds[tdIndex].childNodes[0]).find('option');

                            for(var selectIndex = 0; selectIndex < selectChilds.length; selectIndex++)
                            {
                                if(selectChilds[selectIndex].selected)
                                {
                                    //var temp = "";
                                    //temp = selectChilds[selectIndex].innerText;
                                    optionsChanges[tds[0].innerText] = selectChilds[selectIndex].value; //innerText;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return optionsChanges;
        }


        function CreateModalPanel()
        {
            var dDLMatchOn = '<select id="ddlMatchOn">';

            optionsObjFactory["matchOn"].values.forEach(function(element){
                    dDLMatchOn += '<option value=' + element.id +'>' + element.name + '</option>'; 
                });

            dDLMatchOn += /*'<option selected="selected">test</option>'+*/'</select>';

            htmlModal = '<div class="modal fade" id="optionModalPanel" tabindex="-1" role="dialog" aria-labelledby="setOptionModalCenterTitle" aria-hidden="true">'+
                        '<div class="modal-dialog modal-dialog-centered" role="document">'+
                        '<div class="modal-content">'+
                            '<div class="modal-header">'+
                            '<h5 class="modal-title" id="setOptionModalCenterTitle">Set Option</h5>'+
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                                '<span aria-hidden="true">&times;</span>'+
                            '</button>'+
                            '</div>'+
                            '<div class="modal-body">'+
                            '<table class="table table-striped table-options">' +
                            '<thead>' +
                            '<tr>' +
                                '<th scope="col">#</th>' +
                                '<th scope="col">Name</th>' +
                                '<th scope="col">Value</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>' +
                            '<tr>' +
                                '<th scope="row">1</th>' +
                                '<td>skipIfEmpty</td>' +
                                '<td><input type="checkbox" id="optionSkip"></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<th scope="row">2</th>' +
                                '<td>skipRowIfEmpty</td>' +
                                '<td><input type="checkbox" id="optionSkipRow"></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<th scope="row">3</th>' +
                                '<td>skipGroupIfEmpty</td>' +
                                '<td><input type="checkbox" id="optionSkipGroup"></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<th scope="row">4</th>' +
                                '<td>delimiter</td>' +
                                '<td><input type="text" id="optionDelimiter" value=""></td>' +
                            '</tr>' +
                            '<tr>' +
                                '<th scope="row">5</th>' +
                                '<td>matchOn</td>' +
                                '<td>' +
                                     dDLMatchOn +
                                '</td>' +   
                            '</tr>' +
                            '</tbody>' +
                        '</table>'+
                        '</div>'+
                            '<div class="modal-footer">'+
                            '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>'+
                            '<button type="button" class="btn btn-primary btn-update-settings" data-sender-control="">Save changes</button>'+
                            '</div>'+
                        '</div>'+
                        '</div>'+
                    '</div>';

            $("#fieldsMapping").append(htmlModal);   
        }


        /*function CreateModalPanel(obj)
        {

            htmlModal = '<div class="modal fade" id="optionModalPanel" tabindex="-1" role="dialog" aria-labelledby="setOptionModalCenterTitle" aria-hidden="true">'+
                        '<div class="modal-dialog modal-dialog-centered" role="document">'+
                        '<div class="modal-content">'+
                            '<div class="modal-header">'+
                            '<h5 class="modal-title" id="setOptionModalCenterTitle">Set Option</h5>'+
                            '<button type="button" class="close" data-dismiss="modal" aria-label="Close">'+
                                '<span aria-hidden="true">&times;</span>'+
                            '</button>'+
                            '</div>'+
                            '<div class="modal-body">';
            htmlModal += CreateModalTable(obj);
            htmlModal +=    '</div>'+
                            '<div class="modal-footer">'+
                            '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>'+
                            '<button type="button" class="btn btn-primary btn-update-settings" data-sender-control="' +  $(obj).parents(".rdv").data("row-excel")  +'">Save changes</button>'+
                            '</div>'+
                        '</div>'+
                        '</div>'+
                    '</div>';

            $("#fieldsMapping").append(htmlModal);    // TODO Avihay - need to do remove to the #fieldsMapping" when close the window?
        }

        function CreateModalTable(obj)
        {
            var settingsObj = GetObjDataForOptions(obj, false);
            var cbOptionSkip = "";
            var cbOptionSkipGroup = "";
            var tbOptionDelimiter = "";
            var ddlMatchOn = "";
            var objMatchOnVal = "";


            if("skipIfEmpty" in settingsObj && settingsObj.skipIfEmpty == 1)
            {
                cbOptionSkip = "checked";
            }
            if("skipGroupIfEmpty" in settingsObj && settingsObj.skipGroupIfEmpty == 1)
            {
                cbOptionSkipGroup = "checked";
            }
            if("delimiter" in settingsObj)
            {
                tbOptionDelimiter = settingsObj.delimiter;
            }
            if("matchOn" in settingsObj)
            {
                ddlMatchOn = " selected";
                objMatchOnVal = settingsObj.matchOn;
            }

            var dDLMatchOn = '<select id="ddlMatchOn">';

            optionsObjFactory["matchOn"].values.forEach(function(element){
                if(element == objMatchOnVal)
                {
                    dDLMatchOn += '<option' + ddlMatchOn + '>' + element + '</option>'; 
                }
                else
                {
                    dDLMatchOn += '<option>' + element + '</option>'; 
                }

                
            });       
            dDLMatchOn += '</select>';

            htmlModalTable = '<table class="table table-striped">' +
                                '<thead>' +
                                '<tr>' +
                                    '<th scope="col">#</th>' +
                                    '<th scope="col">Name</th>' +
                                    '<th scope="col">Value</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>' +
                                '<tr>' +
                                    '<th scope="row">1</th>' +
                                    '<td>skipIfEmpty</td>' +
                                    '<td><input type="checkbox" id="optionSkip"' + cbOptionSkip +'></td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<th scope="row">2</th>' +
                                    '<td>skipGroupIfEmpty</td>' +
                                    '<td><input type="checkbox" id="optionSkipGroup"' + cbOptionSkipGroup +'></td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<th scope="row">3</th>' +
                                    '<td>delimiter</td>' +
                                    '<td><input type="text" id="optionDelimiter" value="'+ tbOptionDelimiter +'"></td>' +
                                '</tr>' +
                                '<tr>' +
                                    '<th scope="row">4</th>' +
                                    '<td>matchOn</td>' +
                                    '<td>' + dDLMatchOn + '</td>' +   
                                '</tr>' +
                                '</tbody>' +
                            '</table>';

            return htmlModalTable;                            
        }*/

        function ClearOptionsControls(obj)
        {
            var dDLMatchOnSelected = "";

            $('#optionSkip').replaceWith($('<input type="checkbox" id="optionSkip">')); 
            $('#optionSkipRow').replaceWith($('<input type="checkbox" id="optionSkipRow">')); 
            $('#optionSkipGroup').replaceWith($('<input type="checkbox" id="optionSkipGroup">'));
            $('#optionDelimiter').replaceWith($('<input type="text" id="optionDelimiter" value="">')); 

            dDLMatchOnSelected = '<select id="ddlMatchOn">';

            optionsObjFactory["matchOn"].values.forEach(function(element){
                dDLMatchOnSelected += '<option value=' + element.id +'>' + element.name + '</option>'; 
            });

            dDLMatchOnSelected += '</select>';
            $('#ddlMatchOn').replaceWith($(dDLMatchOnSelected)); 

            //'<h5 class="modal-title" id="setOptionModalCenterTitle">Set Option</h5>'
            $('#setOptionModalCenterTitle').replaceWith($('<h5 class="modal-title" id="setOptionModalCenterTitle">' + $(obj).data("element-name") + '</h5>')); 
        }

        // Show 'options' panel and initialize it with the call control values
        function SetAndShowOptions(obj)
        {
            ClearOptionsControls(obj);

            var settingsObj = GetObjDataForOptions(obj, false, $(obj).data("element-code"));     
            var tbOptionDelimiter = "";

            if("skipIfEmpty" in settingsObj && settingsObj.skipIfEmpty == 1)
            {
                $('#optionSkip').replaceWith($('<input type="checkbox" id="optionSkip" checked>'));
            }
            if("skipRowIfEmpty" in settingsObj && settingsObj.skipRowIfEmpty == 1)
            {
                $('#optionSkipRow').replaceWith($('<input type="checkbox" id="optionSkipRow" checked>'));
            }
            if("skipGroupIfEmpty" in settingsObj && settingsObj.skipGroupIfEmpty == 1)
            {
                $('#optionSkipGroup').replaceWith($('<input type="checkbox" id="optionSkipGroup" checked>'));
            }
            if("delimiter" in settingsObj)
            {
                tbOptionDelimiter = settingsObj.delimiter;
                $('#optionDelimiter').replaceWith($('<input type="text" id="optionDelimiter" value="' + tbOptionDelimiter + '">'));
            }
            if("matchOn" in settingsObj)
            {
                var dDLMatchOnSelected = '<select id="ddlMatchOn">';

                optionsObjFactory["matchOn"].values.forEach(function(element){
                    if(element.id == settingsObj.matchOn)
                    {
                        //dDLMatchOnSelected += '<option selected="selected">' + element + '</option>';
                        dDLMatchOnSelected += '<option value=' + element.id +' selected="selected">' + element.name + '</option>'; 
                    }
                    else{
                        //dDLMatchOnSelected += '<option>' + element + '</option>'; 
                        dDLMatchOnSelected += '<option value=' + element.id +'>' + element.name + '</option>'; 
                    }
                });
    
                dDLMatchOnSelected += '</select>';
                $('#ddlMatchOn').replaceWith($(dDLMatchOnSelected)); 
            }
        }
        //----------------
        
        function fieldTemplate(element, index)
        {
            let htmlCode = "";
            const elemTitle = element.title[0];
            const elemCode = element.element_code;
            
            htmlCode =  '<div class = "row pb-3 rdv" data-row-excel="'+ elemCode + "Index" + index +'">'+     // The class 'rdv' means row data variables.
                        '<h5 class="col-2" data-element-code="'+ elemCode +'">' +  elemTitle + '</h5>';       
                                                                                                            
            htmlCode +=  '<div class="dropdown col-6 ddl1">' +                                                                   // The class 'ddl1' means drop down list 1.
                                '<input type="text" class="form-control tbc" id=TBConst' + elemCode + "Index" + index + ' data-element-code="'+ elemCode +'" placeholder="הזן ערך" style="display: none;">' +
                                ' <select  class="form-control ddl" id="DDL'+ elemCode + "Index" + index + '" value="">' +
                                '</select>' +
                        '</div>';
                        
            if(elemCode != 'idno' && elemCode != 'idno_stub')
            {
                htmlCode += '<div class = "col-2 align-middle">'+
                                '<input type="checkbox" class="CBConst form-check-input" aria-label="Checkbox for following text input" id="inputTextBox-'+ elemCode + '" data-element-code="'+ elemCode +'">' +
                                '<label class="form-check-label pr-4" for="' + elemCode + '">Const</label>' +
                            '</div>' +
                            '<button type="button" class="col-1 btn btn-primary btn-settings" id="Options' + elemCode + "Index" + index + '" data-element-code="'+ elemCode +'" data-element-name="' + elemTitle +'"  disabled>Options</button>';  
            }
            htmlCode += '</div>';       

            return htmlCode;
        }

        function cardTemplate(field, element, index)
        {
            let htmlCode = ""; 
                htmlCode +='<div class="card sg1" id="' + element.element_code + "Index" + index +'">'+                    // The class 'sg1' means select group 1.
                                '<div class="card-body contain-'+ element.element_code +'" '  + 'data-group="' + element.element_code + '">';//+
                
                                if(element.element_code != 'idno' && element.element_code != 'type_id' && element.element_code != 'lot_status_id' && element.element_code != 'idno_stub')
                                {          
                                    htmlCode += '<div class="floatbylang"><button type="button" class="btn btn-outline-danger brc" data-id-code="' + element.element_code +'">X</button></div>';    // The class'brc' means button remove container
                                }

                htmlCode += field +    
                                '</div>'+
                            '</div>';

            return htmlCode;
        }

        function CreateScreenFields()
        {
            var htmlFieldsList = "";
            var tempMarginTopOnce = "";         // Add margin top to the upper list button. 
            var oneTimeEntry = true;            // Helps to ensure we add just the top button in the list the marging top (mt-4).

            htmlFieldsList = '<div class="card p-3" id="fildesList">';

            identify.forEach(element => {
                if(element.element_code != 'idno' && element.element_code != 'type_id' && element.element_code != 'lot_status_id' && element.element_code != 'idno_stub')
                {
                    if(oneTimeEntry)
                    {
                        tempMarginTopOnce = 'mt-4';
                        oneTimeEntry = false;
                    }
                    else{
                        tempMarginTopOnce = "";
                    }

                    htmlFieldsList += '<div class="card p-3 ' + tempMarginTopOnce +'">' +
                                        '<div class="row">'+
                                            '<div class="col-1">' + 
                                                '<button type="button" class="btn btn-primary btn-add-fields" id="btnAddField' + element.element_code + '" data-id-code="' + element.element_code +'" data-title="' +element.title[0] +'"> + </button>' +
                                            '</div>' +
                                            '<div class="col-11">' +
                                                '<h5 data-element-code=' + element.element_code + '>' + element.title[0] + '</h5>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>';
                }
            })

            htmlFieldsList += '</div>';
            $("#fieldsMapping").append(htmlFieldsList);
            
            return htmlFieldsList;
        }


        function CreateFieldSection()
        {

        }

        // Create the array by name and initialize it to the defaults values.
        function createArrByName()
        {   
            identify.forEach(element => {
                arrayPath = [];                          // Holds the const_obj or drop down list path for the server json file 
                //arrInnerSettings =[];                  // Holds the settings drop down list path for the server json file - like"skipIfEmpty" etc... 
                arrayPath["const_obj"] = {};
                arrayPath["drop_down"] = {};
                arrByName[element.element_code] = element;

                if(element.fields != null)
                {            
                    for(var i = 0; i < element.fields.length; i++)
                    {
                        //tempArrayPath1 = [];                          // Holds the const_obj or drop down list path for the server json file 
                        //tempArrayPath1["const_obj"] = {};
                        //tempArrayPath1["drop_down"] = {};

                        element.fields[i] = recursiveDynemicCreate(element.fields[i]);
                    }
                    /*for(var i = 0; i < element.fields.length; i++)
                    {
                        tempArrayPath1 = [];                          // Holds the const_obj or drop down list path for the server json file 
                        tempArrayPath1["const_obj"] = {};
                        tempArrayPath1["drop_down"] = {};

                        element.fields[i]["json_path"] = tempArrayPath1;
                    }*/
                }
                else{
                    element["json_path"] = arrayPath;
                }
            })         
        }

        // Get the head element in the array and run deep to the end of the tree and 
        // create the "drop_down" and "const_obj" in the last branch. 
        function recursiveDynemicCreate(element)
        {   
            if(element.fields != null)
            {
                for(var i = 0; i < element.fields.length; i++)
                {
                    element.fields[i] = recursiveDynemicCreate(element.fields[i]);
                }
            }
            else
            {
                tempArrayPath1 = [];                          // Holds the const_obj or drop down list path for the server json file 
                tempArrayPath1["const_obj"] = {};
                tempArrayPath1["drop_down"] = {};

                element["json_path"] = tempArrayPath1;
            }
            return element;
        }


        // List that the user decided to show by his selected
        // First value is the id, the second value is whether to add or remove from the list, The third value is his index in the list. 
        function createEditedList(idName, toAdd, indexValue)
        {
            if(toAdd)
            {
                var newObject = jQuery.extend(true, {}, arrByName[idName]);
                arrDefault.push({"index":lastPosition, arr: newObject});
                if(arrDefault[arrDefault.length-1].arr.fields != null)    // There is multipal fields
                {
                    arrDefault[arrDefault.length-1].arr.fields.forEach(element => {
                        recursiveClearObj(element);
                        //element.json_path.const_obj = {};
                        //element.json_path.drop_down = {};
                    });
                }
                else
                {
                    arrDefault[arrDefault.length-1].arr.json_path.const_obj = {};
                    arrDefault[arrDefault.length-1].arr.json_path.drop_down = {};
                }
                
                //arrDefault.push({"index":lastPosition, arr: arrByName[idName]});
                lastPosition++;
            }
            else
            {
                for(i = 0; i<arrDefault.length; i++)
                {
                    if(arrDefault[i].index == indexValue)
                    {
                        arrDefault.splice(i,1);
                        break;
                    }
                }
            }
        }

        // Get the head element in the array and run deep to the end of the tree and 
        // clear the "drop_down" and "const_obj" objects.
        function recursiveClearObj(element)
        {
            if(element.fields != null)
            {
                for(var i = 0; i < element.fields.length; i++)
                {
                    /*element.fields[i] = */recursiveClearObj(element.fields[i]);
                }
            }
            else
            {
                element.json_path.const_obj = {};
                element.json_path.drop_down = {};
            }
        }
        
        
        function createGui()
        {
            $("#fieldsMapping").empty();
            settings['mappingType'];

            var index = 0;
            arrDefault.forEach(element => {
                let field = fieldHtml(element.arr, index);
                let card  = cardTemplate(field, element.arr, index);
                $("#fieldsMapping").append(card);
                index++;
            });

            var htmlBtn = "<button type='button' class='btn mt-4 btn-primary btn-lg btn-block' id='btnAddFields'> הוספת שדה </button>"; 
            $("#fieldsMapping").append(htmlBtn);
            CreateScreenFields();
            $("#fildesList").hide();
            
            // Add mapping information in the 'leftNavSidebar'
            $('.editorBottomPadding').append('<div id="caColorbox" style="border: 6px solid #FFFFFF;">' +
                                                '<div class="col-6"><label id="mappinhName"><b>שם מיפוי :</b> <span>'+ settings.Code +'</span></label></div>' +
                                                '<div class="col-6"><label id="mappingCode"><b>קוד מיפוי :</b> <span>'+ settings.Name +'</span></label></div>' +
                                                '<div class="col-12"><label id="fileName"><b>שם קובץ :</b> <span>'+ $('#excelfile')[0].files[0].name +'</span></label></div>' +
                                                '<div class="col-12"><label id="mappingType"><b>סוג מיפוי :</b> <span>'+ $('#Module option:selected')[0].text +'</span></label></div>' +
                                            '</div>');

            $("#mainContent").append('<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script><script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>');                                              
            CreateModalPanel();
        }

         
        function HandleComponentsView(id, showMe)
        {
            //var ddlSection = $("#"+ id);
            var selectL = "selectLbl-";
            var dropD = "DDL";
            var textB = "TBConst";

            if(showMe)
            {
                $("#" + selectL + id).hide();
                $("#" + dropD + id).hide();
                $("#" + textB + id).show();
                //$(".tbc",  ddlSection).show();
                //$(".ddl1", ddlSection).hide();
            }
            else
            {
                $("#" + selectL + id).show();
                $("#" + dropD + id).show();
                $("#" + textB + id).hide();
                //$(".tbc",  ddlSection).hide();
                //$(".ddl1", ddlSection).show();
            }
        }
        function SetFreams()
        {
            var freams = $(".sg1");

            $(".asb").remove();
            for(i = 0; i< freams.length; i++)
            {
                $("#" + freams[i].id).css('border-color','#e6e6e6');      // Gray color
            }
        }
    
        function SaveJsonServer()
        {
            var res;
            var jsonMappObj = {};
            var jsonSettObj = {};
            var JsonComplete = new FormData();
           
            SetFreams();
            jsonMappObj = CreateJsonMapping();
            if(jsonMappObj != null)             // There is problem with the data that the user insert - missing fields.
            {
                jsonSettObj = CreateJsonSettings();
                JsonComplete.append('map', JSON.stringify(jsonMappObj));
                JsonComplete.append('set', JSON.stringify(jsonSettObj));
        
                $("#fieldsMapping").hide();
                $("#mainContent").append('<i class="fa fa-spinner fa-spin" style="font-size:48px"></i>');
                $.when(SendJsonToServer(saveMappingUrl, JsonComplete)).done( function(res)
                {
                    $("#btnSave").hide();
                    //ShowInformation(res);
                    if(typeof res == "undefined")
                    {
                        HandleErrorCon(res);
                    }
                    else{
                        ShowInformation(res);
                    }
                }).fail(function(res)
                {
                    HandleErrorCon(res);
                });

                $('.fa-spin').remove()
                $(document).scrollTop(0);
            }
        }


        // Gets error cases and handle them
        function HandleErrorCon(res)
        {
            var message = "";

            if(res['status'] == 'Error')   
            {
                message = " תבנית היבוא לא נשמרה ";
            }
            else if(res.statusText == "error")
                {
                    message = " ישנה בעיה בתקשורת הנתונים, חלק מהמידע לא נשלח ";
                }
        
            temp = '<div class="alert alert-danger" role="alert" id="alert_link" align="center">'+
                    message + '<a href="#" class="alert-link">לחזרה למסך הקודם לחצו כאן</a> '+
                    '</div>';
                    
            $('#mainContent').append(temp);
            $(".alert-link").on('click',ShowScreenAgain);             // This event attach to the above <div> by id 
        }

    
        function ShowInformation(res)
        {
            var linkUrl = '/mana/index.php/batch/MetadataImport/Run/importer_id/' + res.id; 

            var temp;
            if(res['status'] == 'success')   
            {
                temp ='<div class="alert alert-success" role="alert" align="center">'+
                            'תבנית היבוא נשמרה בהצלחה '+ '<a href="' + linkUrl + '">לביצוע יבוא לחצו כאן</a>' +
                        '</div>';   
                $('#mainContent').append(temp);
            }
        }
    
        function ShowScreenAgain()
        {
            $('#alert_link').remove();
            $("#fieldsMapping").show();
            $("#btnSave").show();
        }

        //Check whether the required value id exist and ok
        function CheckRequiredValues(indexLocation)
        {
            var res = true;              // The required value id exist and ok
            var currentElement = arrDefault[indexLocation].arr;
            var isDone = false;
            
            if(currentElement.fields != null)    // There is multipal fields
            {
                for(var i = 0; i < currentElement.fields.length; i++)
                {
                    isDone = recursiveRequiredValues(currentElement.fields[i]);
                    if(isDone)
                    {
                        return true;
                        //break;
                    }
                    else
                    {
                        res = false;
                    }
                    /*if(!jQuery.isEmptyObject(currentElement.fields[i].json_path.drop_down) || !jQuery.isEmptyObject(currentElement.fields[i].json_path.const_obj))
                    {
                        return true;
                    }
                    else
                    {
                        res = false;
                    }*/
                }
            }
            else if(currentElement.element_code == "idno")
                {
                    if(jQuery.isEmptyObject(currentElement.json_path.drop_down))
                    {
                        return false;
                    } 
                }
                else if(jQuery.isEmptyObject(currentElement.json_path.drop_down)  && jQuery.isEmptyObject(currentElement.json_path.const_obj) && currentElement.element_code !="type_id") // Update 7.23.2019 - allow to "type_id" to be empty
                    {
                        
                        return false;
                    }

            return res;
        }

        function recursiveRequiredValues(element)
        {
            var isDone = false;             // Update the object.

            if(element.fields != null)
            {
                for(var i = 0; i < element.fields.length; i++)
                {
                    isDone = recursiveRequiredValues(element.fields[i]);
                    if(isDone)
                    {
                        return true;
                        //break;
                    }
                }
            }
            else
            {
                if(/*element.element_code == valName*/!jQuery.isEmptyObject(element.json_path.drop_down) || !jQuery.isEmptyObject(element.json_path.const_obj))
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }  
                        
                        /*if(!jQuery.isEmptyObject(currentElement.fields[i].json_path.drop_down) || !jQuery.isEmptyObject(currentElement.fields[i].json_path.const_obj))
                    {
                        return true;
                    }
                    else
                    {
                        res = false;
                    }*/
                    
            }
        }

        function CreateJsonMapping()
        {
            var jsonObj = {};
            var constIndex;      // Represent the value we insert after the "_CONSTANT_" in the json obj. it's a loop that count every 'const' checkbox in the screen.
            var isConst;
            var elemCode;
            var firstRequiredVal = "idno";
            //var secondRequiredVal = "type_id";
            var statusOk = true;

            for( count = 0, globalCounter = 0; count < arrDefault.length; count++)
            {
                isConst = true;
                elemCode = arrDefault[count].arr.element_code; 
                jsonObj[elemCode + count] = "";

                if(CheckRequiredValues(count))
                {
                    if(arrDefault[count].arr.fields == null)            //  there is no multipal fields
                    {
                        var objHeader = {};
                        if(jQuery.isEmptyObject(arrDefault[count].arr.json_path.const_obj))       // check whether the 'const' object is empty
                        {
                            isConst = false;
                            objHeader[arrDefault[count].arr.json_path.drop_down.selectedRow] = BuildeJsonString(isConst, arrDefault[count].arr);
                        }
                        else
                        {
                            var constTemp  = "_CONSTANT_:" + globalCounter + ":" + $('#TBConst' + arrDefault[count].arr.element_code + "Index" + arrDefault[count].index).val();
                            objHeader[constTemp] =  BuildeJsonString(isConst, arrDefault[count].arr);
                        }
                        jsonObj[elemCode +count] = objHeader; 
                            
                        globalCounter++;
                    }
                    else
                    {
                        var tempRes = recursiveJsonMappingObj(arrDefault[count].arr.fields, count);
                        jsonObj[arrDefault[count].arr.element_code + count] = jQuery.extend(true, {}, tempRes);
                        innerTempJsonObj = {};
                    }
                }
                else{
                    $("#" + arrDefault[count].arr.element_code + "Index" + arrDefault[count].index).css('border-color','#f00');               
                    statusOk = false;
                }
            }

            if(!statusOk)
            {
                $('<div class="alert alert-danger asb" role="alert">יש למלא את כל שדות החובה</div>').insertBefore("#fieldsMapping");
                ManageBackGroundcolorContainer();
                return null;
            }
            else
            {
                return jsonObj;
            }
        }
                 

        
        // Get the head element in the array and run deep to the end of the tree and 
        // clear the "drop_down" and "const_obj" objects.
        function recursiveJsonMappingObj(fieldsObj, count)
        {
            fieldsObj.forEach(element =>{

                if(element.fields != null)
                {
                    recursiveJsonMappingObj(element.fields, count);
                }
                else
                {
                    var emptyConst = jQuery.isEmptyObject(element.json_path.const_obj);

                            if(!emptyConst || !jQuery.isEmptyObject(element.json_path.drop_down))
                            {
                                isConst = true;
                                if(jQuery.isEmptyObject(element.json_path.const_obj))       // check whether the 'const_obj' object is empty
                                {
                                    isConst = false;
                                    innerTempJsonObj[element.json_path.drop_down.selectedRow] = BuildeJsonString(isConst, element);                
                                }
                                else
                                {
                                    var constTemp  = "_CONSTANT_:" + globalCounter + ":" + $('#TBConst' + element.element_code + "Index" + count).val();
                                    innerTempJsonObj[constTemp] = BuildeJsonString(isConst, element);
                                }

                                globalCounter++;
                            }
                }
            });
            return innerTempJsonObj;
        }
//------------------------------------------------
        function ManageBackGroundcolorContainer()
        {
            var conList = $("#fieldsMapping .sg1");
            var color;

            for(i = 0; i < conList.length; i++)
            {
                if(i < 2 || (i > 1 && i%2 == 1)) 
                {
                    color = '#E9ECEF';                      // Gray color TODO - get te currect color
                }
                else
                {
                    color = '#FFFFFF';                      // White color 
                }
                $("#" + conList[i].id).css('background-color', "'" + color + "'");

                
            }  
        }

        function BuildeJsonString(isConst, obj)
        {
            var innerNewObj = {};
            innerNewObj['options'] = {};
        
            if(isConst)
            {
                innerNewObj['const_val'] = obj.json_path.const_obj.const_val;
                innerNewObj['destenation'] = obj.json_path.const_obj.destenation;
                innerNewObj['options'] = BuildeJsonOptions(obj.json_path.const_obj.settings);          // TODO - delete - obj.json_path.const_obj.settings;
            }
            else
            {
                innerNewObj['options'] = BuildeJsonOptions(obj.json_path.drop_down.settings);           // TODO - delete - obj.json_path.drop_down.settings;
                innerNewObj['destenation'] = obj.json_path.drop_down.destenation;
            }

            if(innerNewObj['options'])

            return innerNewObj;
        }

        // Build the Option json and in case of many parameters in the "matchOn" we turn it into array (insted of string)
        function BuildeJsonOptions(OptSettings)
        {
            var objSettings = {};
            
            for(var key in OptSettings)
            {
                if(key == "matchOn")
                {
                    if(OptSettings[key].toString().includes(","))
                    {
                        objSettings["matchOn"] = OptSettings[key].split(",");
                    }
                    else{
                        objSettings["matchOn"] = OptSettings[key];
                    }
                }
                else{
                    objSettings[key] = OptSettings[key];
                }
            }

            return objSettings;
        }
    
        // Get element and return the full path by the html ancestors 
        function BuiltFullPath(thisObj, selectedDdlId, isDropDown)
        {
            var arrPath = [];
            var ancestorsPath = "";
                      
            var ancestors = $(thisObj).parents(".rowExcelGroup");//.length;//$("#" + selectedDdlId ).parents(".rowExcelGroup");//.length;

            arrPath[0] = selectedDdlId;
            for(var i = 0; i < ancestors.length; i++)
            {        
                arrPath[i+1] = ancestors[i].dataset.elementCode;//$("#" + selectedDdlId ).closest(".rowExcelGroup").data("element-code");
            }

            arrPath[ancestors.length+1] = settings.Module;
            arrPath = arrPath.reverse();
            ancestorsPath = arrPath.toString();
            ancestorsPath = ancestorsPath.replace(/,/g,'.');

            return ancestorsPath;
        }

        function CreateJsonSettings()
        {
            var jsonObj = {};
            
            jsonObj["locale"] = settings.language;
            jsonObj["name"] = settings.Name;
            jsonObj["code"] = settings.Code;
            jsonObj["table"] = settings.Module;
    
            // Const values
            jsonObj["inputFormats"] = "XLSX";
            jsonObj["existingRecordPolicy"] = "merge_on_idno";
            jsonObj["errorPolicy"] = "ignore";
            jsonObj["numInitialRowsToSkip"] = "1";
            jsonObj["type"] = "Physical_object";
    
            return jsonObj;
        }
    
        /*Return res array/object with two cells, the first is whether the ajax call succeeded (true/false).
          the second (exist only if the first is true) holds the data.
        */
        function SendJsonToServer(arrContact, data)
        {
            var resArr = [];
            resArr["result"] = "false";
    
            return $.ajax({
                type: 'POST',
                url: arrContact,
                data: data,
                dataType: 'json',      
                processData: false,
                contentType: false
            })
            .done( function(data){
                resArr["result"].value = "true";
                resArr["data"] = data;
                return resArr;
            })
            .fail(function(data){
                resArr["data"] = data;
                return resArr;
            });
        }
    
        // Get the screen id
        // Checks that all fields have been filled by the user 
        function validateForm( dataScreenName) {
            var fieldsFull = false;
            var tempSet = $('#' + dataScreenName +' :input');

            // Delete all the empty cell alerts remarks 
            for( index= 0; index < tempSet.length; index++)         // Set the settings object
            {
                if( tempSet[index].type == "text" || tempSet[index].tagName == "SELECT" || tempSet[index].type == "file") 
                {
                    $("#"+ tempSet[index].name).removeClass('is-invalid');
                }
            }
            
            for( i= 0; i < tempSet.length; i++)         // Set the settings object
            {
                if( tempSet[i].type == "text" || tempSet[i].tagName == "SELECT" || tempSet[i].type == "file") 
                {
                    if(tempSet[i].value == "")
                    {
                        if(tempSet[i].id == "excelfile")
                        {
                            alert("חובה למלא את השדה " + $("#"+ tempSet[i].name).siblings("label").text());
                        }
                        $("#"+ tempSet[i].name).addClass('is-invalid');
                        break; 
                    }
                }
                if(i == tempSet.length-1)
                {
                    fieldsFull = true;
                }
            }
    
            return fieldsFull;
        }

        function updateDataObjectValues(thisObj, isConst)//id, value, innerText, isConst)
        {
            var id  = thisObj.id;
            //var value = thisObj.value;
            var exceptionValue = "idno";
            var dest = "destenation";               // System value                 
            //var mainHeader = $(thisObj).parents(".rowExcelGroup").data("element-code");
            var index = parseInt(id.substring(id.indexOf("Index")+5));      // Get the id name with the index num and extract the index from it.        
            //var rowsInContainer;
            //var elements = GetNumOfElementsUnder(thisObj, ".rowExcelGroup", ".rdv");
            var constantParams;
            //var constIndex = 333;  // TODO -need to create loop and increment this value for all of one of the checkbbox text-------------------------------------********
            //rowsInContainer = //element.getElementsByClassName("rdv");   // The selected row in the dropDownList - it's value is the column num in the Excel.

            for(var i = 0; i< arrDefault.length; i++)
            {
                constantParams = "";
                var valuesObj = {};
                if(arrDefault[i].index == index)
                {   
                    if(arrDefault[i].arr.element_code != exceptionValue && isConst)
                    {
                        var childeHeaderName = $(thisObj).data("element-code");                        //$(".CBConst",column).data('element-code');  
                        var cbTextValue  = $(thisObj)[0].value;                                         //$('#TBConst' + childeHeaderName)[0].value;               
                        
                        valuesObj["constHeader"] = "_CONSTANT_:" /*+ constIndex + ":"*/ + cbTextValue;
                        valuesObj[dest] = BuiltFullPath(thisObj, childeHeaderName, false);
                        valuesObj["const_val"] = valuesObj[dest]; 
                        //---------------------------
                        
                        if(arrDefault[i].arr.require != undefined)              // The 'require' value came from the server
                            {
                                constantParams = "skipRowIfEmpty";
                            }
                            else{
                                constantParams = "skipIfEmpty";
                                }
                        
                        valuesObj["settings"] = {};
                        valuesObj["settings"]={[constantParams]:1};                 // Constnt parameters


                        //-----------------------------

                        UpdateJsonPathObj(i, valuesObj, childeHeaderName, false);                               

                    }// DropDownList
                    else if($(thisObj, "form-control ddl")[0].selectedIndex != 0) // Check whether the selected row on the dropDownList is not empty 
                        {
                            var tempStr = $(thisObj, "select")[0].id;
                            var  selectedDdlId = tempStr.substring(3,tempStr.indexOf("Index"));     //element.getElementsByTagName("select")[colCount-1].id;
                            valuesObj["selectedRow"] = $(thisObj, "form-control ddl")[0].value;      // The selected value from the dropDownList
                            valuesObj[dest] = BuiltFullPath(thisObj, selectedDdlId, true);
                            //---------------------
                            
                            //if(mainHeader == exceptionValue)
                            if(arrDefault[i].arr.require != undefined)              // The 'require' value came from the server
                            {
                                constantParams = "skipRowIfEmpty";
                            }
                            else{
                                constantParams = "skipIfEmpty";
                                }
                            valuesObj["settings"] = {};                 
                            valuesObj["settings"]={[constantParams]:1};         // Constnt parameters 
                            //----------------------------------
                            UpdateJsonPathObj(i, valuesObj, selectedDdlId, true);                               
                        }
                        else{
                            var tempStr = $(thisObj, "select")[0].id;
                            var  selectedDdlId = tempStr.substring(3,tempStr.indexOf("Index"));
                            UpdateJsonPathObj(i, "", selectedDdlId, true);
                        }

                    break;
                }
                if(Object.keys(valuesObj).length > 0)
                {
                    columnObj[key] = valuesObj;
                }
            }
        }

        // Get object value to update the main object 'arrDefault' values.
        function UpdateJsonPathObj(location, updateObjVal, valName, isDropdown)
        {
            var constTempVal;
            var dropVal;
            var isDone = false;

            if(isDropdown)
            {
                dropVal = updateObjVal;
                constTempVal = {};     
            }
            else
            {
                dropVal = {};
                constTempVal = updateObjVal; 
            }
                // Insert the server json path to the object.
                if(arrDefault[location].arr.fields != null)
                {       
                    for(var count = 0; count < arrDefault[location].arr.fields.length; count++)
                    {
                        isDone = recursiveUpdateObj(arrDefault[location].arr.fields[count], valName, dropVal, constTempVal);
                        if(isDone)
                        {
                            break;
                        }  
                    }
                }
                else
                {
                    arrDefault[location].arr.json_path.drop_down = dropVal;
                    arrDefault[location].arr.json_path.const_obj = constTempVal;
                }    
        }

        // Get the head element in the array and run deep to the end of the tree and 
        // clear the "drop_down" and "const_obj" objects.
        function recursiveUpdateObj(element, valName, dropVal, constTempVal)
        {
            var isDone = false;             // Update the object.

            if(element.fields != null)
            {
                for(var i = 0; i < element.fields.length; i++)
                {
                    isDone = recursiveUpdateObj(element.fields[i], valName, dropVal, constTempVal);
                    if(isDone)
                    {
                        break;
                    }
                }
            }
            else
            {
                if(element.element_code == valName)
                    {
                        element.json_path.drop_down = dropVal;
                        element.json_path.const_obj = constTempVal;
                        isDone = true;      
                    } 
            }

            return isDone;
        }

        // Get number of suns by given father class name and suns class name.
        function GetNumOfElementsUnder(thisObj, fatherName, searchVal)
        {
            var res;

            res = $(thisObj).parents(fatherName).children(searchVal).length;

            return res;
        }
        
        //------------------------------------Evants section-------------------------------
    
        // This event is globally for all the 'select' tags in the 'body' section and it fired when the 'change' event occur.
        $('body').on('change','.ddl', function(e){       
            DropdownlistFirstOption(this.id, this.value, this.selectedOptions[0].innerText);
            updateDataObjectValues(this, false);//.id, this.value, this.selectedOptions[0].innerText, false);
            
            if(this.selectedOptions[0].innerText == "")
            {
                $("#Options"+this.id.substring(3)).prop("disabled",true);
            }
            else{
                $("#Options"+this.id.substring(3)).prop("disabled",false);
            }
        });
                                //select
        $('body').on('click','#dropdown col-6', function(e){
    
           console.log(this.value);
        });
                                //select
        $('body').on('click','#botLoadExcel', function(e){ 
            var dataScreenName = e.currentTarget.attributes['data-screen-name'].value; 
            if(validateForm(dataScreenName))
            {
                if($(this).data("screen-name") == dataScreenName)   //"importMapping"
                {
                    $("#importMapping").hide();
                    $("#importMappingFirst").show();
                }
            }   
         });
         
         
         $('body').on('click','#btnLoadExcelProperties', function(e){ 
            var dataScreenName = e.currentTarget.attributes['data-screen-name'].value; 
            if(validateForm(dataScreenName))
            {
               if($(this).data("screen-name") == dataScreenName)    // "importMappingFirst"
                {
                    $("#importMappingFirst").hide();
                    $("#importMappingSecond ").show();
                    preload();
                }
            }   
         });
         

        // This event is globally for all the 'select' tags in the 'body' section and it fired when the 'focus' event occur.
        // Here we clears the specific 'dropDownList' and then update the list with the new list - (object); 
        $('body').on('focus','.ddl', function(e){
    
            var lastSelectedIndex;                  // The position of the last selected index in the new dynamic DropDownList.            
            var lastSelectedId = this.id;
            var lastSelectedValue = this.value;
            
            lastSelectedIndex = CreateDynamicDropList(this.id, this.value);
            $(this).empty();
            $(this).append(minimizeDropListStr);
      
            if(typeof lastSelectedIndex != "undefined" && lastSelectedIndex != "")
            {
                 this.options[lastSelectedIndex].selected = true;
            }
            
        });
    
        // This event is globally for all the 'checkbox' tags in the 'body' section and it fired when the 'checked' event occur.
        $('body').on('change', '.CBConst', function(e){
            var dataEC = $(this).parents('.rdv').data('row-excel');       
            var location = dataEC.slice(dataEC.indexOf("Index") + 5); // Extract the number after the "Index"
            for(var i = 0; i < arrDefault.length; i++)
            {
                if(arrDefault[i].index == location)
                {
                    if ($(this).is(':checked')) 
                    {  
                        HandleComponentsView(dataEC, true);     
                        DropdownlistFirstOption("DDL" + dataEC, "", "");
                        $('#DDL' + dataEC).val('0');                                          // Avihay - check if there other way to empty the selected dropDownList...
                        UpdateJsonPathObj(/*dataEC.slice(-1)*/ i , {} , $(this).data("element-code"), true);
                    }     
                    else
                    {
                        $("#TBConst" + dataEC).val("");
                        //$("#TBConst" + dataEC).trigger("focusout");         // Fier the 'focusout' event
                        UpdateJsonPathObj(/*dataEC.slice(-1)*/ i , {} , $(this).data("element-code"), false);             // Clear the 'const' value in the main object
                        HandleComponentsView(dataEC, false);
                    }
                }
            }

            if(!$(this).is(':checked') || $("#TBConst"+$(this).closest(".rdv").data("row-excel")).val() == "")
            {
                $("#Options"+$(this).closest(".rdv").data("row-excel")).prop("disabled",true);
            }
        });
    
        $('body').on('click', '#btnAddFields',function(e){
            if($('#fildesList:hidden'))
            {
                $("#btnAddFields").hide();
                $("#fildesList").show();             
            }
        });

        $('body').on('click', '#btnSave',SaveJsonServer);

        $('body').on('click', '.btn-settings', function(e){
            // These rows lines go together!
            $(".btn-update-settings").data("sender-control",$(this).parents(".rdv").data("row-excel"));             // need to fix it later - update the .data but we cant see it in the html attribute.
            $(".btn-update-settings").attr("data-sender-control", $(this).parents(".rdv").data("row-excel"));       // need to fix it later - Update the attribute (we can see it in the html) but not the .data.
            //Options btn
            SetAndShowOptions(this);
            $('#optionModalPanel').modal('show');
        });

        $('body').on('click', '.btn-update-settings', function(e){
            var ctrRowName = $(this).data("sender-control").substring(0,$(this).data("sender-control").indexOf("Index")); // the name of the row
            GetObjDataForOptions(this, true, ctrRowName);
            $('#optionModalPanel').modal('hide');
        });

        $('body').on('click', '.btn-add-fields',function(e){      
            var tempElement = [];
            var tempTitle, tempCard;
            var idOfLast;       // The id of the last element on screen - by the list 'identify'
            
            var temp = arrDefault[arrDefault.length-1].index. toString();   
            idOfLast = arrDefault[arrDefault.length-1].arr.element_code + "Index" + temp;   
            createEditedList( $(this).data("id-code"), true, null);                                                 // insert to end of the list
            
            tempElement = {'element_code': $(this).data("id-code") , 'title': [$(this).data("title")]};             // Create object with the new values - to create the "container"      
            tempTitle = fieldHtml(arrByName[$(this).data("id-code") ],  (lastPosition-1).toString());
            if(arrByName[$(this).data("id-code") ].element_code != 'idno' && arrByName[$(this).data("id-code") ].element_code != 'type_id')
            /*{          
                tempTitle += '<div class="floatbylang"><button type="button" class="btn btn-outline-danger brc" data-id-code="' + arrByName[$(this).data("id-code") ].element_code +'">X</button></div>';    // The class'brc' means button remove container
            }*/
            tempCard = cardTemplate(tempTitle, tempElement, lastPosition-1);//tempCard = cardTemplate(tempTitle, tempElement, arrDefault.length-1);
            $(tempCard).insertAfter("#" + idOfLast);
            $("#fildesList").hide();
            $("#btnAddFields").show(); 
            ManageBackGroundcolorContainer();  
        });

        $('body').on('click', '.brc',function(e){     
            var index = 0;
            var arrDDLContainer;
            var temp = $(this).parents('.sg1').attr('id');
            index = parseInt(temp.substring(temp.indexOf("Index")+5));      // Get the id name with the index num and extract the index from it.
            arrDDLContainer = $(this).parents('.sg1').find('.ddl');         // Get all the DDLs in this container.
            UpdateDropDownList( arrDDLContainer);
            createEditedList( $(this).data("id-code"), false, index);
            $(this).parents('.sg1').remove();  
            ManageBackGroundcolorContainer();        
            });

        //First value is the id, the second value is his index in the list.     
        function UpdateDropDownList(ddlContainer)
        {
            for(dIndex = 0; dIndex < ddlContainer.length; dIndex++)
                {
                    DropdownlistFirstOption(ddlContainer[dIndex].id, "");                  
                }
        }

        // This event is globally for all the 'const text box' in the 'body' section and it fired when the 'focusout' event occur.
        /*$('body').on('focusout', '.tbc', function(e){
            //var res = $(this).parents(".rowExcelGroup").children(".rdv");// ".rdv"fatherName).children(searchVal);
            updateDataObjectValues(this, true);//.id, this.value, null, true);

            if(this.value == "")
            {
                $("#Options"+this.id.substring(7)).prop("disabled",true);
            }
            else{
                $("#Options"+this.id.substring(7)).prop("disabled",false);
            }
        });*/

        
        
        // This event is globally for all the 'const text box' in the 'body' section and it fired when the 'focusout' event occur.
        $('body').on('keyup', '.tbc', function(e){
            updateDataObjectValues(this, true);

            if(this.value == "")
            {
                $("#Options"+this.id.substring(7)).prop("disabled",true);
            }
            else{
                $("#Options"+this.id.substring(7)).prop("disabled",false);
            }
        });
    }
    main();