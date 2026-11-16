<?php
$salon_logo_yearmonth = isset($yearmonth) ? (int) $yearmonth : 0;
$salon_logo_show_preview = $salon_logo_yearmonth > 0 && salon_logo_exists($salon_logo_yearmonth);
?>
<div class="row form-group">
	<div class="col-sm-12">
		<label for="salon_logo">Salon logo</label>
		<p class="text-muted small">Saved as <code>salons/<?= $salon_logo_yearmonth > 0 ? $salon_logo_yearmonth : "{salon-id}"; ?>/img/salon_logo.jpg</code> for this salon.</p>
		<?php if ($salon_logo_show_preview) { ?>
			<p><img src="<?php echo htmlspecialchars(salon_logo_manage_src($salon_logo_yearmonth), ENT_QUOTES, "UTF-8"); ?>" alt="Salon logo" style="max-height: 120px; max-width: 100%; border: 1px solid #ddd; padding: 4px; background: #fff;"></p>
		<?php } elseif ($salon_logo_yearmonth > 0) { ?>
			<p class="text-muted"><i class="fa fa-info-circle"></i> No salon logo uploaded yet.</p>
		<?php } else { ?>
			<p class="text-muted small">Enter a Salon ID above, then choose an image when you create or update the salon.</p>
		<?php } ?>
		<input type="file" class="form-control" name="salon_logo" id="salon_logo" accept=".png,.jpg,.jpeg,.gif,image/png,image/jpeg,image/gif">
	</div>
</div>