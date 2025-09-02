// Tinymce Plugin to add YPS Tags

/*
  Note: We have included the plugin in the same JavaScript file as the TinyMCE
  instance for display purposes only. Tiny recommends not maintaining the plugin
  with the TinyMCE instance and using the `external_plugins` option.
*/
tinymce.PluginManager.add('yps_tags', function(editor, url) {
	console.log(editor);
	console.log(url);

	/* Main Function */
	// function insertSalonName () {
	// 	editor.insertContent(' [contest-name] ');
	// };
	
	/* Add a button that opens a window */
	editor.ui.registry.addButton('yps_tags', {
    	text: 'Salon Name',
    	onAction: function () {
			editor.insertContent(' [contest-name] ');
			// insertSalonName();
    	}
  	});
  	/* Adds a menu item, which can then be included in any menu via the menu/menubar configuration */
  	editor.ui.registry.addMenuItem('yps_tags', {
    	text: 'Salon Name',
    	onAction: function() {
			editor.insertContent(' [contest-name] ');
			// insertSalonName();
    	}
  	});

  	/* Return the metadata for the help plugin */
  	return {
    	getMetadata: function () {
      		return  {
        		name: 'YPS Tag Salon Name',
        		url: '#'
      		};
		}
  	};
});

/*
  The following is an example of how to use the new plugin and the new
  toolbar button.
*/
// tinymce.init({
//   selector: 'textarea',
//   plugins: 'yps_tags help',
//   toolbar: 'yps_tags | help'
// });
