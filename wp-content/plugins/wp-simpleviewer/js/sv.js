var SV = window.SV || {};

(SV.Flash = function() {
	return {
		embed : function() {
			
			if (typeof this.configUrl !== 'string' || typeof tb_show !== 'function') {
				return;
			}
			
			var url = this.configUrl + ((this.configUrl.match(/\?/)) ? "&" : "?") + "TB_iframe=true";
			tb_show('Add SimpleViewer Gallery', url , false);
		}
	};
}());

/*
	Generator specific script
*/
(SV.Flash.Generator = function(){
    
	var toggleSection = function($toggleable) {
		if ($toggleable.css('display') === "" || $toggleable.css('display') === "block") {
			$toggleable.css('display', 'none');
		}
		else {
			$toggleable.css('display', 'block');
		}
	};
	
	var buildTag = function() {

        jQuery.post(SV.Flash.Generator.postUrl, jQuery('#build_form').serialize(), function (result) {
            if (result !== '') {
                var tag = '[simpleviewer ' + result + ']';
                insertTag(tag);
            }
        });
	};
	
	var insertTag = function(tag) {
		
		tag = tag || "";
		var win = window.parent || window;
				
		if ( typeof win.tinyMCE !== 'undefined' && ( win.ed = win.tinyMCE.activeEditor ) && !win.ed.isHidden() ) {
			win.ed.focus();
			if (win.tinymce.isIE)
				win.ed.selection.moveToBookmark(win.tinymce.EditorManager.activeEditor.windowManager.bookmark);

			win.ed.execCommand('mceInsertContent', false, tag);
		} else {
			win.edInsertContent(win.edCanvas, tag);
		}
		
		// Close Lightbox
		win.tb_remove();
		
	};
	
	return {
		
		initialize : function() {
			
			if (typeof jQuery === 'undefined') {
				return;
			}
			
			jQuery("#generate").click(function(e) {
				e.preventDefault();
				buildTag();
			});
            
			jQuery("#clear").click(function(e) {
				e.preventDefault();
				var win = window.parent || window;
				win.tb_remove();
			});

            jQuery('#library').change(function (e) {
                toggleSection(jQuery('#toggle_flickr'));
            });
		}
		
	};
	
}());

jQuery(document).ready(function () {
    jQuery('.deleteGallery').click(function () {
        return confirm('Are you sure you want to delete this gallery');
    });
});