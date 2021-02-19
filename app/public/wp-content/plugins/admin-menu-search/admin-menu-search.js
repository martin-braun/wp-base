var preloadSearchIcon = new Image();
	preloadSearchIcon.src = "../wp-content/plugins/admin-menu-search/icon-search.png";

jQuery( document ).ready(function() {
    jQuery("#adminmenu").prepend("<li style='width: 80%;margin: 10px auto 10px auto;'><input type='text' id='admin-menu-filter-field' placeholder='Search Menus' style='width: 100%;background-image: url(../wp-content/plugins/admin-menu-search/icon-search.png);background-repeat: no-repeat;text-indent: 20px;-webkit-user-select:text;'></li>");

    jQuery(document).on('change input propertychange paste keyup', '#admin-menu-filter-field', function(){

    	var adminMenuFilterFieldValue = this.value.toLowerCase();

    	if(adminMenuFilterFieldValue == ""){
    		jQuery("li.menu-top").show();
    	}

    	jQuery('.wp-menu-name').each(function(index){
    		var topMenuItem = jQuery(this).clone().children().remove().end().text().toLowerCase();
    		if(topMenuItem.indexOf(adminMenuFilterFieldValue) == -1){
    			jQuery(this).closest("li.menu-top").hide();	
    		}else{
    			jQuery(this).closest("li.menu-top").show();	
    		}
    		
    		//Submenus have feelings too
    		jQuery(this).closest("li.menu-top").find("ul.wp-submenu li").each(function(index){
    			var subMenuItem = jQuery(this).text().toLowerCase();
    			if(subMenuItem.indexOf(adminMenuFilterFieldValue) > -1){
	    			jQuery(this).closest("li.menu-top").show();	
    			}
    		});
    	});

    });
});