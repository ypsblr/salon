<?php
?>
<!-- Loading image made visible during processing -->
<div id="loader_img" style="display:none;background: rgba(0, 0, 0, 0.5);z-index: 9999;">
	<img alt="Loader Image" title="Loading..." src="img/moreajax.gif">
</div>
<script>
	function launchPage(page) {
		$('#loader_img').show();
		let jqxhr = $.post(page)
					.done(function(messages){
						$("#loader_img").hide();
						swal("Messages", messages);
					})
					.always(function(){
						$("#loader_img").hide();
					});
	}
</script>
<?php
?>