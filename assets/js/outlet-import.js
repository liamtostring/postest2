/*global ajaxurl, wkwcpos_outlet_import_params */
;(function ( $, window ) {

	/**
	 * outletImportForm handles the import process.
	 */
	var outletImportForm = function( $form ) {
		this.$form           = $form;
		this.xhr             = false;
		this.mapping         = wkwcpos_outlet_import_params.mapping;
		this.position        = 0;
		this.file            = wkwcpos_outlet_import_params.file;
		this.update_existing = wkwcpos_outlet_import_params.update_existing;
		this.delimiter       = wkwcpos_outlet_import_params.delimiter;
		this.security        = wkwcpos_outlet_import_params.import_nonce;

		// Number of import successes/failures.
		this.imported = 0;
		this.failed   = 0;
		this.updated  = 0;
		this.skipped  = 0;

		// Initial state.
		this.$form.find('.woocommerce-importer-progress').val( 0 );

		this.run_import = this.run_import.bind( this );

		// Start importing.
		this.run_import();
	};

	/**
	 * Run the import in batches until finished.
	 */
	outletImportForm.prototype.run_import = function() {
		var $this = this;

		$.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action          : 'wkwcpos_do_ajax_outlet_import',
				position        : $this.position,
				mapping         : $this.mapping,
				file            : $this.file,
				update_existing : $this.update_existing,
				delimiter       : $this.delimiter,
				security        : $this.security
			},
			dataType: 'json',
			success: function( response ) {
				if ( response.success ) {
					$this.position  = response.data.position;
					$this.imported += response.data.imported;
					$this.failed   += response.data.failed;
					$this.updated  += response.data.updated;
					$this.skipped  += response.data.skipped;
					$this.$form.find('.woocommerce-importer-progress').val( response.data.percentage );

					if ( 'done' === response.data.position ) {
						window.location = response.data.url + '&outlets-imported=' + parseInt( $this.imported, 10 ) + '&outlets-failed=' + parseInt( $this.failed, 10 ) + '&outlets-updated=' + parseInt( $this.updated, 10 ) + '&outlets-skipped=' + parseInt( $this.skipped, 10 );
					} else {
						$this.run_import();
					}
				}
			}
		} ).fail( function( response ) {
			window.console.log( response );
		} );
	};

	/**
	 * Function to call outletImportForm on jQuery selector.
	 */
	$.fn.wc_outlet_importer = function() {
		new outletImportForm( this );
		return this;
	};

	$( '.woocommerce-importer' ).wc_outlet_importer();

})( jQuery, window );
