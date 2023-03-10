jQuery(document).ready(function(e) {

    jQuery('body').on('click', '.sort-asc',function(){
         var sortBy = jQuery(this).closest('th').attr('class');
         var table, rows, switching, i, x, y, shouldSwitch;
         table = jQuery(this).closest('table');
         switching = true;
         while (switching) {
           switching = false;
           rows = jQuery(table).find('tr');
           for (i = 1; i < (rows.length - 1); i++) {
             shouldSwitch = false;
             x = jQuery(rows[i]).find('.' + sortBy);
             y = jQuery(rows[i + 1]).find('.' + sortBy);
             if (x.text().toLowerCase() > y.text().toLowerCase()) {
               shouldSwitch = true;
               break;
             }
           }
           if (shouldSwitch) {
             jQuery(rows[i]).before(jQuery(rows[i + 1]), jQuery(rows[i]));
             switching = true;
           }
     }
   });
   
    jQuery('body').on('click', '.sort-desc',function(){
         var sortBy = jQuery(this).closest('th').attr('class');
         var table, rows, switching, i, x, y, shouldSwitch;
         table = jQuery(this).closest('table');
         switching = true;
         while (switching) {
           switching = false;
           rows = jQuery(table).find('tr');
           for (i = 1; i < (rows.length - 1); i++) {
             shouldSwitch = false;
             x = jQuery(rows[i]).find('.' + sortBy);
             y = jQuery(rows[i + 1]).find('.' + sortBy);
             if (x.text().toLowerCase() < y.text().toLowerCase()) {
               shouldSwitch = true;
               break;
             }
           }
           if (shouldSwitch) {
             jQuery(rows[i]).before(jQuery(rows[i + 1]), jQuery(rows[i]));
             switching = true;
           }
     }
   });
   
      jQuery('.loader').show();
   
       getGalleryImagesFromDB();
   
       jQuery('#addimage').click(function(e) {
       
           if(document.getElementById("packagephoto").files.length == 0) {
               alert('Please select an image to upload');
           } else if(jQuery("#packagephoto")[0].files[0].size > 2097152) {
               alert('File too big, please select a file less than 2mb');
           } else {
               var arr = jQuery("#packagephoto").val().split('.');
              if(arr[1] == "jpg" || arr[1] == "jpeg" || arr[1] == "png" || arr[1] == "gif" || arr[1] == "svg") {
                   jQuery('.loader').show();
               jQuery('#preview').html('');
               var classname= jQuery('#packagename').val();
               var packagephoto_data = jQuery('#packagephoto').prop('files')[0];
           
               var form_data = new FormData();
   
               form_data.append('packagephoto_name', packagephoto_data);
               form_data.append('classname', classname);
               form_data.append('action', 'get_gallery_images');
               
               jQuery.ajax({
                   url: action_url_ajax.ajax_url,
                   type: 'post',
                   contentType: false,
                   processData: false,
                   data: form_data,
                   success: function(data){
                     jQuery('.loader').hide();
                       if(data != '') {
                       jQuery( "#preview" ).append(data);
                           jQuery('#packagephoto').val('')
                           jQuery('#packagename').val('')
                           jQuery('.loader').hide();
                       } else {
                           jQuery( "#preview" ).append(data);   
                       }
                   }
               });
              } else {
                   alert('Invalid format. Allowed formats are png, jpg, jpeg, svg, gif.');
                   jQuery('#packagephoto').val('')
               }  
           }
           });
       });   
       
       function getGalleryImagesFromDB(){
           jQuery('#preview').html('');
           jQuery.ajax({
               url: action_url_ajax.ajax_url,
               type: 'post',
               data:{
                   'action': 'load_gallery_images_db',
               },
               success: function(data){
                   if(data != '') {
                       jQuery('#preview').html(data);
                       jQuery('.loader').hide();
                   } else {
                       jQuery('#preview').html(data);     
                   }
               }
       
           });
       }
   
   function deleteGalleryImagesFromDB(){
           var post_arr = [];
           if(jQuery('#preview input[type=checkbox]:checked').length == 0){
                alert('Please select the image to delete');
                return false;
            }
           // Get checked checkboxes
           jQuery('#preview input[type=checkbox]').each(function() {
               if (jQuery(this).is(":checked")) {
                   var id = this.id;
                   post_arr.push(id);
               }
           });
           if(post_arr.length > 0){
               var isDelete = confirm("Do you really want to delete records?");
               if (isDelete == true) {
                   jQuery.ajax({
                       url: action_url_ajax.ajax_url,
                       type: 'post',
                       data:{
                           'post_id': post_arr,
                           'action': 'delete_gallery_images_db'
                       },
                       success: function(data){
                           jQuery.each(post_arr, function( i,l ){
                             jQuery("#"+l).closest('tr').remove();
                           });
                       }
               
                   });
               }
           }
       }