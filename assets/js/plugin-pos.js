

$pos = jQuery.noConflict();

(function($pos){

    $pos(document).ready(function () {
        // Added css for improving wordpress WP_List Tables.
        $pos("input[name=s]").addClass('pos_input_css');
        $pos("select[name=payment]").addClass('pos_input_css2');
        $pos("select[name=outlet_id]").addClass('pos_input_css report_select_outlet');
        $pos("#search-submit").addClass('pos_input_css2');
        $pos("#bulk-action-selector-top").addClass('pos_input_css1');
        $pos("#bulk-action-selector-bottom").addClass('pos_input_css1');
        $pos("#doaction").addClass('pos_input_css2');
        $pos("input[type=submit]").addClass('pos_input_css2');

        document.documentElement.style.setProperty('--primary', '#FFFFFF');
        document.documentElement.style.setProperty('--secondary', '#F6F6F6');
        document.documentElement.style.setProperty('--text-color-light', '#9F9F9E');
        document.documentElement.style.setProperty('--text-color', '#171826');
        document.documentElement.style.setProperty('--text-color-dark', '#171826');
        document.documentElement.style.setProperty('--primary-accent', '#FC8019');
        document.documentElement.style.setProperty('--primary-accent-light', '#f7e1c9');
        document.documentElement.style.setProperty('--secondary-accent', '#09AA29');
        document.documentElement.style.setProperty('--fixed', '#fff');


        // added place holder to wordpress input search box
        var translate = wk_wc_apipos_script.admin_translation;
        $pos('#pos-order-search-id-search-input').attr('placeholder', translate.order_search);
        $pos('#search-user-search-input').attr('placeholder', translate.user_search);
        $pos('#search-outlet-search-input').attr('placeholder', translate.outlet_search);
        $pos('#search-product-search-input').attr('placeholder', translate.product_search);
        $pos('#search-id-search-input').attr('placeholder', translate.product_search);
        $pos('#search-template-search-input').attr('placeholder', translate.invoice_search);
        $pos('#search-payment-search-input').attr('placeholder', translate.payment_search);
        $pos('#footer-upgrade').html('Point of Sale Version - '+ wk_wc_apipos_script.site_version);

        $pos('#footer-left').html(wk_wc_apipos_script.footer_left);
		$pos(document).on('click','.icon-uploader',function(event) {
			event.preventDefault();
			const iconSize = event.target.getAttribute( 'data-id' ).split( 'icon' )[1];
			var custom_uploader;
			var _self = this;
			var data_attr = $pos(this).data("id");
			var custom_uploader = wp.media({
				title: iconSize + 'x' + iconSize + ' icon',
				button: {
					text: 'Upload Pic',
				},
					multiple: false
				}).on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					$pos(_self).closest('.wc-pos-log-upload-logo-wraper').find(".image-url").attr('src',attachment.url);
					var site_url = attachment.url.replace( wk_wc_apipos_script.site_url, '' );
					$pos("#" + data_attr).val(site_url);
				})
				.open();

        });

        //Barcode text size reflactor




        $pos('#sync_products').on('click', (e) => {
            e.preventDefault();
            let outlet_id = $pos('#outlet_id').val();
            $pos.ajax({

					type:"POST",
					url: wk_wc_apipos_script.api_admin_ajax,
					data: {
						'action': 'sync_all_variable_product',
						'nonce': wk_wc_apipos_script.pos_api_nonce,
						'outlet_id': outlet_id,
                },
                beforeSend: function () {
                    $pos('#sync').text("Products syncing...");
                },
				success: function(response){
                    if (response.status == "success") {
                        $pos('#sync').text(response.message);
					} else {

                        $pos('#sync').text("Something Went wrong");
                        $pos('#sync').css("color", "red");
                    }

				}
			});
        });


		$pos('.wc-pos-barcode-print-wrapper .close').on('click', function () {
			$pos('#printBarcode').css('display', 'none')
		})

        $pos(document).on('click', '#_pos_user_pic', function (event) {

			var custom_uploader;

			event.preventDefault();

			var custom_uploader = wp.media({

				title:'Profile Pic',

				button: {

					text: 'Upload profile Pic',

				},
                library: {
                        type: [ 'image' ]
                },
				multiple: false  // Set this to true to allow multiple files to be selected

			})

			.on('select', function() {

				var attachment = custom_uploader.state().get('selection').first().toJSON();
				var file_short_path = attachment.url.split('uploads')[1];
                if (attachment.subtype === 'png' || attachment.subtype === 'jpg' || attachment.subtype === 'jpeg') {
                    $pos('.logo-url').attr('src', attachment.url);

                    $pos('#_pos_user_pic_val').val(file_short_path);
                }

			})
			.open();

		});

		$pos(document).on('click','#_pos_upload_logo',function(event) {
			var custom_uploader;
			event.preventDefault();
			var custom_uploader = wp.media({
				title:'Invoice Logo',
				button: {
					text: 'Upload Invoice Logo',
				},
                library: {
                        type: [ 'image' ]
                },
				multiple: false  // Set this to true to allow multiple files to be selected
            }).on('select', function () {
				var attachment = custom_uploader.state().get('selection').first().toJSON();
                var file_short_path = attachment.url.split('uploads')[1];
                if (attachment.subtype === 'png' || attachment.subtype === 'jpg' || attachment.subtype === 'jpeg') {
                    $pos('.logo-url').attr('src', attachment.url);
                    $pos('#_pos_invoice_logo').val(file_short_path);
                }
			})
			.open();

		});

		var oldval = '';
		$pos("input.pos_pro_stock").on('focus',function(){
			oldval = this.value;
		});
		$pos('input.pos_pro_stock').on('blur', function () {
			var outlet_id = $pos(this).data('outlet-id');
			var thisElm = $pos(this);
			var product_id = $pos(this).data('product-id');
			if( Math.abs( this.value ) != Math.abs( oldval ) && outlet_id && product_id){
				$pos.ajax({
					type:"POST",
					url: wk_wc_apipos_script.api_admin_ajax,
					data: {
						'action': 'update_pos_outlet_stock',
						'nonce': wk_wc_apipos_script.pos_api_nonce,
						'product_id': product_id,
						'outlet_id': outlet_id,
						'stock': Math.abs( this.value )
					},
					success: function(response){

						if (response.msg !==undefined) {
								if (! $pos(thisElm).hasClass('thick')) {
										location.reload();
								}
						}
						else if(response.err != undefined ) {
							alert(response.err);
							location.reload();

						} else {

							alert('Error updating stock.');

						}

					}
				});
			}

		});

		$pos('.posuserlist .delete').on('click', function (e) {
			if (! confirm( "This will completely delete this user." ) ) {
				e.preventDefault()
			}
		});

		$pos( ".pos-masterbulk-settings button[type='submit']" ).on( "click", function() {
				defQty = $pos(".pos-masterbulk-settings #_pos_master_assign_qty").val();
				if (/^\d*$/.test(defQty)) {
					var index = 0;
					var batch = 1;
					var batchSize = 50;
					var paged = 1;
					function recursive_master_product_ajax( paged ) {
						$pos.ajax({
							type:"POST",
							url:wk_wc_apipos_script.api_admin_ajax,
							data: {
								'action': 'get_all_products',
								'nonce': wk_wc_apipos_script.pos_api_nonce,
								'paged': paged
							},
							beforeSend : function (paged) {
								$pos(".wc-product-import-section").show();
								if( paged == 1 ) {
									$pos(".pos-masterbulk-settings .wc-product-import-section-body").append('<div class="notice notice-success is-dismissible"><p>Starting Execution...</p></div>');
									$pos(".pos-masterbulk-settings .wc-product-import-section-body").append('<div class="notice notice-error is-dismissible"><p>Please don\'t close or refresh the window while importing product(s).</p></div>');
								}
								$pos(".pos-masterbulk-settings .wc-product-import-section-body").append('<div class="notice notice-success"><p>Batch Process for products. Batch size will be <strong>' + batchSize + '</strong>.</p></div>');
							},
							success: function( response ) {
								if( response && _.size(response) ) {
									var new_array = $pos.map(response, function(value, index) {
										return [value];
									});
									var responseLength = new_array.length;
									function recursive_master_ajax( index ) {
										var productData = new_array.splice( index, batchSize );
										var raw_data = JSON.stringify( productData );
										$pos.ajax({
											type: 'POST',
											url:wk_wc_apipos_script.api_admin_ajax,
											data: {
												'action': 'assign_pos_master_stock',
												'nonce': wk_wc_apipos_script.pos_api_nonce,
												'percent' : defQty,
												'products': raw_data,
											},
											beforeSend: function(){
											},
											success: function( last_response ) {
												if (productData.length > 0) {
													if(last_response) {
														$pos(".pos-masterbulk-settings .wc-product-import-section-body").append('<div class="notice notice-success"><p>'+last_response+' Master Stock to Product assigned successfully.!</p></div>');
													} else{
														$pos(".pos-masterbulk-settings .wc-product-import-section-body").append('<div class="notice notice-error"><p>No New products to be assigned in batch <strong>' + batch + '</strong>.</p></div>');
													}
													batch++;
													recursive_master_ajax(index);
												} else {
													if (responseLength >= 99) {
														paged++;
														recursive_master_product_ajax(paged);
													} else {
														$pos(".pos-masterbulk-settings .wc-product-import-section").append('<div class="complete-process  notice notice-success"><p>Process Completed.!</p></div>');
														$pos(".pos-masterbulk-settings .wc-product-import-section .complete-process").hide();
														setTimeout(function(){
															location.reload();
														}, 1500);
													}

												}
											}
										});
									}
									recursive_master_ajax(index);
								}
							}
						});
					}
					recursive_master_product_ajax(paged);
				}
				else{
					alert('Must Enter Stock value');
				}
		});
		if( $pos("#_pos_outlet_payment").length ) {
			$pos("#_pos_outlet_payment").select2();
		}

	});

})(jQuery);


