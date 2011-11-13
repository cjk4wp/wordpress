var topWin = window.dialogArguments || opener || parent || top, uploader, uploader_init;

function fileDialogStart() {
	jQuery("#media-upload-error").empty();
}

// progress and success handlers for media multi uploads
function fileQueued(fileObj) {
	// Get rid of unused form
	jQuery('.media-blank').remove();

	var items = jQuery('#media-items').children();

	// Collapse a single item
	if ( items.length == 1 ) {
		items.removeClass('open').find('.slidetoggle').slideUp(200);
	}
	// Create a progress bar containing the filename
	jQuery('#media-items').append('<div id="media-item-' + fileObj.id + '" class="media-item child-of-' + post_id + '"><div class="progress"><div class="percent">0%</div><div class="bar"></div></div><div class="filename original"> ' + fileObj.name + '</div></div>');
	// Display the progress div
	jQuery('.progress', jQuery('#media-item-' + fileObj.id)).fadeIn();

	// Disable submit
	jQuery('#insert-gallery').prop('disabled', true);
}

function uploadStart() {
	try {
		if ( typeof topWin.tb_remove != 'undefined' )
			topWin.jQuery('#TB_overlay').unbind('click', topWin.tb_remove);
	} catch(e){}

	return true;
}

function uploadProgress(up, file) {
	var item = jQuery('#media-item-' + file.id);

	jQuery('.bar', item).width( (200 * file.percent) / 100 );
	jQuery('.percent', item).html( file.percent + '%' );

	if ( file.percent == 100 ) {
		setTimeout( function(){
			jQuery('.percent', item).html( pluploadL10n.crunching );
		}, 200 );
	}
}

// check to see if a large file failed to upload
function fileUploading(up, file) {
	var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);

	if ( max > hundredmb && file.size > hundredmb ) {
		setTimeout(function(){
			if ( file.status == 2 && file.loaded == 0 ) { // not uploading
				wpFileError(file, pluploadL10n.big_upload_failed.replace('%1$s', '<a class="uploader-html" href="#">').replace('%2$s', '</a>'));

				if ( up.current && up.current.file.id == file.id && up.current.xhr.abort )
					up.current.xhr.abort();

				up.removeFile(file);
			}
		}, 5000); // 20 sec.
	}
}

function updateMediaForm() {
	var items = jQuery('#media-items').children();

	// Just one file, no need for collapsible part
	if ( items.length == 1 ) {
		items.addClass('open').find('.slidetoggle').slideDown();
		jQuery('.insert-gallery').hide();
	} else if ( items.length > 1 ) {
		items.removeClass('open');
		// Only show Gallery button when there are at least two files.
		jQuery('.insert-gallery').show();
	}

	// Only show Save buttons when there is at least one file.
	if ( items.not('.media-blank').length > 0 )
		jQuery('.savebutton').show();
	else
		jQuery('.savebutton').hide();
}

function uploadSuccess(fileObj, serverData) {
	var item = jQuery('#media-item-' + fileObj.id);

	// if async-upload returned an error message, place it in the media item div and return
	if ( serverData.match('media-upload-error') ) {
		item.html(serverData);
		return;
	}

	prepareMediaItem(fileObj, serverData);
	updateMediaForm();

	// Increment the counter.
	if ( item.hasClass('child-of-' + post_id) )
		jQuery('#attachments-count').text(1 * jQuery('#attachments-count').text() + 1);
}

function setResize(arg) {
	if ( arg ) {
		if ( uploader.features.jpgresize )
			uploader.settings['resize'] = { width: resize_width, height: resize_height, quality: 100 };
		else
			uploader.settings.multipart_params.image_resize = true;
	} else {
		delete(uploader.settings.resize);
		delete(uploader.settings.multipart_params.image_resize);
	}
}

