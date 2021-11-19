jQuery(function($) {
   
	var eh_uploadField = {
		frames: [],
		init: function() {
           
			$( 'button.eh_image_upload' )
				.on( 'click', this.onClickUploadButton );

			$( 'button.eh_image_remove' )
				.on( 'click', this.removeProductImage );
		},

		onClickUploadButton: function( event ) {
           
			event.preventDefault();

			var data = $( event.target ).data();

			// If the media frame already exists, reopen it.
			if ( 'undefined' !== typeof eh_uploadField.frames[ data.fieldId ] ) {
				// Open frame.
				eh_uploadField.frames[ data.fieldId ].open();
				return false;
			}

			// Create the media frame.
			eh_uploadField.frames[ data.fieldId ] = wp.media( {
				title: data.mediaFrameTitle,
				button: {
					text: data.mediaFrameButton
				},
				multiple: false // Set to true to allow multiple files to be selected
			} );

			// When an image is selected, run a callback.
			var context = {
				fieldId: data.fieldId,
			};

			eh_uploadField.frames[ data.fieldId ]
				.on( 'select', eh_uploadField.onSelectAttachment, context );

			// Finally, open the modal.
			eh_uploadField.frames[ data.fieldId ].open();
		},

		onSelectAttachment: function() {
			// We set multiple to false so only get one image from the uploader.
			var attachment = eh_uploadField.frames[ this.fieldId ]
				.state()
				.get( 'selection' )
				.first()
				.toJSON();

			var $field = $( '#' + this.fieldId );
			var $img = $( '<img />' )
				.attr( 'src', getAttachmentUrl( attachment ) );

			$field.siblings( '.eh-image-preview-wrapper' )
				.html( $img );

			$field.val( attachment.id );
			$field.siblings( 'button.eh_image_remove' ).show();
			$field.siblings( 'button.eh_image_upload' ).hide();
		},

		removeProductImage: function( event ) {
			event.preventDefault();
			var $button = $( event.target );
			var data = $button.data();
			var $field = $( '#' + data.fieldId );

			//update fields
			$field.val( '' );
			$field.siblings( '.eh-image-preview-wrapper' ).html( ' ' );
			$button.hide();
			$field.siblings( 'button.eh_image_upload' ).show();
		},
		
	};
    function getAttachmentUrl( attachment ) {
		if ( attachment.sizes && attachment.sizes.medium ) {
			return attachment.sizes.medium.url;
		}
		if ( attachment.sizes && attachment.sizes.thumbnail ) {
			return attachment.sizes.thumbnail.url;
		}
		return attachment.url;
	}
    function run() {
		eh_uploadField.init();
	}

	$( run );
});