jQuery(document).ready(function($){
	var tabs = $('.cd-tabs');

	tabs.each(function(){
		var tab = $(this),
			tabItems = tab.find('ul.cd-tabs-navigation'),
			tabContentWrapper = tab.find('ul.cd-tabs-content'),
			tabNavigation = tab.find('nav');

		tabItems.on('click', 'a', function(event){
			event.preventDefault();
			var selectedItem = $(this);
			if( !selectedItem.hasClass('selected') ) {
				var selectedTab = selectedItem.data('content'),
					selectedContent = tabContentWrapper.find('li[data-content="'+selectedTab+'"]'),
					slectedContentHeight = selectedContent.innerHeight();

				tabItems.find('a.selected').removeClass('selected');
				selectedItem.addClass('selected');
				selectedContent.addClass('selected').siblings('li').removeClass('selected');
				//animate tabContentWrapper height when content changes
				tabContentWrapper.animate({
					'height': slectedContentHeight
				}, 200);
			}
		});

		//hide the .cd-tabs::after element when tabbed navigation has scrolled to the end (mobile version)
		checkScrolling(tabNavigation);
		tabNavigation.on('scroll', function(){
			checkScrolling($(this));
		});
	});

	$(window).on('resize', function(){
		tabs.each(function(){
			var tab = $(this);
			checkScrolling(tab.find('nav'));
			tab.find('.cd-tabs-content').css('height', 'auto');
		});
	});

	function checkScrolling(tabs){
		var totalTabWidth = parseInt(tabs.children('.cd-tabs-navigation').width()),
		 	tabsViewport = parseInt(tabs.width());
		if( tabs.scrollLeft() >= totalTabWidth - tabsViewport) {
			tabs.parent('.cd-tabs').addClass('is-ended');
		} else {
			tabs.parent('.cd-tabs').removeClass('is-ended');
		}
	}
});
// theme mode JS

const wkwcposObj = {
    theme_items: '',
    initElements:function(){
        this.theme_items = document.querySelectorAll('.wc-pos-theme-item');
        this.handleEvent();
    },
    handleEvent:function(){
        this.theme_items !== null ? this.theme_items.forEach(ele=>ele.addEventListener('click', this.handleThemeSwitch)) : null;
    },
    handleThemeSwitch: (event) => {
        let mode = event.target.getAttribute('data-mode');
        wkwcposObj.theme_items.forEach(ele => ele.classList.remove('active-theme'));
        event.target.classList.add('active-theme');
        document.querySelector('input[name="_pos_theme_mode"][value="' + mode + '"]').checked = true;
    }

}
document.addEventListener('DOMContentLoaded', () => {
    wkwcposObj.initElements();
});