function prepareMediaItem(fileObj, serverData) {
	var f = ( typeof shortform == 'undefined' ) ? 1 : 2, item = jQuery('#media-item-' + fileObj.id);
	// Move the progress bar to 100%
	jQuery('.bar', item).remove();
	jQuery('.progress', item).hide();

	try {
		if ( typeof topWin.tb_remove != 'undefined' )
			topWin.jQuery('#TB_overlay').click(topWin.tb_remove);
	} catch(e){}

	// Old style: Append the HTML returned by the server -- thumbnail and form inputs
	if ( isNaN(serverData) || !serverData ) {
		item.append(serverData);
		prepareMediaItemInit(fileObj);
	}
	// New style: server data is just the attachment ID, fetch the thumbnail and form html from the server
	else {
		item.load('async-upload.php', {attachment_id:serverData, fetch:f}, function(){prepareMediaItemInit(fileObj);updateMediaForm()});
	}
}

function prepareMediaItemInit(fileObj) {
	var item = jQuery('#media-item-' + fileObj.id);
	// Clone the thumbnail as a "pinkynail" -- a tiny image to the left of the filename
	jQuery('.thumbnail', item).clone().attr('class', 'pinkynail toggle').prependTo(item);

	// Replace the original filename with the new (unique) one assigned during upload
	jQuery('.filename.original', item).replaceWith( jQuery('.filename.new', item) );

	// Also bind toggle to the links
	jQuery('a.toggle', item).click(function(){
		jQuery(this).siblings('.slidetoggle').slideToggle(350, function(){
			var w = jQuery(window).height(), t = jQuery(this).offset().top, h = jQuery(this).height(), b;

			if ( w && t && h ) {
				b = t + h;

				if ( b > w && (h + 48) < w )
					window.scrollBy(0, b - w + 13);
				else if ( b > w )
					window.scrollTo(0, t - 36);
			}
		});

		if ( jQuery(this).is('.describe-toggle-on') )
			item.addClass('open');
		else
			item.removeClass('open');

		jQuery(this).siblings('a.toggle').focus();
		return false;
	});

	// Bind AJAX to the new Delete button
	jQuery('a.delete', item).click(function(){
		// Tell the server to delete it. TODO: handle exceptions
		jQuery.ajax({
			url: 'admin-ajax.php',
			type: 'post',
			success: deleteSuccess,
			error: deleteError,
			id: fileObj.id,
			data: {
				id : this.id.replace(/[^0-9]/g, ''),
				action : 'trash-post',
				_ajax_nonce : this.href.replace(/^.*wpnonce=/,'')
			}
		});
		return false;
	});

	// Bind AJAX to the new Undo button
	jQuery('a.undo', item).click(function(){
		// Tell the server to untrash it. TODO: handle exceptions
		jQuery.ajax({
			url: 'admin-ajax.php',
			type: 'post',
			id: fileObj.id,
			data: {
				id : this.id.replace(/[^0-9]/g,''),
				action: 'untrash-post',
				_ajax_nonce: this.href.replace(/^.*wpnonce=/,'')
			},
			success: function(data, textStatus){
				var item = jQuery('#media-item-' + fileObj.id);

				if ( type = jQuery('#type-of-' + fileObj.id).val() )
					jQuery('#' + type + '-counter').text(jQuery('#' + type + '-counter').text()-0+1);
				if ( item.hasClass('child-of-'+post_id) )
					jQuery('#attachments-count').text(jQuery('#attachments-count').text()-0+1);

				jQuery('.filename .trashnotice', item).remove();
				jQuery('.filename .title', item).css('font-weight','normal');
				jQuery('a.undo', item).addClass('hidden');
				jQuery('.menu_order_input', item).show();
				item.css( {backgroundColor:'#ceb'} ).animate( {backgroundColor: '#fff'}, { queue: false, duration: 500, complete: function(){ jQuery(this).css({backgroundColor:''}); } }).removeClass('undo');
			}
		});
		return false;
	});

	// Open this item if it says to start open (e.g. to display an error)
	jQuery('#media-item-' + fileObj.id + '.startopen').removeClass('startopen').slideToggle(500).siblings('.toggle').toggle();
}

// generic error message
function wpQueueError(message) {
	jQuery('#media-upload-error').show().html( '<div class="error"><p>' + message + '</p></div>' );
}

// file-specific error messages
function wpFileError(fileObj, message) {
	itemAjaxError(fileObj.id, message);
}

