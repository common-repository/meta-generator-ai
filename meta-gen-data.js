jQuery(document).ready(function($) {

    $('#generate-meta-data').on('click', function(e) {
        e.preventDefault();
        var productName = jQuery("#title").val();

        if (confirm("Are you sure you want to update the Meta Data?")) {
            getGeneratedData(productName);
        }
    });

    $('#generate-custom-description-button').on('click', function(e) {
        e.preventDefault();
        var productDetails = prompt("Enter product details for which product tags need to be generated:");

        if (productDetails === null || productDetails.trim() === "") {
            alert("Please enter the product details.");
            return;
        }

        $.ajax({
            type: 'POST',
            url: metaSettings.ajax_url, // WordPress AJAX URL
            data: {
                action: 'get_custom_meta_settings',
                productDetails: productDetails,
                nonce: metaSettings.nonce // Include the nonce here
            },
            success: function(response) {
                if(response.success == true && response.data != null){
                  if (response.data.custom_product_title_resp && response.data.custom_product_title_resp.status == "success") {
                    var custom_product_title_result = response.data.custom_product_title_resp.custom_product_title ? response.data.custom_product_title_resp.custom_product_title.trim() : '';
                    custom_product_title_result = custom_product_title_result.replace(/[^A-Za-z0-9\s:-]/g, '');
                    if(custom_product_title_result){
                      jQuery("#title-prompt-text").addClass("screen-reader-text");
                      jQuery("#title").val(custom_product_title_result);
                      getGeneratedData(custom_product_title_result);
                    }
                  }
                }
              },
              error: function(error) {
                console.error('Error:', error);
            },
        });

    });

    function getGeneratedData(productName) { 
        $.ajax({
            type: 'POST',
            url: metaSettings.ajax_url, // WordPress AJAX URL
            data : {
                action: 'get_meta_settings',
                productName: productName,
                nonce: metaSettings.nonce  // Include the nonce here
            },
            success: function(response) {
                var full_desc_editor = "";
                var short_desc_editor = "";
                
                if(response.success == true && response.data != null){
                    if (response.data.full_description_resp && response.data.full_description_resp.status == "success") {
                    var full_description_result = response.data.full_description_resp.full_description ? response.data.full_description_resp.full_description.trim() : '';
                        if(full_description_result){
                            full_desc_editor = tinyMCE.get('content');
                            if (full_desc_editor) {
                            full_desc_editor.setContent(full_description_result);
                            } else {
                            jQuery("#content").val(full_description_result);
                            }
                        }
                    }

                    if (response.data.short_description_resp && response.data.short_description_resp.status == "success") {
                    var short_description_result = response.data.short_description_resp.short_description ? response.data.short_description_resp.short_description.trim() : '';
                        if(short_description_result){
                            short_desc_editor = tinyMCE.get('excerpt');
                            if (short_desc_editor) {
                            short_desc_editor.setContent(short_description_result);
                            } else {
                            jQuery("#excerpt").val(short_description_result);
                            }
                        }
                    }

                    if (response.data.product_tags_resp && response.data.product_tags_resp.status == "success") {
                        var product_tags_result = response.data.product_tags_resp.generate_tags ? response.data.product_tags_resp.generate_tags.trim() : '';
                        if(product_tags_result){
                            var newTags = product_tags_result.split(",").map((tag) => tag.trim());
                            
                            // Remove existing tags
                            var numExistingTags = jQuery(".tagchecklist .ntdelbutton").length;
                            for (var i = 0; i < numExistingTags; i++) {
                            jQuery(".tagchecklist .ntdelbutton:first").trigger("click");
                            }

                            // Add new tags
                            setTimeout(function () {
                            newTags.forEach(function (tag) {
                                jQuery("#new-tag-product_tag").val(tag);
                                jQuery(".tagadd").trigger("click");
                            });
                            }, 0);
                        }
                    }
                }
            },
            error: function(error) {
            console.error('Error:', error);
            },
        });
    }
});