function itemAjaxError(id, message) {
	var item = jQuery('#media-item-' + id), filename = item.find('.filename').text();

	item.html('<div class="error-div">'
				+ '<a class="dismiss" href="#">' + pluploadL10n.dismiss + '</a>'
				+ '<strong>' + pluploadL10n.error_uploading.replace('%s', jQuery.trim(filename)) + '</strong> '
				+ message
				+ '</div>');
}

function deleteSuccess(data, textStatus) {
	if ( data == '-1' )
		return itemAjaxError(this.id, 'You do not have permission. Has your session expired?');
	if ( data == '0' )
		return itemAjaxError(this.id, 'Could not be deleted. Has it been deleted already?');

	var id = this.id, item = jQuery('#media-item-' + id);

	// Decrement the counters.
	if ( type = jQuery('#type-of-' + id).val() )
		jQuery('#' + type + '-counter').text( jQuery('#' + type + '-counter').text() - 1 );
	if ( item.hasClass('child-of-'+post_id) )
		jQuery('#attachments-count').text( jQuery('#attachments-count').text() - 1 );

	if ( jQuery('form.type-form #media-items').children().length == 1 && jQuery('.hidden', '#media-items').length > 0 ) {
		jQuery('.toggle').toggle();
		jQuery('.slidetoggle').slideUp(200).siblings().removeClass('hidden');
	}

	// Vanish it.
	jQuery('.toggle', item).toggle();
	jQuery('.slidetoggle', item).slideUp(200).siblings().removeClass('hidden');
	item.css( {backgroundColor:'#faa'} ).animate( {backgroundColor:'#f4f4f4'}, {queue:false, duration:500} ).addClass('undo');

	jQuery('.filename:empty', item).remove();
	jQuery('.filename .title', item).css('font-weight','bold');
	jQuery('.filename', item).append('<span class="trashnotice"> ' + pluploadL10n.deleted + ' </span>').siblings('a.toggle').hide();
	jQuery('.filename', item).append( jQuery('a.undo', item).removeClass('hidden') );
	jQuery('.menu_order_input', item).hide();

	return;
}

function deleteError(X, textStatus, errorThrown) {
	// TODO
}

function uploadComplete() {
	jQuery('#insert-gallery').prop('disabled', false);
}

function switchUploader(s) {
	if ( s ) {
		deleteUserSetting('uploader');
		jQuery('.media-upload-form').removeClass('html-uploader');

		if ( typeof(uploader) == 'object' )
			uploader.refresh();
	} else {
		setUserSetting('uploader', '1'); // 1 == html uploader
		jQuery('.media-upload-form').addClass('html-uploader');
	}
}

function dndHelper(s) {
	var d = document.getElementById('dnd-helper');

	if ( s ) {
		d.style.display = 'block';
	} else {
		d.style.display = 'none';
	}
}

function uploadError(fileObj, errorCode, message) {

	switch (errorCode) {
		case plupload.FAILED:
			wpFileError(fileObj, pluploadL10n.upload_failed);
			break;
		case plupload.FILE_EXTENSION_ERROR:
			wpFileError(fileObj, pluploadL10n.invalid_filetype);
			break;
		case plupload.FILE_SIZE_ERROR:
			wpQueueError( pluploadL10n.file_exceeds_size_limit.replace('%s', fileObj.name) );
			break;
		case plupload.IMAGE_FORMAT_ERROR:
			wpFileError(fileObj, pluploadL10n.not_an_image);
			break;
		case plupload.IMAGE_MEMORY_ERROR:
			wpFileError(fileObj, pluploadL10n.image_memory_exceeded);
			break;
		case plupload.IMAGE_DIMENSIONS_ERROR:
			wpFileError(fileObj, pluploadL10n.image_dimensions_exceeded);
			break;
		case plupload.GENERIC_ERROR:
			wpQueueError(pluploadL10n.upload_failed);
			break;
		case plupload.IO_ERROR:
			wpQueueError(pluploadL10n.io_error);
			break;
		case plupload.HTTP_ERROR:
			wpQueueError(pluploadL10n.http_error);
			break;
		case plupload.INIT_ERROR:
			jQuery('.media-upload-form').addClass('html-uploader');
			break;
		case plupload.SECURITY_ERROR:
			wpQueueError(pluploadL10n.security_error);
			break;
/*		case plupload.UPLOAD_ERROR.UPLOAD_STOPPED:
		case plupload.UPLOAD_ERROR.FILE_CANCELLED:
			jQuery('#media-item-' + fileObj.id).remove();
			break;*/
		default:
			wpFileError(fileObj, pluploadL10n.default_error);
	}
}

function uploadSizeError( up, error ) {
	var file = error.file;

	jQuery('#media-items').append('<div id="media-item-' + file.id + '" class="media-item error"><p>' + pluploadL10n.file_exceeds_size_limit.replace('%s', file.name) + '</p></div>');
	up.removeFile(file);
}

jQuery(document).ready(function($){
	$('.media-upload-form').bind('click.uploader', function(e) {
		var target = $(e.target), tr, c;

		if ( target.is('input[type="radio"]') ) { // remember the last used image size and alignment
			tr = target.closest('tr');

			if ( $(tr).hasClass('align') )
				setUserSetting('align', target.val());
			else if ( $(tr).hasClass('image-size') )
				setUserSetting('imgsize', target.val());

		} else if ( target.is('button.button') ) { // remember the last used image link url
			c = e.target.className || '';
			c = c.match(/url([^ '"]+)/);

			if ( c && c[1] ) {
				setUserSetting('urlbutton', c[1]);
				target.siblings('.urlfield').val( target.attr('title') );
			}
		} else if ( target.is('a.dismiss') ) {
			target.parents('.media-item').fadeOut(200, function(){
				$(this).remove();
			});
		} else if ( target.is('.upload-flash-bypass a') || target.is('a.uploader-html') ) { // switch uploader to html4
			switchUploader(0);
			return false;
		} else if ( target.is('.upload-html-bypass a') ) { // switch uploader to multi-file
			switchUploader(1);
			return false;
		}
	});

	// init and set the uploader
	uploader_init = function() {
		uploader = new plupload.Uploader(wpUploaderInit);

		$('#image_resize').bind('change', function() {
			var arg = $(this).prop('checked');

			setResize( arg );

			if ( arg )
				setUserSetting('upload_resize', '1');
			else
				deleteUserSetting('upload_resize');
		});

		uploader.bind('Init', function(up) {
			setResize( getUserSetting('upload_resize', false) );

			if ( up.features.dragdrop ) {
				$('#plupload-upload-ui').addClass('drag-drop');
				$('#drag-drop-area').bind('dragover.wp-uploader', function(){ // dragenter doesn't fire right :(
					$(this).css('border-color', '#ccff55');
				}).bind('dragleave.wp-uploader, drop.wp-uploader', function(){
					$(this).css('border-color', '');
				});
			} else {
				$('#plupload-upload-ui').removeClass('drag-drop');
				$('#drag-drop-area').unbind('.wp-uploader');
			}
		});

		uploader.init();

		uploader.bind('FilesAdded', function(up, files) {
			$('#media-upload-error').html('');
			uploadStart();
			up.refresh();
			up.start();
		});

		uploader.bind('BeforeUpload', function(up, file) {
			fileQueued(file);
		});

		uploader.bind('UploadFile', function(up, file) {
			fileUploading(up, file);
		});

		uploader.bind('UploadProgress', function(up, file) {
			uploadProgress(up, file);
		});

		uploader.bind('Error', function(up, err) {
			if ( err.code == plupload.FILE_SIZE_ERROR ) {
				uploadSizeError(up, err);
			} else {
				uploadError(err.file, err.code, err.message);
				up.refresh();
			}
		});

		uploader.bind('FileUploaded', function(up, file, response) {
			uploadSuccess(file, response.response);
		});

		uploader.bind('UploadComplete', function(up, files) {
			uploadComplete();
		});
	}

	if ( typeof(wpUploaderInit) == 'object' )
		uploader_init();

});
